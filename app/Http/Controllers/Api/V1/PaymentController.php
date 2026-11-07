<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Payment;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends BaseApiController
{
    protected RazorpayService $razorpayService;

    public function __construct(RazorpayService $razorpayService)
    {
        $this->razorpayService = $razorpayService;
    }

    /**
     * Create Razorpay payment order for subscription or star pack.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_type' => 'required|in:subscription,star_package',
            'item_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = $request->user();
            $orderData = $this->razorpayService->createOrder(
                $user,
                $request->item_type,
                (int) $request->item_id
            );

            return $this->sendResponse($orderData, 'Razorpay order created successfully.');
        } catch (\Throwable $e) {
            return $this->sendError($e->getMessage(), null, 400);
        }
    }

    /**
     * Verify payment signature and fulfill purchase.
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        try {
            $result = $this->razorpayService->verifyPayment(
                $request->razorpay_order_id,
                $request->razorpay_payment_id,
                $request->razorpay_signature
            );

            return $this->sendResponse($result, $result['message']);
        } catch (\Throwable $e) {
            return $this->sendError($e->getMessage(), null, 400);
        }
    }

    /**
     * Handle incoming Razorpay webhook.
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature', '');

        try {
            $result = $this->razorpayService->processWebhook($payload, $signature);
            return response()->json(['status' => 'ok', 'result' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * User's payment history.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        $payments = Payment::with('refunds')
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);

        return $this->sendResponse($payments, 'Payment history retrieved.');
    }
}
