@extends('admin.layouts.app')

@section('title', "Edit Plan: {$plan->name}")
@section('page_title', "Edit Subscription Plan: {$plan->name}")

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.subscriptions.index') }}">Subscriptions</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-crown mr-1 text-warning"></i> Modify Subscription Plan</h3>
            </div>

            <form action="{{ route('admin.subscriptions.update', $plan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Plan Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name) }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Badge Color (HEX) <span class="text-danger">*</span></label>
                            <input type="text" name="badge_color" class="form-control" value="{{ old('badge_color', $plan->badge_color) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $plan->description) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $plan->price) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Currency <span class="text-danger">*</span></label>
                            <input type="text" name="currency" class="form-control" value="{{ old('currency', $plan->currency) }}" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Duration (Days) <span class="text-danger">*</span></label>
                            <input type="number" name="duration_days" class="form-control" value="{{ old('duration_days', $plan->duration_days) }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Daily Swipe Limit <span class="text-danger">*</span></label>
                            <input type="number" name="daily_swipe_limit" class="form-control" value="{{ old('daily_swipe_limit', $plan->daily_swipe_limit) }}" required>
                            <small class="text-muted">Enter <code>-1</code> for Unlimited Swipes per day.</small>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Daily Free Star Allowance <span class="text-danger">*</span></label>
                            <input type="number" name="daily_star_allowance" class="form-control" value="{{ old('daily_star_allowance', $plan->daily_star_allowance) }}" required>
                        </div>
                    </div>

                    <hr>
                    <h5 class="font-weight-bold mb-3">VIP Feature Entitlements</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="can_see_likes" name="can_see_likes" value="1" {{ old('can_see_likes', $plan->can_see_likes) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="can_see_likes">See Who Liked You</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="can_rewind" name="can_rewind" value="1" {{ old('can_rewind', $plan->can_rewind) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="can_rewind">Rewind Swipes</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="can_boost" name="can_boost" value="1" {{ old('can_boost', $plan->can_boost) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="can_boost">Profile Boost</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="is_active">Plan Active (Publicly Available)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top d-flex justify-content-between">
                    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-default">Cancel</a>
                    <button type="submit" class="btn btn-primary font-weight-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
