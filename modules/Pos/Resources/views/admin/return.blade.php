@extends('admin::layout')
@section('title', 'Return / Exchange')
@section('content_header')
    <h3>Return / Exchange</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li><a href="{{ route('admin.pos.index') }}">POS</a></li><li class="active">Return / Exchange</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div class="box box-primary"><div class="box-header"><h4>Bill {{ $sale->invoice_no }} &middot; {{ \Modules\Pos\Services\Pos::money($order->total) }}</h4></div>
    <form method="POST" action="{{ route('admin.pos.return.store', $order->id) }}">{{ csrf_field() }}
    <div class="box-body table-responsive"><table class="table">
        <thead><tr><th>Item</th><th>Sold</th><th>Returned</th><th>Return qty</th><th class="text-right">Line</th></tr></thead><tbody>
        @foreach ($lines as $l) @php $max = $l['qty'] - ($returned[$l['order_product_id']] ?? 0); @endphp
            <tr><td>{{ $l['name'] }} @if ($l['variant']) <small>({{ $l['variant'] }})</small> @endif</td><td>{{ $l['qty'] }}</td><td>{{ $returned[$l['order_product_id']] ?? 0 }}</td>
            <td><input type="number" min="0" max="{{ $max }}" name="qty[{{ $l['order_product_id'] }}]" value="0" class="form-control" style="width:90px" {{ $max < 1 ? 'disabled' : '' }}></td><td class="text-right">{{ number_format($l['line_total'], 2) }}</td></tr>
        @endforeach</tbody></table>
        <div class="form-inline"><select name="refund_method" class="form-control"><option value="cash">Refund cash</option><option value="card">Refund to card</option><option value="qr">Refund to wallet</option><option value="other">Store credit / other</option></select>
        <input name="reason" class="form-control" placeholder="Reason" style="width:260px"> <button class="btn btn-primary">Process return &amp; print</button></div>
        <p class="help-block">Returned items go back into stock. Refund amount is the discounted, tax-inclusive value actually paid.</p></div></form></div>
    @if ($returns->count())<div class="box"><div class="box-header"><h4>Previous returns</h4></div><div class="box-body"><table class="table">
        @foreach ($returns as $r)<tr><td>{{ $r->return_no }}</td><td>{{ $r->created_at }}</td><td>{{ \Modules\Pos\Services\Pos::money($r->amount) }} ({{ $r->refund_method }})</td><td><a target="_blank" href="{{ route('admin.pos.receipt', [$order->id, 'return' => $r->id, 'print' => 1]) }}">Print</a></td></tr>@endforeach</table></div></div>@endif
@endsection
