@extends('admin.layouts.app')

@section('title', 'Platform Analytics Dashboard')
@section('page_title', 'Analytics & Overview')

@section('content')
<!-- Row 1: KPI Stat Boxes -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-info">
            <div class="inner">
                <h3>{{ number_format($totalUsers) }}</h3>
                <p>Total Registered Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                Manage Users <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-danger">
            <div class="inner">
                <h3>{{ number_format($totalMatches) }}</h3>
                <p>Mutual Matches Created</p>
            </div>
            <div class="icon">
                <i class="fas fa-heart"></i>
            </div>
            <a href="{{ route('admin.matches.index') }}" class="small-box-footer">
                View Matches <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-success">
            <div class="inner">
                <h3>{{ $currency }}{{ number_format($totalRevenue, 2) }}</h3>
                <p>Total Revenue (Razorpay)</p>
            </div>
            <div class="icon">
                <i class="fas fa-indian-rupee-sign"></i>
            </div>
            <a href="{{ route('admin.payments.index') }}" class="small-box-footer">
                View Transactions <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-gradient-warning text-white">
            <div class="inner text-white">
                <h3>{{ number_format($activeSubscriptions) }}</h3>
                <p>Active VIP Subscriptions</p>
            </div>
            <div class="icon">
                <i class="fas fa-crown"></i>
            </div>
            <a href="{{ route('admin.subscriptions.users') }}" class="small-box-footer text-white">
                View Subscribers <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Row 2: Secondary Quick Stats -->
<div class="row mb-3">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-mars"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">Male Users</span>
                <span class="info-box-number font-weight-bold">{{ number_format($maleUsers) }} <small class="text-muted">({{ $totalUsers > 0 ? round(($maleUsers/$totalUsers)*100, 1) : 0 }}%)</small></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-pink elevation-1" style="background-color: #ec4899; color: #fff;"><i class="fas fa-venus"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">Female Users</span>
                <span class="info-box-number font-weight-bold">{{ number_format($femaleUsers) }} <small class="text-muted">({{ $totalUsers > 0 ? round(($femaleUsers/$totalUsers)*100, 1) : 0 }}%)</small></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-badge-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">Verified Profiles</span>
                <span class="info-box-number font-weight-bold">{{ number_format($verifiedUsers) }} <small class="text-muted">({{ number_format($unverifiedUsers) }} unverified)</small></span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-md-3">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-secondary elevation-1"><i class="fas fa-database"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">Demo Accounts</span>
                <span class="info-box-number font-weight-bold">{{ number_format($demoUsers) }} <small><a href="{{ route('admin.demo-data.index') }}" class="text-danger ml-1">Manage</a></small></span>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Interactive Analytics Charts -->
<div class="row">
    <!-- Daily Registrations & Revenue Chart -->
    <div class="col-lg-8">
        <div class="card card-outline card-primary">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-chart-line mr-1 text-primary"></i> 7-Day Growth Trends (Users & Revenue)
                </h3>
                <div class="card-tools">
                    <span class="badge badge-light border">Daily Aggregation</span>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative mb-4">
                    <canvas id="growthChart" height="260"></canvas>
                </div>
                <div class="d-flex flex-row justify-content-end text-sm">
                    <span class="mr-3">
                        <i class="fas fa-square text-primary"></i> Registrations
                    </span>
                    <span>
                        <i class="fas fa-square text-success"></i> Revenue ({{ $currency }})
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Demographics & Verification Doughnut Charts -->
    <div class="col-lg-4">
        <div class="card card-outline card-danger">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-venus-mars mr-1 text-danger"></i> Gender Split
                </h3>
            </div>
            <div class="card-body">
                <canvas id="genderChart" height="220"></canvas>
                <div class="mt-3 text-center text-sm">
                    <span class="badge badge-primary mr-2"><i class="fas fa-circle mr-1"></i> Male: {{ $maleUsers }}</span>
                    <span class="badge badge-pink mr-2" style="background:#ec4899; color:#fff;"><i class="fas fa-circle mr-1"></i> Female: {{ $femaleUsers }}</span>
                    @if($otherUsers > 0)
                        <span class="badge badge-secondary"><i class="fas fa-circle mr-1"></i> Other: {{ $otherUsers }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Recent Registrations & Recent Transactions Tables -->
<div class="row">
    <!-- Recent Users -->
    <div class="col-lg-6">
        <div class="card card-outline card-info">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-user-plus mr-1 text-info"></i> Recent Registrations
                </h3>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-info">View All</a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>City / Age</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentUsers as $user)
                        <tr>
                            <td>
                                @php
                                    $avatar = $user->photos->where('is_primary', true)->first()?->url ?? $user->photos->first()?->url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name);
                                @endphp
                                <img src="{{ $avatar }}" alt="{{ $user->name }}" class="img-circle img-size-32 mr-2" style="width:32px; height:32px; object-fit:cover;">
                                <span class="font-weight-bold">{{ $user->name }}</span>
                                @if($user->is_verified)
                                    <i class="fas fa-check-circle text-primary text-xs" title="Verified"></i>
                                @endif
                                @if($user->is_demo)
                                    <span class="badge badge-secondary text-xs">Demo</span>
                                @endif
                            </td>
                            <td>
                                {{ $user->location?->city ?? 'N/A' }} / {{ $user->age ?? 'N/A' }} yrs
                            </td>
                            <td>
                                @if($user->status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-danger">{{ ucfirst($user->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-xs btn-default">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted">No registrations found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Razorpay Transactions -->
    <div class="col-lg-6">
        <div class="card card-outline card-success">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-receipt mr-1 text-success"></i> Recent Razorpay Payments
                </h3>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($recentTransactions as $tx)
                        <tr>
                            <td>
                                <span class="font-weight-bold">{{ $tx->user?->name ?? 'Guest/Deleted' }}</span>
                            </td>
                            <td>
                                <span class="badge badge-light border">
                                    {{ $tx->item_type === 'subscription' ? 'Plan' : 'Stars' }}
                                </span>
                            </td>
                            <td class="font-weight-bold text-success">
                                {{ $currency }}{{ number_format($tx->amount, 2) }}
                            </td>
                            <td>
                                @if($tx->status === 'captured')
                                    <span class="badge badge-success">Paid</span>
                                @elseif($tx->status === 'refunded')
                                    <span class="badge badge-warning">Refunded</span>
                                @else
                                    <span class="badge badge-danger">{{ ucfirst($tx->status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted text-xs">
                                {{ $tx->created_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">No transactions recorded yet.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // Growth Chart (Dual Axis: Registrations & Revenue)
        const ctxGrowth = document.getElementById('growthChart').getContext('2d');
        new Chart(ctxGrowth, {
            type: 'bar',
            data: {
                labels: {!! json_encode($dateLabels) !!},
                datasets: [
                    {
                        label: 'Registrations',
                        data: {!! json_encode($registrationTrends) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.7)',
                        borderColor: '#3b82f6',
                        borderWidth: 1,
                        yAxisID: 'y',
                    },
                    {
                        type: 'line',
                        label: 'Revenue ({{ $currency }})',
                        data: {!! json_encode($revenueTrends) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Users' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Revenue ({{ $currency }})' }
                    }
                }
            }
        });

        // Gender Distribution Chart
        const genderCanvas = document.getElementById('genderChart');
        if (genderCanvas) {
            const ctxGender = genderCanvas.getContext('2d');
            const maleUsers = {{ (int) $maleUsers }};
            const femaleUsers = {{ (int) $femaleUsers }};
            const otherUsers = {{ (int) $otherUsers }};
            const totalUsers = maleUsers + femaleUsers + otherUsers;

            new Chart(ctxGender, {
                type: 'doughnut',
                data: {
                    labels: totalUsers > 0 ? ['Male', 'Female', 'Other'] : ['No Users Yet'],
                    datasets: [{
                        data: totalUsers > 0 ? [maleUsers, femaleUsers, otherUsers] : [1],
                        backgroundColor: totalUsers > 0 ? ['#3b82f6', '#ec4899', '#94a3b8'] : ['#cbd5e1'],
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 500
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }
    });
</script>
@endpush
