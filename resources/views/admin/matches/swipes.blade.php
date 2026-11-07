@extends('admin.layouts.app')

@section('title', 'Swipe Logs')
@section('page_title', 'Real-time Swipe Activity Stream')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.matches.index') }}">Matches</a></li>
    <li class="breadcrumb-item active">Swipes</li>
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-hand-pointer text-primary mr-1"></i> Swipe Activity Logs
        </h3>
        <div class="card-tools">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.matches.swipes', ['type' => 'all']) }}" class="btn {{ $type === 'all' ? 'btn-primary' : 'btn-default' }}">All Swipes</a>
                <a href="{{ route('admin.matches.swipes', ['type' => 'like']) }}" class="btn {{ $type === 'like' ? 'btn-success' : 'btn-default' }}">Likes</a>
                <a href="{{ route('admin.matches.swipes', ['type' => 'star']) }}" class="btn {{ $type === 'star' ? 'btn-warning' : 'btn-default' }}">Super Likes</a>
                <a href="{{ route('admin.matches.swipes', ['type' => 'dislike']) }}" class="btn {{ $type === 'dislike' ? 'btn-secondary' : 'btn-default' }}">Passes</a>
            </div>
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Swiping</th>
                    <th>Action</th>
                    <th>Target Profile</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($swipes as $s)
                    <tr>
                        <td>#{{ $s->id }}</td>
                        <td>
                            @php
                                $uAvatar = $s->user?->photos->where('is_primary', true)->first()?->url ?? $s->user?->photos->first()?->url;
                            @endphp
                            <div class="d-flex align-items-center">
                                <img src="{{ $uAvatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($s->user?->name ?? 'User') }}" class="img-circle mr-2" style="width: 32px; height: 32px; object-fit: cover;">
                                <div>
                                    <a href="{{ route('admin.users.show', $s->user_id) }}" class="font-weight-bold text-dark">{{ $s->user?->name ?? 'Deleted' }}</a>
                                    <small class="text-muted d-block">{{ $s->user?->gender }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($s->type === 'like')
                                <span class="badge badge-success"><i class="fas fa-heart mr-1"></i> Liked</span>
                            @elseif($s->type === 'star')
                                <span class="badge badge-warning text-dark"><i class="fas fa-star mr-1"></i> Super Liked</span>
                            @else
                                <span class="badge badge-secondary"><i class="fas fa-times mr-1"></i> Passed</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $tAvatar = $s->targetUser?->photos->where('is_primary', true)->first()?->url ?? $s->targetUser?->photos->first()?->url;
                            @endphp
                            <div class="d-flex align-items-center">
                                <img src="{{ $tAvatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($s->targetUser?->name ?? 'User') }}" class="img-circle mr-2" style="width: 32px; height: 32px; object-fit: cover;">
                                <div>
                                    <a href="{{ route('admin.users.show', $s->target_user_id) }}" class="font-weight-bold text-dark">{{ $s->targetUser?->name ?? 'Deleted' }}</a>
                                    <small class="text-muted d-block">{{ $s->targetUser?->gender }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-xs text-muted">
                            {{ $s->created_at->format('d M Y, h:i:s A') }} ({{ $s->created_at->diffForHumans() }})
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No swipes recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($swipes->hasPages())
        <div class="card-footer clearfix border-top">
            <div class="float-right">
                {{ $swipes->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>
@endsection
