<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['profile', 'location', 'photos', 'starWallet', 'activeSubscription.plan'])
            ->latest('id');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Gender filter
        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender', $request->gender);
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Verification filter
        if ($request->filled('verified') && $request->verified !== 'all') {
            $query->where('is_verified', $request->verified === '1');
        }

        // Demo user filter
        if ($request->filled('demo') && $request->demo !== 'all') {
            $query->where('is_demo', $request->demo === '1');
        }

        $users = $query->paginate(25)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(int $id)
    {
        $user = User::with([
            'profile', 'location', 'preferences', 'photos', 'interests',
            'starWallet.transactions', 'subscriptions.plan', 'payments',
            'swipes.targetUser', 'matchesAsUser1.user2', 'matchesAsUser2.user1',
            'reportsReceived.reporter',
        ])->findOrFail($id);

        return view('admin.users.show', compact('user'));
    }

    public function edit(int $id)
    {
        $user = User::with(['profile', 'location'])->findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => "nullable|email|unique:users,email,{$id}",
            'phone' => "nullable|string|unique:users,phone,{$id}",
            'gender' => 'required|in:male,female,non_binary,other',
            'dob' => 'nullable|date',
            'status' => 'required|in:active,suspended,banned,deactivated',
            'custom_swipe_limit' => 'nullable|integer',
            'is_verified' => 'nullable|boolean',
            'is_premium' => 'nullable|boolean',
        ]);

        $dob = $request->dob ? Carbon::parse($request->dob) : $user->dob;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'dob' => $dob ? $dob->toDateString() : null,
            'age' => $dob ? $dob->age : $user->age,
            'status' => $request->status,
            'custom_swipe_limit' => $request->filled('custom_swipe_limit') ? (int) $request->custom_swipe_limit : null,
            'is_verified' => $request->boolean('is_verified'),
            'is_premium' => $request->boolean('is_premium'),
        ]);

        ActivityLog::record(Auth::guard('admin')->id(), 'update_user', 'User', $user->id, [
            'status' => $user->status,
            'is_verified' => $user->is_verified,
        ]);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', "Profile of {$user->name} updated successfully.");
    }

    public function toggleVerification(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $user->is_verified = !$user->is_verified;
        $user->save();

        ActivityLog::record(Auth::guard('admin')->id(), 'toggle_verification', 'User', $user->id, [
            'is_verified' => $user->is_verified,
        ]);

        $statusStr = $user->is_verified ? 'verified' : 'unverified';
        return back()->with('success', "User {$user->name} has been marked as {$statusStr}.");
    }

    public function toggleStatus(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $newStatus = ($user->status === 'active') ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        ActivityLog::record(Auth::guard('admin')->id(), 'toggle_user_status', 'User', $user->id, [
            'new_status' => $newStatus,
        ]);

        return back()->with('success', "User account status changed to {$newStatus}.");
    }

    public function adjustStars(Request $request, int $id)
    {
        $request->validate([
            'action' => 'required|in:credit,debit',
            'amount' => 'required|integer|min:1|max:10000',
            'note' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $wallet = $user->getOrCreateWallet();

        if ($request->action === 'credit') {
            $wallet->credit($request->amount, "Admin credit: {$request->note}", 'admin_adjustment', (string) Auth::guard('admin')->id());
        } else {
            if ($wallet->balance < $request->amount) {
                return back()->withErrors(['amount' => 'User star balance is less than deduction amount.']);
            }
            $wallet->debit($request->amount, "Admin debit: {$request->note}", 'admin_adjustment', (string) Auth::guard('admin')->id());
        }

        ActivityLog::record(Auth::guard('admin')->id(), 'adjust_stars', 'User', $user->id, [
            'action' => $request->action,
            'amount' => $request->amount,
            'note' => $request->note,
            'balance_after' => $wallet->balance,
        ]);

        return back()->with('success', "Star wallet adjusted successfully. New balance: {$wallet->balance} Stars.");
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        ActivityLog::record(Auth::guard('admin')->id(), 'delete_user', 'User', $id, ['name' => $name]);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$name} was deleted successfully.");
    }
}
