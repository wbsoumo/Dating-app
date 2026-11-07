<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StarPackage;
use App\Models\StarTransaction;
use App\Models\StarWallet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StarController extends Controller
{
    public function index()
    {
        $packages = StarPackage::orderBy('sort_order', 'asc')->get();
        return view('admin.stars.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.stars.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'stars_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'discount_percent' => 'nullable|integer|between:0,100',
            'badge' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $pkg = StarPackage::create([
            'name' => $request->name,
            'stars_count' => $request->stars_count,
            'price' => $request->price,
            'currency' => $request->currency,
            'discount_percent' => $request->discount_percent ?? 0,
            'badge' => $request->badge,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        ActivityLog::record(Auth::guard('admin')->id(), 'create_star_package', 'StarPackage', $pkg->id, ['name' => $pkg->name]);

        return redirect()->route('admin.stars.index')
            ->with('success', "Star package '{$pkg->name}' created successfully.");
    }

    public function edit(int $id)
    {
        $package = StarPackage::findOrFail($id);
        return view('admin.stars.edit', compact('package'));
    }

    public function update(Request $request, int $id)
    {
        $package = StarPackage::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'stars_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'discount_percent' => 'nullable|integer|between:0,100',
            'badge' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $package->update([
            'name' => $request->name,
            'stars_count' => $request->stars_count,
            'price' => $request->price,
            'currency' => $request->currency,
            'discount_percent' => $request->discount_percent ?? 0,
            'badge' => $request->badge,
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?? 0,
        ]);

        ActivityLog::record(Auth::guard('admin')->id(), 'update_star_package', 'StarPackage', $package->id, ['name' => $package->name]);

        return redirect()->route('admin.stars.index')
            ->with('success', "Star package '{$package->name}' updated successfully.");
    }

    public function toggleActive(int $id)
    {
        $package = StarPackage::findOrFail($id);
        $package->is_active = !$package->is_active;
        $package->save();

        return back()->with('success', "Star package status updated.");
    }

    /**
     * All star transactions audit log across users.
     */
    public function transactions(Request $request)
    {
        $type = $request->input('type', 'all');

        $query = StarTransaction::with(['user', 'wallet'])
            ->latest('id');

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        if ($request->filled('ref_type') && $request->ref_type !== 'all') {
            $query->where('reference_type', $request->ref_type);
        }

        $transactions = $query->paginate(30)->withQueryString();

        return view('admin.stars.transactions', compact('transactions', 'type'));
    }

    /**
     * All user star wallet balances.
     */
    public function wallets(Request $request)
    {
        $query = StarWallet::with('user')
            ->orderBy('balance', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $wallets = $query->paginate(25)->withQueryString();

        return view('admin.stars.wallets', compact('wallets'));
    }
}
