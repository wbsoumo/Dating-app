<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\PaymentRefund;
use App\Models\Setting;
use App\Services\RazorpayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    protected RazorpayService $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $itemType = $request->input('item_type', 'all');
        $dateRange = $request->input('date_range', 'all');

        $query = Payment::with(['user', 'refunds'])->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($itemType !== 'all') {
            $query->where('item_type', $itemType);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhere('payment_id', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter
        if ($dateRange === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateRange === 'week') {
            $query->where('created_at', '>=', Carbon::now()->startOfWeek());
        } elseif ($dateRange === 'month') {
            $query->where('created_at', '>=', Carbon::now()->startOfMonth());
        } elseif ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay(),
            ]);
        }

        // Financial KPI Cards
        $totalCollected = (clone $query)->where('status', 'captured')->sum('amount');
        $totalRefunded = PaymentRefund::sum('amount');
        $countCaptured = (clone $query)->where('status', 'captured')->count();
        $countFailed = (clone $query)->where('status', 'failed')->count();

        $payments = $query->paginate(25)->withQueryString();
        $currency = Setting::get('currency_symbol', '₹');

        return view('admin.payments.index', compact(
            'payments',
            'status',
            'itemType',
            'dateRange',
            'totalCollected',
            'totalRefunded',
            'countCaptured',
            'countFailed',
            'currency'
        ));
    }

    public function show(int $id)
    {
        $payment = Payment::with(['user', 'refunds.processedByAdmin'])->findOrFail($id);
        $item = $payment->getItem();

        return view('admin.payments.show', compact('payment', 'item'));
    }

    /**
     * Process a refund on Razorpay transaction.
     */
    public function processRefund(Request $request, int $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'captured') {
            return back()->withErrors(['amount' => 'Only successfully captured payments can be refunded.']);
        }

        if ($request->amount > $payment->amount) {
            return back()->withErrors(['amount' => 'Refund amount cannot exceed payment amount.']);
        }

        try {
            $adminId = Auth::guard('admin')->id();
            $refund = $this->razorpayService->refund($payment, (float) $request->amount, $request->reason, $adminId);

            ActivityLog::record($adminId, 'process_refund', 'PaymentRefund', $refund->id, [
                'payment_id' => $payment->id,
                'amount' => $request->amount,
                'reason' => $request->reason,
            ]);

            return back()->with('success', "Refund of {$payment->currency} {$request->amount} processed successfully (Ref: {$refund->refund_id}).");
        } catch (\Throwable $e) {
            return back()->withErrors(['amount' => 'Refund processing error: ' . $e->getMessage()]);
        }
    }

    /**
     * Export Transactions as CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $status = $request->input('status', 'all');
        $itemType = $request->input('item_type', 'all');

        $query = Payment::with('user')->latest('id');
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($itemType !== 'all') {
            $query->where('item_type', $itemType);
        }

        $filename = 'transactions_export_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'ID',
                'Date',
                'User Name',
                'User Email',
                'Item Type',
                'Item ID',
                'Amount',
                'Currency',
                'Status',
                'Order ID',
                'Payment ID',
                'Payment Method',
            ]);

            $query->chunk(500, function ($payments) use ($file) {
                foreach ($payments as $p) {
                    fputcsv($file, [
                        $p->id,
                        $p->created_at->format('Y-m-d H:i:s'),
                        $p->user?->name ?? 'N/A',
                        $p->user?->email ?? 'N/A',
                        $p->item_type,
                        $p->item_id,
                        $p->amount,
                        $p->currency,
                        $p->status,
                        $p->order_id,
                        $p->payment_id,
                        $p->method ?? 'gateway',
                    ]);
                }
            });

            fclose($file);
        };

        ActivityLog::record(Auth::guard('admin')->id(), 'export_transactions_csv');

        return response()->stream($callback, 200, $headers);
    }
}
