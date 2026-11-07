@extends('admin.layouts.app')

@section('title', 'Safety & Moderation Reports')
@section('page_title', 'User Reports & Safety Moderation')

@section('breadcrumb')
    <li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<div class="card card-outline card-danger shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-flag text-danger mr-1"></i> User Complaints Queue
        </h3>
        <div class="card-tools">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}" class="btn {{ $status === 'pending' ? 'btn-danger' : 'btn-default' }}">Pending Review</a>
                <a href="{{ route('admin.reports.index', ['status' => 'resolved']) }}" class="btn {{ $status === 'resolved' ? 'btn-danger' : 'btn-default' }}">Resolved</a>
                <a href="{{ route('admin.reports.index', ['status' => 'dismissed']) }}" class="btn {{ $status === 'dismissed' ? 'btn-danger' : 'btn-default' }}">Dismissed</a>
                <a href="{{ route('admin.reports.index', ['status' => 'all']) }}" class="btn {{ $status === 'all' ? 'btn-danger' : 'btn-default' }}">All</a>
            </div>
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Reported User</th>
                    <th>Reason Category</th>
                    <th>Details / Complaint</th>
                    <th>Reported By</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                    <tr>
                        <td>#{{ $r->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $r->reported_user_id) }}" class="font-weight-bold text-dark">
                                {{ $r->reportedUser?->name ?? 'Deleted Profile' }}
                            </a>
                            <small class="text-muted d-block">{{ $r->reportedUser?->status }}</small>
                        </td>
                        <td>
                            <span class="badge badge-danger">
                                {{ ucfirst(str_replace('_', ' ', $r->reason)) }}
                            </span>
                        </td>
                        <td style="max-width: 250px; white-space: normal;" class="text-sm">
                            {{ $r->details ?: 'No additional notes provided by reporter.' }}
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $r->reporter_id) }}" class="text-muted text-sm">
                                {{ $r->reporter?->name ?? 'User #' . $r->reporter_id }}
                            </a>
                        </td>
                        <td>
                            @if($r->status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($r->status === 'resolved')
                                <span class="badge badge-success">Resolved</span>
                            @else
                                <span class="badge badge-secondary">Dismissed</span>
                            @endif
                        </td>
                        <td class="text-xs text-muted">{{ $r->created_at->diffForHumans() }}</td>
                        <td class="text-right">
                            @if($r->status === 'pending')
                                <button class="btn btn-xs btn-primary font-weight-bold" data-toggle="modal" data-target="#moderateModal{{ $r->id }}">
                                    <i class="fas fa-gavel mr-1"></i> Moderate
                                </button>
                            @else
                                <span class="text-xs text-muted">Reviewed</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Moderate Modal -->
                    @if($r->status === 'pending')
                        <div class="modal fade" id="moderateModal{{ $r->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <form action="{{ route('admin.reports.resolve', $r->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title font-weight-bold"><i class="fas fa-gavel text-danger mr-1"></i> Moderate Report #{{ $r->id }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Take moderation action regarding <strong>{{ $r->reportedUser?->name }}</strong>:</p>

                                            <div class="form-group">
                                                <label>Action to Take</label>
                                                <select name="action" class="form-control" required>
                                                    <option value="dismiss">Dismiss Report (No Violation)</option>
                                                    <option value="warn">Warn User / Keep Active</option>
                                                    <option value="suspend_user">Suspend User Account</option>
                                                    <option value="ban_user">Permanently Ban User Account</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Admin Audit Note</label>
                                                <textarea name="admin_notes" class="form-control" rows="2" placeholder="Record rationale for this decision..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger btn-sm font-weight-bold">Confirm Action</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="fas fa-shield-alt fa-2x mb-2 text-success d-block"></i>
                            No reports in this queue. Platform safety is clean!
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($reports->hasPages())
        <div class="card-footer clearfix border-top">
            <div class="float-right">
                {{ $reports->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>
@endsection
