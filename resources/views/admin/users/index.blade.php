@extends('admin.layouts.app')

@section('title', 'User Management')
@section('page_title', 'Registered Dating Profiles')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header border-0 pb-0">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.users.index') }}" class="form-row align-items-center mb-3">
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="input-group">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, phone..." value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-sm btn-default"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-2">
                <select name="gender" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="all">All Genders</option>
                    <option value="male" {{ request('gender') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ request('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    <option value="other" {{ request('gender') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="col-md-2 col-sm-6 mb-2">
                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="all">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                </select>
            </div>

            <div class="col-md-2 col-sm-6 mb-2">
                <select name="verified" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="all">All Verifications</option>
                    <option value="1" {{ request('verified') === '1' ? 'selected' : '' }}>Verified Only</option>
                    <option value="0" {{ request('verified') === '0' ? 'selected' : '' }}>Unverified Only</option>
                </select>
            </div>

            <div class="col-md-2 col-sm-6 mb-2">
                <select name="demo" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="all">All Types (Demo & Real)</option>
                    <option value="0" {{ request('demo') === '0' ? 'selected' : '' }}>Real Users Only</option>
                    <option value="1" {{ request('demo') === '1' ? 'selected' : '' }}>Demo Accounts Only</option>
                </select>
            </div>

            <div class="col-md-1 col-sm-6 mb-2 text-right">
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="fas fa-rotate"></i></a>
            </div>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Profile</th>
                    <th>Gender & Age</th>
                    <th>City / Location</th>
                    <th>Stars</th>
                    <th>VIP Plan</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            @php
                                $avatar = $user->photos->where('is_primary', true)->first()?->url
                                    ?? $user->photos->first()?->url
                                    ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name);
                            @endphp
                            <div class="d-flex align-items-center">
                                <img src="{{ $avatar }}" class="img-circle mr-2" style="width: 38px; height: 38px; object-fit: cover;" alt="avatar">
                                <div>
                                    <span class="font-weight-bold d-block">{{ $user->name }}</span>
                                    <small class="text-muted">{{ $user->email ?? $user->phone }}</small>
                                    @if($user->is_demo)
                                        <span class="badge badge-light border text-xs">Demo</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->gender === 'male')
                                <span class="badge badge-primary"><i class="fas fa-mars mr-1"></i> Male</span>
                            @elseif($user->gender === 'female')
                                <span class="badge badge-pink" style="background:#ec4899; color:#fff;"><i class="fas fa-venus mr-1"></i> Female</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($user->gender) }}</span>
                            @endif
                            <span class="text-muted ml-1">{{ $user->age ?? 'N/A' }} yrs</span>
                        </td>
                        <td>
                            <i class="fas fa-location-dot text-danger mr-1 text-xs"></i>
                            {{ $user->location?->city ?? 'N/A' }}
                        </td>
                        <td>
                            <span class="badge badge-warning text-dark font-weight-bold">
                                <i class="fas fa-star text-warning"></i> {{ $user->starWallet?->balance ?? 0 }}
                            </span>
                        </td>
                        <td>
                            @if($user->hasActivePremium())
                                <span class="badge badge-success"><i class="fas fa-crown mr-1"></i> VIP</span>
                            @else
                                <span class="text-muted text-xs">Free</span>
                            @endif
                        </td>
                        <td>
                            @if($user->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @elseif($user->status === 'suspended')
                                <span class="badge badge-warning">Suspended</span>
                            @elseif($user->status === 'banned')
                                <span class="badge badge-danger">Banned</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($user->status) }}</span>
                            @endif

                            @if($user->is_verified)
                                <i class="fas fa-check-circle text-primary ml-1" title="Verified Badge"></i>
                            @endif
                        </td>
                        <td class="text-muted text-xs">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="text-right">
                            <div class="btn-group">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-sm btn-default" title="View Full Profile">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-default" title="Edit Profile">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.users.toggle-verify', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-default" title="{{ $user->is_verified ? 'Unverify' : 'Verify' }}">
                                        <i class="fas fa-certificate {{ $user->is_verified ? 'text-primary' : 'text-muted' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-default" title="{{ $user->status === 'active' ? 'Suspend' : 'Activate' }}">
                                        <i class="fas fa-ban {{ $user->status === 'suspended' ? 'text-danger' : 'text-muted' }}"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-users-slash fa-2x mb-2 d-block"></i>
                            No users found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="card-footer clearfix border-top">
            <div class="float-right">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
            <div class="float-left text-muted text-sm pt-2">
                Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ number_format($users->total()) }} users
            </div>
        </div>
    @endif
</div>
@endsection
