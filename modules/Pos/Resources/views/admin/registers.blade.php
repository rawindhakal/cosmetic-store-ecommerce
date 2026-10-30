@extends('admin::layout')
@section('title', 'Cash Registers')
@section('content_header')
    <h3>Cash Registers</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li><a href="{{ route('admin.pos.index') }}">POS</a></li><li class="active">Cash Registers</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div class="box box-primary"><div class="box-body table-responsive"><table class="table table-striped">
        <thead><tr><th>#</th><th>Cashier</th><th>Opened</th><th>Closed</th><th>Opening</th><th>Expected</th><th>Counted</th><th>Variance</th><th></th></tr></thead><tbody>
        @forelse ($rows as $r)<tr><td>{{ $r->id }}</td><td>{{ $r->first_name }}</td><td>{{ $r->opened_at }}</td><td>{{ $r->closed_at ?: 'Open' }}</td><td>{{ number_format($r->opening_cash, 2) }}</td><td>{{ $r->expected_cash !== null ? number_format($r->expected_cash, 2) : '-' }}</td><td>{{ $r->closing_cash !== null ? number_format($r->closing_cash, 2) : '-' }}</td>
        <td class="{{ $r->closing_cash !== null && abs($r->closing_cash - $r->expected_cash) > 0.5 ? 'text-danger' : '' }}">{{ $r->closing_cash !== null ? number_format($r->closing_cash - $r->expected_cash, 2) : '-' }}</td><td><a href="{{ route('admin.pos.registers.show', $r->id) }}">Z-report</a></td></tr>
        @empty<tr><td colspan="9" class="text-center">No registers yet.</td></tr>@endforelse</tbody></table>{{ $rows->links() }}</div></div>
@endsection
