@extends('admin.layouts.app')

@section('title', 'Star Wallet Transactions')
@section('page_title', 'Star Wallet Transaction Logs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.stars.index') }}">Star Packages</a></li>
    <li class="breadcrumb-item active">Transactions</li>
@endsection

@section('content')
<div class="card card-outline card-warning">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold">
            <i class="fas fa-receipt text-warning mr-1"></i> Star Credits & Debits Audit Trail
        </h3>
        <div class="card-tools">
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.stars.transactions', ['type' => 'all']) }}" class="btn {{ $type === 'all' ? 'btn-warning' : 'btn-default' }}">All</a>
                <a href="{{ route('admin.stars.transactions', ['type' => 'credit']) }}" class="btn {{ $type === 'credit' ? 'btn-warning' : 'btn-default' }}">Credits (+)</a>
                <a href="{{ route('admin.stars.transactions', ['type' => 'debit']) }}" class="btn {{ $type === 'debit' ? 'btn-warning' : 'btn-default' }}">Debits (-)</a>
            </div>
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Balance After</th>
                    <th>Description</th>
                    <th>Reference</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td>#{{ $tx->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $tx->user_id) }}" class="font-weight-bold text-dark">
                                {{ $tx->user?->name ?? 'Deleted User' }}
                            </a>
                            <small class="text-muted d-block">{{ $tx->user?->email }}</small>
                        </td>
                        <td>
                            <span class="badge badge-{{ $tx->type === 'credit' ? 'success' : 'danger' }}">
                                {{ strtoupper($tx->type) }}
                            </span>
                        </td>
                        <td class="font-weight-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                            {{ $tx->type === 'credit' ? '+' : '-' }}{{ $tx->amount }} ⭐
                        </td>
                        <td>{{ $tx->balance_after }} ⭐</td>
                        <td>{{ $tx->description }}</td>
                        <td>
                            <span class="badge badge-light border">
                                {{ ucfirst(str_replace('_', ' ', $tx->reference_type ?? 'N/A')) }}
                            </span>
                        </td>
                        <td class="text-xs text-muted">{{ $tx->created_at->format('d M Y, h:i:s A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No star transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="card-footer clearfix border-top">
            <div class="float-right">
                {{ $transactions->links('pagination::bootstrap-4') }}
            </div>
        </div>
    @endif
</div>
@endsection
