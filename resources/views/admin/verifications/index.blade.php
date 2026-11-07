@extends('admin.layouts.app')

@section('title', 'Profile Verification Requests')
@section('page_title', 'Identity & Selfie Verification')

@section('breadcrumb')
    <li class="breadcrumb-item active">Verifications</li>
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-id-card text-primary mr-1"></i> Verification Queue
        </h3>
        <div class="card-tools">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.verifications.index', ['status' => 'pending']) }}" class="btn {{ $status === 'pending' ? 'btn-primary' : 'btn-default' }}">Pending Review</a>
                <a href="{{ route('admin.verifications.index', ['status' => 'verified']) }}" class="btn {{ $status === 'verified' ? 'btn-primary' : 'btn-default' }}">Approved Verified</a>
                <a href="{{ route('admin.verifications.index', ['status' => 'all']) }}" class="btn {{ $status === 'all' ? 'btn-primary' : 'btn-default' }}">All Requests</a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row">
            @forelse($requests as $reqUser)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border shadow-sm h-100">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center p-2">
                            <div>
                                <span class="font-weight-bold">{{ $reqUser->name }}</span>
                                <small class="text-muted d-block">{{ $reqUser->gender }}, {{ $reqUser->age }} yrs &bull; {{ $reqUser->location?->city ?? 'N/A' }}</small>
                            </div>
                            @if($reqUser->is_verified)
                                <span class="badge badge-success"><i class="fas fa-check mr-1"></i> Verified</span>
                            @else
                                <span class="badge badge-warning">Pending Review</span>
                            @endif
                        </div>

                        <div class="card-body p-2">
                            <div class="row no-gutters">
                                <div class="col-6 pr-1 text-center">
                                    <small class="d-block font-weight-bold text-muted mb-1">Uploaded Selfie</small>
                                    <a href="{{ $reqUser->verification_photo }}" target="_blank">
                                        <img src="{{ $reqUser->verification_photo }}" class="img-fluid rounded" style="height: 160px; width: 100%; object-fit: cover;" alt="Selfie">
                                    </a>
                                </div>
                                <div class="col-6 pl-1 text-center">
                                    <small class="d-block font-weight-bold text-muted mb-1">Profile Avatar</small>
                                    @php
                                        $primaryUrl = $reqUser->photos->where('is_primary', true)->first()?->url ?? $reqUser->photos->first()?->url;
                                    @endphp
                                    <a href="{{ $primaryUrl }}" target="_blank">
                                        <img src="{{ $primaryUrl ?? 'https://ui-avatars.com/api/?name=' . urlencode($reqUser->name) }}" class="img-fluid rounded" style="height: 160px; width: 100%; object-fit: cover;" alt="Avatar">
                                    </a>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2 text-center">
                                Submitted: {{ $reqUser->verification_submitted_at ? $reqUser->verification_submitted_at->diffForHumans() : 'N/A' }}
                            </small>
                        </div>

                        <div class="card-footer bg-white p-2 d-flex justify-content-between">
                            <a href="{{ route('admin.users.show', $reqUser->id) }}" class="btn btn-default btn-sm">
                                <i class="fas fa-eye"></i> View Profile
                            </a>
                            <div>
                                @if(!$reqUser->is_verified)
                                    <form action="{{ route('admin.verifications.approve', $reqUser->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check mr-1"></i> Approve
                                        </button>
                                    </form>
                                    <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#rejectModal{{ $reqUser->id }}">
                                        <i class="fas fa-times mr-1"></i> Reject
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rejection Modal -->
                <div class="modal fade" id="rejectModal{{ $reqUser->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form action="{{ route('admin.verifications.reject', $reqUser->id) }}" method="POST">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-times-circle mr-1"></i> Reject Verification</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p>Select reason for rejecting {{ $reqUser->name }}'s verification request:</p>
                                    <div class="form-group">
                                        <select name="reason" class="form-control">
                                            <option value="Selfie does not match profile photos">Selfie does not match profile photos</option>
                                            <option value="Image is too blurry or low quality">Image is too blurry or low quality</option>
                                            <option value="Face is covered or not clearly visible">Face is covered or not clearly visible</option>
                                            <option value="Suspected fake or impersonation profile">Suspected fake or impersonation profile</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="fas fa-clipboard-check fa-3x mb-2 text-success"></i>
                    <h5>No verification requests in this queue.</h5>
                    <p class="text-sm">All member verification submissions have been reviewed.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if($requests->hasPages())
        <div class="card-footer clearfix border-top">
            <div class="float-right">
                {{ $requests->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>
@endsection
