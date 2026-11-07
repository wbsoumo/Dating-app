@extends('admin.layouts.app')

@section('title', 'Star Packages')
@section('page_title', 'Star & Super Like Coin Packages')

@section('breadcrumb')
    <li class="breadcrumb-item active">Star Packages</li>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Manage consumable star packs used for sending Super Likes and profile highlights.</p>
    <div>
        <a href="{{ route('admin.stars.transactions') }}" class="btn btn-default btn-sm mr-2">
            <i class="fas fa-receipt mr-1"></i> Transaction Log
        </a>
        <a href="{{ route('admin.stars.create') }}" class="btn btn-warning font-weight-bold btn-sm">
            <i class="fas fa-plus mr-1"></i> Add Star Package
        </a>
    </div>
</div>

<div class="row">
    @forelse($packages as $pkg)
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm border text-center">
                <div class="card-header bg-white border-0 pt-3 pb-0">
                    @if($pkg->badge)
                        <span class="badge badge-warning text-dark font-weight-bold mb-2">{{ $pkg->badge }}</span>
                    @else
                        <span class="badge badge-light border mb-2">Standard Pack</span>
                    @endif
                    <h5 class="font-weight-bold mb-0 text-dark">{{ $pkg->name }}</h5>
                </div>

                <div class="card-body py-3">
                    <div class="my-2">
                        <i class="fas fa-star fa-3x text-warning"></i>
                        <h2 class="font-weight-bold mt-2 mb-0">{{ number_format($pkg->stars_count) }}</h2>
                        <small class="text-muted text-uppercase font-weight-bold">Stars</small>
                    </div>

                    <div class="h4 font-weight-bold text-success mt-3 mb-0">
                        {{ $pkg->currency }} {{ number_format($pkg->price, 2) }}
                    </div>
                    @if($pkg->discount_percent > 0)
                        <small class="text-muted"><del>{{ $pkg->currency }} {{ number_format($pkg->price / (1 - $pkg->discount_percent/100), 2) }}</del> ({{ $pkg->discount_percent }}% OFF)</small>
                    @endif
                </div>

                <div class="card-footer bg-white border-top d-flex justify-content-between p-2">
                    <form action="{{ route('admin.stars.toggle-active', $pkg->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-{{ $pkg->is_active ? 'outline-secondary' : 'outline-success' }}">
                            {{ $pkg->is_active ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.stars.edit', $pkg->id) }}" class="btn btn-xs btn-default font-weight-bold">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="fas fa-star fa-3x mb-2 text-warning"></i>
            <h5>No star packages created yet.</h5>
            <a href="{{ route('admin.stars.create') }}" class="btn btn-warning mt-2">Create First Package</a>
        </div>
    @endforelse
</div>
@endsection
