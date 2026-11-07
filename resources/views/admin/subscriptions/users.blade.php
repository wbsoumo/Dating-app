@extends('admin.layouts.app')

@section('title', 'User Subscriptions')
@section('page_title', 'Active & Historical User Subscriptions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.subscriptions.index') }}">Subscriptions</a></li>
    <li class="breadcrumb-item active">Subscribers</li>
@endsection

@section('content')
<div class="card card-outline card-warning">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-crown text-warning mr-1"></i> Subscriber Roster
        </h3>
        <div class="card-tools">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.subscriptions.users', ['status' => 'all']) }}" class="btn {{ $status === 'all' ? 'btn-warning' : 'btn-default' }}">All</a>
                <a href="{{ route('admin.subscriptions.users', ['status' => 'active']) }}" class="btn {{ $status === 'active' ? 'btn-warning' : 'btn-default' }}">Active Only</a>
                <a href="{{ route('admin.subscriptions.users', ['status' => 'expired']) }}" class="btn {{ $status === 'expired' ? 'btn-warning' : 'btn-default' }}">Expired</a>
                <a href="{{ route('admin.subscriptions.users', ['status' => 'cancelled']) }}" class="btn {{ $status === 'cancelled' ? 'btn-warning' : 'btn-default' }}">Cancelled</a>
            </div>
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Sub ID</th>
                    <th>User</th>
                    <th>Plan Tier</th>
                    <th>Started</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                    <tr>
                        <td>#{{ $sub->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $sub->user_id) }}" class="font-weight-bold text-dark">
                                {{ $sub->user?->name ?? 'Deleted User' }}
                            </a>
                            <small class="text-muted d-block">{{ $sub->user?->email }}</small>
                        </td>
                        <td>
                            <span class="badge" style="background: {{ $sub->plan?->badge_color ?? '#eab308' }}; color: #fff;">
                                <i class="fas fa-crown mr-1"></i> {{ $sub->plan?->name ?? 'Plan Removed' }}
                            </span>
                        </td>
                        <td class="text-xs text-muted">{{ $sub->starts_at->format('d M Y') }}</td>
                        <td>
                            <span class="text-xs font-weight-bold {{ $sub->isCurrent() ? 'text-success' : 'text-danger' }}">
                                {{ $sub->expires_at->format('d M Y') }}
                                ({{ $sub->expires_at->diffForHumans() }})
                            </span>
                        </td>
                        <td>
                            @if($sub->status === 'active' && $sub->isCurrent())
                                <span class="badge badge-success">Active VIP</span>
                            @elseif($sub->status === 'cancelled')
                                <span class="badge badge-danger">Cancelled</span>
                            @else
                                <span class="badge badge-secondary">Expired</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <button class="btn btn-xs btn-outline-warning" data-toggle="modal" data-target="#extendModal{{ $sub->id }}">
                                <i class="fas fa-calendar-plus mr-1"></i> Extend
                            </button>
                            @if($sub->status === 'active')
                                <form action="{{ route('admin.subscriptions.cancel', $sub->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel this subscription?');">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-outline-danger">Cancel</button>
                                </form>
                            @endif
                        </td>
                    </tr>

                    <!-- Extend Modal -->
                    <div class="modal fade" id="extendModal{{ $sub->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('admin.subscriptions.extend', $sub->id) }}" method="POST">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title font-weight-bold"><i class="fas fa-calendar-plus text-warning mr-1"></i> Extend Subscription</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Extend subscription for <strong>{{ $sub->user?->name }}</strong> ({{ $sub->plan?->name }}):</p>
                                        <div class="form-group">
                                            <label>Days to Add</label>
                                            <input type="number" name="days" class="form-control" value="30" min="1" max="365" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-warning btn-sm font-weight-bold">Confirm Extension</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No subscriptions found in this view.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($subscriptions->hasPages())
        <div class="card-footer clearfix border-top">
            <div class="float-right">
                {{ $subscriptions->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>
@endsection
