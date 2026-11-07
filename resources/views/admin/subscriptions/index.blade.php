@extends('admin.layouts.app')

@section('title', 'Subscription Plans')
@section('page_title', 'VIP Subscription Plans & Tiers')

@section('breadcrumb')
    <li class="breadcrumb-item active">Subscription Plans</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Manage premium membership pricing, durations, swipe quotas, and VIP perks.</p>
    <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary font-weight-bold">
        <i class="fas fa-plus mr-1"></i> Create New Plan
    </a>
</div>

<div class="row">
    @forelse($plans as $plan)
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border" style="border-top: 4px solid {{ $plan->badge_color }} !important;">
                <div class="card-header bg-white d-flex justify-content-between align-items-center pt-3 pb-2">
                    <h5 class="font-weight-bold mb-0" style="color: {{ $plan->badge_color }};">
                        <i class="fas fa-crown mr-1"></i> {{ $plan->name }}
                    </h5>
                    @if($plan->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Disabled</span>
                    @endif
                </div>

                <div class="card-body">
                    <div class="text-center my-2">
                        <span class="h2 font-weight-bold">{{ $plan->currency }} {{ number_format($plan->price, 0) }}</span>
                        <span class="text-muted text-sm d-block">for {{ $plan->duration_days }} Days</span>
                    </div>

                    <p class="text-muted text-sm text-center mb-3">{{ $plan->description }}</p>

                    <ul class="list-unstyled text-sm border-top pt-3">
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            <strong>Swipes:</strong> {{ $plan->daily_swipe_limit === -1 ? 'Unlimited' : $plan->daily_swipe_limit . '/day' }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            <strong>Daily Stars:</strong> {{ $plan->daily_star_allowance }} Free Stars/day
                        </li>
                        <li class="mb-2">
                            <i class="fas {{ $plan->can_see_likes ? 'fa-check text-success' : 'fa-times text-muted' }} mr-2"></i>
                            See Who Liked You
                        </li>
                        <li class="mb-2">
                            <i class="fas {{ $plan->can_rewind ? 'fa-check text-success' : 'fa-times text-muted' }} mr-2"></i>
                            Rewind Last Swipe
                        </li>
                        <li class="mb-2">
                            <i class="fas {{ $plan->can_boost ? 'fa-check text-success' : 'fa-times text-muted' }} mr-2"></i>
                            Discovery Profile Boost
                        </li>
                    </ul>
                </div>

                <div class="card-footer bg-white border-top d-flex justify-content-between p-2">
                    <form action="{{ route('admin.subscriptions.toggle-active', $plan->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-{{ $plan->is_active ? 'outline-secondary' : 'outline-success' }}">
                            {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.subscriptions.edit', $plan->id) }}" class="btn btn-xs btn-default font-weight-bold">
                        <i class="fas fa-edit mr-1"></i> Edit Plan
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-crown fa-3x mb-2 text-warning"></i>
            <h5>No subscription plans created yet.</h5>
            <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary mt-2">Create First Plan</a>
        </div>
    @endforelse
</div>
@endsection
