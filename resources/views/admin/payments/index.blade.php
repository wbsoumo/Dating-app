@extends('admin.layouts.app')

@section('title', 'Razorpay Payments & Financials')
@section('page_title', 'Razorpay Payment Transactions & Revenue')

@section('breadcrumb')
    <li class="breadcrumb-item active">Transactions</li>
@endsection

@section('content')
<!-- Financial KPI Cards -->
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-indian-rupee-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">Total Collections</span>
                <span class="info-box-number text-success font-weight-bold">{{ $currency }}{{ number_format($totalCollected, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-undo"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">Total Refunded</span>
                <span class="info-box-number text-warning font-weight-bold">{{ $currency }}{{ number_format($totalRefunded, 2) }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-primary elevation-1"><i class="fas fa-check-double"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">Captured Orders</span>
                <span class="info-box-number font-weight-bold">{{ number_format($countCaptured) }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="info-box shadow-sm border">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text text-muted">Failed Orders</span>
                <span class="info-box-number font-weight-bold text-danger">{{ number_format($countFailed) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card card-outline card-success shadow-sm">
    <div class="card-header border-0 pb-0">
        <!-- Filter Form & CSV Export -->
        <form method="GET" action="{{ route('admin.payments.index') }}" class="form-row align-items-center mb-3">
            <div class="col-md-3 col-sm-6 mb-2">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search order ID, payment ID, user..." value="{{ request('search') }}">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>

            <div class="col-md-2 col-sm-6 mb-2">
                <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="all">All Statuses</option>
                    <option value="captured" {{ request('status') === 'captured' ? 'selected' : '' }}>Captured / Paid</option>
                    <option value="created" {{ request('status') === 'created' ? 'selected' : '' }}>Created (Pending)</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                </select>
            </div>

            <div class="col-md-2 col-sm-6 mb-2">
                <select name="item_type" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="all">All Item Types</option>
                    <option value="subscription" {{ request('item_type') === 'subscription' ? 'selected' : '' }}>VIP Subscriptions</option>
                    <option value="star_package" {{ request('item_type') === 'star_package' ? 'selected' : '' }}>Star Packs</option>
                </select>
            </div>

            <div class="col-md-2 col-sm-6 mb-2">
                <select name="date_range" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="all">All Time</option>
                    <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                    <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                </select>
            </div>

            <div class="col-md-3 col-sm-12 mb-2 text-right">
                <a href="{{ route('admin.payments.export-csv', request()->all()) }}" class="btn btn-sm btn-outline-success mr-1">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV
                </a>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="fas fa-rotate"></i></a>
            </div>
        </form>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Order & Payment Ref</th>
                    <th>Item Purchased</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr>
                        <td>#{{ $p->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $p->user_id) }}" class="font-weight-bold text-dark">
                                {{ $p->user?->name ?? 'Deleted User' }}
                            </a>
                            <small class="text-muted d-block">{{ $p->user?->email }}</small>
                        </td>
                        <td>
                            <code class="text-xs d-block">Order: {{ $p->order_id }}</code>
                            @if($p->payment_id)
                                <code class="text-xs text-primary">Pay: {{ $p->payment_id }}</code>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-light border">
                                <i class="fas {{ $p->item_type === 'subscription' ? 'fa-crown text-warning' : 'fa-star text-warning' }} mr-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $p->item_type)) }} #{{ $p->item_id }}
                            </span>
                        </td>
                        <td class="font-weight-bold text-success">
                            {{ $p->currency }} {{ number_format($p->amount, 2) }}
                        </td>
                        <td>
                            <span class="text-xs text-muted text-uppercase">{{ $p->method ?? 'Razorpay Gateway' }}</span>
                        </td>
                        <td>
                            @if($p->status === 'captured')
                                <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Paid</span>
                            @elseif($p->status === 'refunded')
                                <span class="badge badge-warning text-dark"><i class="fas fa-undo mr-1"></i> Refunded</span>
                            @elseif($p->status === 'failed')
                                <span class="badge badge-danger" title="{{ $p->error_description }}"><i class="fas fa-exclamation-triangle mr-1"></i> Failed</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($p->status) }}</span>
                            @endif
                        </td>
                        <td class="text-xs text-muted">
                            {{ $p->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.payments.show', $p->id) }}" class="btn btn-xs btn-default" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($p->status === 'captured')
                                <button class="btn btn-xs btn-outline-danger" data-toggle="modal" data-target="#refundModal{{ $p->id }}" title="Process Refund">
                                    <i class="fas fa-undo"></i> Refund
                                </button>
                            @endif
                        </td>
                    </tr>

                    <!-- Refund Modal -->
                    @if($p->status === 'captured')
                        <div class="modal fade" id="refundModal{{ $p->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <form action="{{ route('admin.payments.refund', $p->id) }}" method="POST">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title font-weight-bold text-danger"><i class="fas fa-undo mr-1"></i> Issue Razorpay Refund</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Refund payment of <strong>{{ $p->currency }} {{ number_format($p->amount, 2) }}</strong> for {{ $p->user?->name }}:</p>
                                            <div class="form-group">
                                                <label>Refund Amount ({{ $p->currency }})</label>
                                                <input type="number" step="0.01" name="amount" class="form-control" value="{{ $p->amount }}" max="{{ $p->amount }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Refund Reason</label>
                                                <input type="text" name="reason" class="form-control" placeholder="e.g. Accidental double purchase, customer requested refund" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger btn-sm font-weight-bold">Execute Refund</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                            No payments found matching your filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payments->hasPages())
        <div class="card-footer clearfix border-top">
            <div class="float-right">
                {{ $payments->links('pagination::bootstrap-4') }}
            </div>
            <div class="float-left text-muted text-sm pt-2">
                Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ number_format($payments->total()) }} transactions
            </div>
        </div>
    @endif
</div>
@endsection
