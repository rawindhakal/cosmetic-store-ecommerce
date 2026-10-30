@extends('admin::layout')
@section('title', 'POS Sales')
@section('content_header')
    <h3>POS Sales</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li><a href="{{ route('admin.pos.index') }}">POS</a></li><li class="active">POS Sales</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div class="row">
        <div class="col-md-3"><div class="box box-primary"><div class="box-body"><h4>Today's bills</h4><h2>{{ $today->c }}</h2></div></div></div>
        <div class="col-md-3"><div class="box box-primary"><div class="box-body"><h4>Today's sales</h4><h2>{{ \Modules\Pos\Services\Pos::money($today->t) }}</h2></div></div></div>
        <div class="col-md-6 text-right" style="padding-top:20px"><a class="btn btn-primary btn-lg" href="{{ route('admin.pos.index') }}">Open POS terminal</a></div>
    </div>
    <div class="box box-primary">
        <div class="box-header"><form class="form-inline" method="GET">
            <input name="q" class="form-control" placeholder="Invoice, customer, phone" value="{{ request('q') }}"> <input type="date" name="date" class="form-control" value="{{ request('date') }}"> <button class="btn btn-default">Filter</button></form></div>
        <div class="box-body table-responsive"><table class="table table-striped">
            <thead><tr><th>Invoice</th><th>Date</th><th>Customer</th><th>Cashier</th><th>Payment</th><th class="text-right">Total</th><th>Status</th><th></th></tr></thead><tbody>
            @forelse ($sales as $s)
                <tr><td><b>{{ $s->invoice_no }}</b></td><td>{{ \Carbon\Carbon::parse($s->created_at)->format('d M Y h:i A') }}</td><td>{{ trim($s->customer_first_name.' '.$s->customer_last_name) }}</td><td>{{ $s->cashier_name }}</td>
                <td>@foreach (json_decode($s->payments, true) as $p) {{ $p['method'] === 'qr' ? 'QR' : ($p['method'] === 'bank' ? 'Bank' : ucfirst($p['method'])) }} @endforeach</td><td class="text-right">{{ \Modules\Pos\Services\Pos::money($s->total) }}</td><td>@if ($s->status === 'pending_payment')<span class="label label-warning">Awaiting verification</span>@else {{ ucwords(str_replace('_', ' ', $s->status)) }} @endif</td>
                <td class="text-right">@if ($s->status === 'pending_payment')<form method="POST" action="{{ route('admin.pos.verify', $s->order_id) }}" style="display:inline" onsubmit="return confirm('Confirm the money for {{ $s->invoice_no }} has arrived?')">{{ csrf_field() }}<button class="btn btn-success btn-sm">Verify payment</button></form> @endif<a class="btn btn-default btn-sm" target="_blank" href="{{ route('admin.pos.receipt', [$s->order_id, 'reprint' => 1, 'print' => 1]) }}">Reprint</a> <a class="btn btn-default btn-sm" href="{{ route('admin.pos.return', $s->order_id) }}">Return</a></td></tr>
            @empty <tr><td colspan="8" class="text-center">No POS sales yet.</td></tr> @endforelse
            </tbody></table>{{ $sales->links() }}</div>
    </div>
@endsection
