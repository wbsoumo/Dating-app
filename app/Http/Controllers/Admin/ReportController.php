<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');

        $query = UserReport::with(['reporter', 'reportedUser.photos', 'resolvedByAdmin'])
            ->latest('id');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $reports = $query->paginate(25)->withQueryString();

        return view('admin.reports.index', compact('reports', 'status'));
    }

    public function resolve(Request $request, int $id)
    {
        $request->validate([
            'action' => 'required|in:dismiss,suspend_user,ban_user,warn',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $report = UserReport::findOrFail($id);
        $adminId = Auth::guard('admin')->id();

        $action = $request->action;
        $reportedUser = $report->reportedUser;

        if ($action === 'suspend_user' && $reportedUser) {
            $reportedUser->update(['status' => 'suspended']);
        } elseif ($action === 'ban_user' && $reportedUser) {
            $reportedUser->update(['status' => 'banned']);
            $reportedUser->tokens()->delete();
        }

        $report->update([
            'status' => ($action === 'dismiss') ? 'dismissed' : 'resolved',
            'admin_notes' => $request->admin_notes ?? "Action taken: {$action}",
            'resolved_by' => $adminId,
            'resolved_at' => Carbon::now(),
        ]);

        ActivityLog::record($adminId, 'moderate_report', 'UserReport', $report->id, [
            'action' => $action,
            'reported_user_id' => $report->reported_user_id,
        ]);

        return back()->with('success', "Report #{$report->id} has been processed ({$action}).");
    }
}
