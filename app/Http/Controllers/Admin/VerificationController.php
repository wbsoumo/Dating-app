<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        $query = User::with(['profile', 'photos'])
            ->whereNotNull('verification_photo');

        if ($status === 'pending') {
            $query->where('is_verified', false);
        } elseif ($status === 'verified') {
            $query->where('is_verified', true);
        }

        $requests = $query->orderBy('verification_submitted_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.verifications.index', compact('requests', 'status'));
    }

    public function approve(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_verified' => true]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Profile Verified! 🛡️',
            'body' => 'Congratulations! Your profile has been verified and awarded the official verification badge.',
            'type' => 'system',
        ]);

        ActivityLog::record(Auth::guard('admin')->id(), 'approve_verification', 'User', $user->id);

        return back()->with('success', "Verification approved for {$user->name}. Badge activated!");
    }

    public function reject(Request $request, int $id)
    {
        $user = User::findOrFail($id);
        $reason = $request->input('reason', 'Verification selfie did not match profile pictures.');

        $user->update([
            'is_verified' => false,
            'verification_photo' => null,
            'verification_submitted_at' => null,
        ]);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Verification Rejected',
            'body' => "Your profile verification request was not approved: {$reason}. You may re-submit anytime.",
            'type' => 'system',
        ]);

        ActivityLog::record(Auth::guard('admin')->id(), 'reject_verification', 'User', $user->id, ['reason' => $reason]);

        return back()->with('info', "Verification request for {$user->name} has been rejected.");
    }
}
