@extends('admin.layouts.app')

@section('title', "Payment #{$payment->id}")
@section('page_title', "Transaction Receipt: #{$payment->id}")

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Transactions</a></li>
    <li class="breadcrumb-item active">#{{ $payment->id }}</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-file-invoice mr-1 text-success"></i> Transaction Details
                </h3>
                <div>
                    @if($payment->status === 'captured')
                        <span class="badge badge-success px-3 py-2"><i class="fas fa-check-circle mr-1"></i> Paid / Captured</span>
                    @elseif($payment->status === 'refunded')
                        <span class="badge badge-warning text-dark px-3 py-2"><i class="fas fa-undo mr-1"></i> Refunded</span>
                    @else
                        <span class="badge badge-danger px-3 py-2">{{ ucfirst($payment->status) }}</span>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-sm-6">
                        <h6 class="text-muted font-weight-bold">CUSTOMER DETAILS</h6>
                        <h5 class="font-weight-bold mb-1">{{ $payment->user?->name ?? 'Deleted User' }}</h5>
                        <p class="text-muted text-sm mb-0">Email: {{ $payment->user?->email ?? 'N/A' }}</p>
                        <p class="text-muted text-sm">Phone: {{ $payment->user?->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="col-sm-6 text-sm-right">
                        <h6 class="text-muted font-weight-bold">TRANSACTION META</h6>
                        <p class="mb-1"><strong>Date:</strong> {{ $payment->created_at->format('d M Y, h:i A') }}</p>
                        <p class="mb-1"><strong>Razorpay Order ID:</strong> <code>{{ $payment->order_id }}</code></p>
                        <p class="mb-0"><strong>Razorpay Payment ID:</strong> <code>{{ $payment->payment_id ?? 'N/A' }}</code></p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr>
                                <th>Item Purchased</th>
                                <th>Category</th>
                                <th class="text-right">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>{{ $item?->name ?? ucfirst(str_replace('_', ' ', $payment->item_type)) }}</strong>
                                    @if($item && isset($item->duration_days))
                                        <small class="text-muted d-block">{{ $item->duration_days }} days VIP access</small>
                                    @elseif($item && isset($item->stars_count))
                                        <small class="text-muted d-block">{{ $item->stars_count }} Super Like Stars</small>
                                    @endif
                                </td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->item_type)) }}</td>
                                <td class="text-right font-weight-bold">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-right font-weight-bold">Total Paid:</td>
                                <td class="text-right font-weight-bold text-success h5 mb-0">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if($payment->refunds->count() > 0)
                    <div class="mt-4">
                        <h6 class="text-danger font-weight-bold"><i class="fas fa-undo mr-1"></i> Refund History</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Refund ID</th>
                                    <th>Amount</th>
                                    <th>Reason</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->refunds as $rf)
                                    <tr>
                                        <td><code>{{ $rf->refund_id }}</code></td>
                                        <td class="text-danger font-weight-bold">{{ $payment->currency }} {{ number_format($rf->amount, 2) }}</td>
                                        <td>{{ $rf->reason }}</td>
                                        <td class="text-xs text-muted">{{ $rf->created_at->format('d M Y, h:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card-footer bg-white border-top d-flex justify-content-between">
                <a href="{{ route('admin.payments.index') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-1"></i> Back to Transactions</a>
                @if($payment->user)
                    <a href="{{ route('admin.users.show', $payment->user_id) }}" class="btn btn-primary"><i class="fas fa-user mr-1"></i> View Customer Profile</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
