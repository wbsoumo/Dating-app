@extends('admin.layouts.app')

@section('title', "Edit User: {$user->name}")
@section('page_title', "Edit User Profile: {$user->name}")

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user->id) }}">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-user-edit mr-1"></i> Edit Profile Information</h3>
            </div>

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-control" required>
                                <option value="male" {{ old('gender', $user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                <option value="non_binary" {{ old('gender', $user->gender) === 'non_binary' ? 'selected' : '' }}>Non-Binary</option>
                                <option value="other" {{ old('gender', $user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" class="form-control" value="{{ old('dob', $user->dob?->toDateString()) }}">
                        </div>

                        <div class="col-md-6 form-group">
                            <label>Account Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="banned" {{ old('status', $user->status) === 'banned' ? 'selected' : '' }}>Banned</option>
                                <option value="deactivated" {{ old('status', $user->status) === 'deactivated' ? 'selected' : '' }}>Deactivated</option>
                            </select>
                        </div>
                    </div>

                    <hr>
                    <h5 class="font-weight-bold text-dark mb-3">Custom Swipe Quotas & Privilege Overrides</h5>

                    <div class="form-group">
                        <label>Custom Daily Swipe Limit Override</label>
                        <input type="number" name="custom_swipe_limit" class="form-control" value="{{ old('custom_swipe_limit', $user->custom_swipe_limit) }}" placeholder="Leave empty for plan/system default. Enter -1 for unlimited swipes.">
                        <small class="form-text text-muted">
                            Set <code>-1</code> for unlimited daily swipes. Leave blank to inherit system/plan default ({{ \App\Models\Setting::get('free_daily_swipes', 50) }} swipes/day for free users).
                        </small>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="is_verified" name="is_verified" value="1" {{ old('is_verified', $user->is_verified) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="is_verified">
                                    <i class="fas fa-certificate text-primary mr-1"></i> Verified Profile Badge
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="is_premium" name="is_premium" value="1" {{ old('is_premium', $user->is_premium) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="is_premium">
                                    <i class="fas fa-crown text-warning mr-1"></i> VIP / Premium Status
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top d-flex justify-content-between">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-default">Cancel</a>
                    <button type="submit" class="btn btn-primary font-weight-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
