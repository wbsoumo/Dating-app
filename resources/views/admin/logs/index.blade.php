@extends('admin.layouts.app')

@section('title', 'Admin Activity Logs')
@section('page_title', 'Administrative Audit Trail & Security Logs')

@section('breadcrumb')
    <li class="breadcrumb-item active">Audit Logs</li>
@endsection

@section('content')
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header border-0 pb-0">
        <form method="GET" action="{{ route('admin.logs.index') }}" class="form-row align-items-center mb-3">
            <div class="col-md-4 col-sm-6 mb-2">
                <div class="input-group input-group-sm">
                    <input type="text" name="action" class="form-control" placeholder="Search action (e.g. login, update_user, refund)..." value="{{ request('action') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 mb-2">
                <a href="{{ route('admin.logs.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Administrator</th>
                    <th>Action</th>
                    <th>Target Subject</th>
                    <th>IP Address</th>
                    <th>Details</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>#{{ $log->id }}</td>
                        <td>
                            <span class="font-weight-bold">{{ $log->admin?->name ?? 'System' }}</span>
                            <small class="text-muted d-block">{{ $log->admin?->email }}</small>
                        </td>
                        <td>
                            <code class="text-primary">{{ $log->action }}</code>
                        </td>
                        <td>
                            @if($log->subject_type)
                                <span class="badge badge-light border">{{ $log->subject_type }} #{{ $log->subject_id }}</span>
                            @else
                                <span class="text-muted text-xs">N/A</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $log->ip_address ?? '127.0.0.1' }}</small>
                        </td>
                        <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;" class="text-xs">
                            @if(!empty($log->details))
                                <code>{{ json_encode($log->details) }}</code>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-xs text-muted">
                            {{ $log->created_at->format('d M Y, h:i:s A') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No audit logs recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="card-footer clearfix border-top">
            <div class="float-right">
                {{ $logs->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>
@endsection
