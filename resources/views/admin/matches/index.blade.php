@extends('admin.layouts.app')

@section('title', 'Mutual Matches')
@section('page_title', 'Mutual Connections & Matches')

@section('breadcrumb')
    <li class="breadcrumb-item active">Matches</li>
@endsection

@section('content')
<div class="card card-outline card-danger">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-heart text-danger mr-1"></i> Mutual Match Records
        </h3>
        <div class="card-tools">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.matches.index', ['status' => 'active']) }}" class="btn {{ $status === 'active' ? 'btn-danger' : 'btn-default' }}">Active Matches</a>
                <a href="{{ route('admin.matches.index', ['status' => 'unmatched']) }}" class="btn {{ $status === 'unmatched' ? 'btn-danger' : 'btn-default' }}">Unmatched</a>
                <a href="{{ route('admin.matches.index', ['status' => 'all']) }}" class="btn {{ $status === 'all' ? 'btn-danger' : 'btn-default' }}">All</a>
            </div>
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>Match ID</th>
                    <th>User 1</th>
                    <th>User 2</th>
                    <th>Matched At</th>
                    <th>Status</th>
                    <th>Last Activity</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matches as $m)
                    <tr>
                        <td>#{{ $m->id }}</td>
                        <td>
                            @php
                                $u1Avatar = $m->user1?->photos->where('is_primary', true)->first()?->url ?? $m->user1?->photos->first()?->url;
                            @endphp
                            <div class="d-flex align-items-center">
                                <img src="{{ $u1Avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($m->user1?->name ?? 'User') }}" class="img-circle mr-2" style="width: 32px; height: 32px; object-fit: cover;">
                                <div>
                                    <a href="{{ route('admin.users.show', $m->user1_id) }}" class="font-weight-bold text-dark">{{ $m->user1?->name ?? 'Deleted' }}</a>
                                    <small class="text-muted d-block">{{ $m->user1?->gender }}, {{ $m->user1?->age }} yrs</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $u2Avatar = $m->user2?->photos->where('is_primary', true)->first()?->url ?? $m->user2?->photos->first()?->url;
                            @endphp
                            <div class="d-flex align-items-center">
                                <img src="{{ $u2Avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($m->user2?->name ?? 'User') }}" class="img-circle mr-2" style="width: 32px; height: 32px; object-fit: cover;">
                                <div>
                                    <a href="{{ route('admin.users.show', $m->user2_id) }}" class="font-weight-bold text-dark">{{ $m->user2?->name ?? 'Deleted' }}</a>
                                    <small class="text-muted d-block">{{ $m->user2?->gender }}, {{ $m->user2?->age }} yrs</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <i class="far fa-calendar-alt text-muted mr-1"></i> {{ $m->matched_at->format('d M Y, h:i A') }}
                        </td>
                        <td>
                            @if($m->status === 'active')
                                <span class="badge badge-success"><i class="fas fa-heart mr-1"></i> Active Match</span>
                            @else
                                <span class="badge badge-secondary" title="{{ $m->unmatch_reason }}">
                                    Unmatched (by {{ $m->unmatchedByUser?->name ?? 'User' }})
                                </span>
                            @endif
                        </td>
                        <td class="text-xs text-muted">
                            {{ $m->last_message_at ? $m->last_message_at->diffForHumans() : 'No messages' }}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.users.show', $m->user1_id) }}" class="btn btn-xs btn-default" title="View User 1">U1</a>
                            <a href="{{ route('admin.users.show', $m->user2_id) }}" class="btn btn-xs btn-default" title="View User 2">U2</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-heart-broken fa-2x mb-2 d-block"></i>
                            No match records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($matches->hasPages())
        <div class="card-footer clearfix border-top">
            <div class="float-right">
                {{ $matches->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>
@endsection
