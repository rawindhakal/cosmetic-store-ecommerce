@extends('admin::layout')

@section('title', $name)

@section('content_header')
    <h3>{{ $name }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li class="active">{{ $name }}</li>
    </ol>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4"><div class="box box-primary"><div class="box-body"><h4>Issued</h4><h2>{{ number_format($totalIssued) }}</h2></div></div></div>
        <div class="col-md-4"><div class="box box-primary"><div class="box-body"><h4>Redeemed</h4><h2>{{ number_format($totalRedeemed) }}</h2></div></div></div>
        <div class="col-md-4"><div class="box box-primary"><div class="box-body"><h4>Outstanding</h4><h2>{{ number_format($totalOutstanding) }}</h2></div></div></div>
    </div>

    <div class="box box-primary">
        <div class="box-header"><h4>Adjust a customer's balance</h4></div>
        <div class="box-body">
            <form method="POST" action="{{ route('admin.glowpoints.adjust') }}" class="form-inline">
                {{ csrf_field() }}
                <input type="email" name="email" class="form-control" placeholder="Customer email" value="{{ old('email') }}" required>
                <input type="number" name="points" class="form-control" placeholder="+100 or -50" value="{{ old('points') }}" required>
                <input type="text" name="note" class="form-control" placeholder="Note (optional)" value="{{ old('note') }}">
                <button class="btn btn-primary">Apply</button>
                @foreach ($errors->all() as $e) <div class="text-danger">{{ $e }}</div> @endforeach
            </form>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header">
            <form class="form-inline" method="GET"><input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Filter by email"> <button class="btn btn-default">Filter</button></form>
        </div>
        <div class="box-body table-responsive">
            <table class="table table-striped">
                <thead><tr><th>Date</th><th>Customer</th><th>Type</th><th>Points</th><th>Balance</th><th>Order</th><th>Description</th><th>Expires</th></tr></thead>
                <tbody>
                @forelse ($transactions as $t)
                    <tr>
                        <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $t->customer->email ?? '-' }}</td>
                        <td>{{ ucfirst($t->type) }}</td>
                        <td class="{{ $t->points > 0 ? 'text-success' : 'text-danger' }}">{{ $t->points > 0 ? '+' : '' }}{{ $t->points }}</td>
                        <td>{{ $t->balance_after }}</td>
                        <td>@if ($t->order_id)<a href="{{ route('admin.orders.show', $t->order_id) }}">#{{ $t->order_id }}</a>@endif</td>
                        <td>{{ $t->description }}</td>
                        <td>{{ $t->expires_at?->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center">No transactions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $transactions->links() }}
        </div>
    </div>
@endsection
