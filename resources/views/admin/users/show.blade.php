@extends('admin.layouts.app')

@section('title', "User: {$user->name}")
@section('page_title', "Profile: {$user->name}")

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')
<div class="row">
    <!-- Left Column: User Summary Card -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile text-center">
                @php
                    $primaryPhoto = $user->photos->where('is_primary', true)->first()?->url
                        ?? $user->photos->first()?->url
                        ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name);
                @endphp
                <img class="profile-user-img img-fluid img-circle shadow"
                     src="{{ $primaryPhoto }}"
                     alt="{{ $user->name }}"
                     style="width: 110px; height: 110px; object-fit: cover;">

                <h3 class="profile-username font-weight-bold mt-2 mb-0">
                    {{ $user->name }}
                    @if($user->is_verified)
                        <i class="fas fa-check-circle text-primary text-sm" title="Verified Badge"></i>
                    @endif
                </h3>

                <p class="text-muted mb-2">
                    {{ $user->profile?->occupation ?? 'Occupation Not Listed' }} &bull; {{ $user->age ?? 'N/A' }} yrs
                </p>

                <div class="mb-3">
                    @if($user->status === 'active')
                        <span class="badge badge-success">Active</span>
                    @elseif($user->status === 'suspended')
                        <span class="badge badge-warning">Suspended</span>
                    @else
                        <span class="badge badge-danger">{{ ucfirst($user->status) }}</span>
                    @endif

                    @if($user->hasActivePremium())
                        <span class="badge badge-warning text-dark ml-1"><i class="fas fa-crown"></i> VIP Member</span>
                    @else
                        <span class="badge badge-light border ml-1">Free Tier</span>
                    @endif

                    @if($user->is_demo)
                        <span class="badge badge-secondary ml-1">Demo Profile</span>
                    @endif
                </div>

                <ul class="list-group list-group-unbordered mb-3 text-left">
                    <li class="list-group-item">
                        <b>Email</b> <span class="float-right text-muted">{{ $user->email ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Phone</b> <span class="float-right text-muted">{{ $user->phone ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>City</b> <span class="float-right text-muted">{{ $user->location?->city ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Star Balance</b>
                        <span class="float-right font-weight-bold text-warning">
                            ⭐ {{ $user->starWallet?->balance ?? 0 }}
                            <button class="btn btn-xs btn-outline-warning ml-1" data-toggle="modal" data-target="#adjustStarsModal">Adjust</button>
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Daily Swipes Remaining</b>
                        <span class="float-right font-weight-bold">
                            {{ $user->getDailySwipeLimit() === -1 ? 'Unlimited (VIP)' : $user->getSwipesRemainingToday() . ' / ' . $user->getDailySwipeLimit() }}
                        </span>
                    </li>
                </ul>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm flex-fill mr-1">
                        <i class="fas fa-edit mr-1"></i> Edit Profile
                    </a>
                    <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="flex-fill mx-1">
                        @csrf
                        <button type="submit" class="btn btn-{{ $user->status === 'active' ? 'warning' : 'success' }} btn-sm w-100">
                            <i class="fas fa-power-off mr-1"></i> {{ $user->status === 'active' ? 'Suspend' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Verification Selfie Card if uploaded -->
        @if($user->verification_photo)
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold"><i class="fas fa-shield-alt mr-1"></i> Verification Selfie</h3>
                </div>
                <div class="card-body text-center p-2">
                    <img src="{{ $user->verification_photo }}" class="img-fluid rounded" style="max-height: 220px;" alt="Verification Photo">
                    <div class="mt-2">
                        @if($user->is_verified)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Approved</span>
                        @else
                            <form action="{{ route('admin.verifications.approve', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-success mr-1">Approve Badge</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Tabs (Details, Photos, Swipes, Matches, Transactions) -->
    <div class="col-md-8">
        <div class="card card-outline card-primary card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-about-tab" data-toggle="pill" href="#tab-about">About & Bio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-photos-tab" data-toggle="pill" href="#tab-photos">Photos ({{ $user->photos->count() }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-swipes-tab" data-toggle="pill" href="#tab-swipes">Swipes & Matches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-wallet-tab" data-toggle="pill" href="#tab-wallet">Star Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-payments-tab" data-toggle="pill" href="#tab-payments">Payments ({{ $user->payments->count() }})</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Tab 1: About & Bio -->
                    <div class="tab-pane fade show active" id="tab-about">
                        <h5><strong>Bio</strong></h5>
                        <p class="text-muted">{{ $user->profile?->bio ?: 'No bio provided.' }}</p>
                        <hr>

                        <div class="row">
                            <div class="col-sm-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Education:</strong> {{ $user->profile?->education ?: 'N/A' }}</li>
                                    <li class="mb-2"><strong>Occupation:</strong> {{ $user->profile?->occupation ?: 'N/A' }}</li>
                                    <li class="mb-2"><strong>Height:</strong> {{ $user->profile?->height ? $user->profile->height . ' cm' : 'N/A' }}</li>
                                    <li class="mb-2"><strong>Religion:</strong> {{ $user->profile?->religion ?: 'N/A' }}</li>
                                </ul>
                            </div>
                            <div class="col-sm-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Relationship Goal:</strong> {{ ucfirst(str_replace('_', ' ', $user->profile?->relationship_goal ?? 'N/A')) }}</li>
                                    <li class="mb-2"><strong>Drinking:</strong> {{ ucfirst($user->profile?->drinking ?? 'N/A') }}</li>
                                    <li class="mb-2"><strong>Smoking:</strong> {{ ucfirst($user->profile?->smoking ?? 'N/A') }}</li>
                                    <li class="mb-2"><strong>Workout:</strong> {{ ucfirst($user->profile?->workout ?? 'N/A') }}</li>
                                    <li class="mb-2"><strong>Zodiac Sign:</strong> {{ $user->profile?->zodiac_sign ?: 'N/A' }}</li>
                                </ul>
                            </div>
                        </div>

                        <hr>
                        <h5><strong>Interests & Hobbies</strong></h5>
                        <div>
                            @forelse($user->interests as $interest)
                                <span class="badge badge-light border mr-1 mb-1 p-2">
                                    <i class="fas {{ $interest->icon ?? 'fa-tag' }} text-danger mr-1"></i> {{ $interest->name }}
                                </span>
                            @empty
                                <span class="text-muted">No interests selected.</span>
                            @endforelse
                        </div>

                        <hr>
                        <h5><strong>GPS Location & Discovery Preferences</strong></h5>
                        <p class="text-muted mb-1">
                            Coordinates: <code>{{ $user->location?->latitude ?? 'N/A' }}, {{ $user->location?->longitude ?? 'N/A' }}</code> ({{ $user->location?->city ?? 'N/A' }}, {{ $user->location?->state ?? 'N/A' }})
                        </p>
                        <p class="text-muted text-sm">
                            Discovery Preferences: Gender Preference: <b>{{ ucfirst($user->preferences?->gender_preference ?? 'All') }}</b> | Age Range: <b>{{ $user->preferences?->min_age ?? 18 }} - {{ $user->preferences?->max_age ?? 60 }} yrs</b> | Max Distance: <b>{{ $user->preferences?->max_distance_km ?? 50 }} km</b>
                        </p>
                    </div>

                    <!-- Tab 2: Photos -->
                    <div class="tab-pane fade" id="tab-photos">
                        <div class="row">
                            @forelse($user->photos as $photo)
                                <div class="col-sm-4 mb-3">
                                    <div class="card border shadow-sm">
                                        <img src="{{ $photo->url }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="Photo">
                                        <div class="card-footer p-2 text-center">
                                            @if($photo->is_primary)
                                                <span class="badge badge-primary"><i class="fas fa-star mr-1"></i> Primary Avatar</span>
                                            @else
                                                <span class="badge badge-light border">Secondary Photo</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center py-4 text-muted">No photos uploaded yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tab 3: Swipes & Matches -->
                    <div class="tab-pane fade" id="tab-swipes">
                        <h6 class="font-weight-bold">Recent Swipes Made ({{ $user->swipes->count() }})</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Target User</th>
                                        <th>Swipe Type</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->swipes->take(10) as $swipe)
                                        <tr>
                                            <td>
                                                <span class="font-weight-bold">{{ $swipe->targetUser?->name ?? 'Deleted' }}</span>
                                            </td>
                                            <td>
                                                @if($swipe->type === 'like')
                                                    <span class="badge badge-success"><i class="fas fa-heart mr-1"></i> Like</span>
                                                @elseif($swipe->type === 'star')
                                                    <span class="badge badge-warning text-dark"><i class="fas fa-star mr-1"></i> Super Like</span>
                                                @else
                                                    <span class="badge badge-secondary"><i class="fas fa-times mr-1"></i> Pass</span>
                                                @endif
                                            </td>
                                            <td class="text-xs text-muted">{{ $swipe->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-muted text-center py-2">No swipes made.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 4: Wallet Transactions -->
                    <div class="tab-pane fade" id="tab-wallet">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="font-weight-bold mb-0">Star Wallet Audit Trail (Current: {{ $user->starWallet?->balance ?? 0 }} Stars)</h6>
                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#adjustStarsModal">
                                <i class="fas fa-coins mr-1"></i> Adjust Balance
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Balance After</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->starTransactions->take(15) as $tx)
                                        <tr>
                                            <td>
                                                <span class="badge badge-{{ $tx->type === 'credit' ? 'success' : 'danger' }}">
                                                    {{ strtoupper($tx->type) }}
                                                </span>
                                            </td>
                                            <td class="font-weight-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                                {{ $tx->type === 'credit' ? '+' : '-' }}{{ $tx->amount }}
                                            </td>
                                            <td>{{ $tx->balance_after }}</td>
                                            <td class="text-muted">{{ $tx->description }}</td>
                                            <td class="text-xs text-muted">{{ $tx->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center py-3 text-muted">No transactions recorded.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 5: Payments -->
                    <div class="tab-pane fade" id="tab-payments">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Payment ID</th>
                                        <th>Item</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->payments as $payment)
                                        <tr>
                                            <td><code>{{ $payment->order_id }}</code></td>
                                            <td><code>{{ $payment->payment_id ?? 'N/A' }}</code></td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $payment->item_type)) }}</td>
                                            <td class="font-weight-bold">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $payment->status === 'captured' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                            <td class="text-xs text-muted">{{ $payment->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-3 text-muted">No payment records found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Adjust Stars -->
<div class="modal fade" id="adjustStarsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.users.adjust-stars', $user->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-coins text-warning mr-1"></i> Adjust Star Balance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Current Balance: <strong>{{ $user->starWallet?->balance ?? 0 }} Stars</strong></p>

                    <div class="form-group">
                        <label>Action</label>
                        <select name="action" class="form-control" required>
                            <option value="credit">Credit (Add Stars)</option>
                            <option value="debit">Debit (Deduct Stars)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Amount (Stars)</label>
                        <input type="number" name="amount" class="form-control" min="1" max="10000" value="10" required>
                    </div>

                    <div class="form-group">
                        <label>Reason / Audit Note</label>
                        <input type="text" name="note" class="form-control" placeholder="e.g. Promotional Bonus, Customer Care issue resolution" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning font-weight-bold">Save Adjustment</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
