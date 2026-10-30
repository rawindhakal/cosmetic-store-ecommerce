@extends('admin::layout')
@section('title', 'Barcodes & Labels')
@section('content_header')
    <h3>Barcodes & Labels</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li><a href="{{ route('admin.pos.index') }}">POS</a></li><li class="active">Barcodes & Labels</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div class="box box-primary"><div class="box-header">
        <form class="form-inline" method="GET" style="display:inline-block">
            <input id="scan-q" name="q" class="form-control" placeholder="Search or SCAN a barcode here" value="{{ request('q') }}" autofocus>
            <label style="margin:0 8px"><input type="checkbox" name="missing" value="1" {{ request('missing') ? 'checked' : '' }}> Missing barcode only</label>
            <button class="btn btn-default">Find</button></form>
        <form method="POST" action="{{ route('admin.pos.barcodes.generate') }}" style="display:inline-block;margin-left:12px">{{ csrf_field() }}<button class="btn btn-primary">Generate all missing barcodes</button></form>
        <button class="btn btn-default" id="print-sel" type="button">Print labels for selected</button>
        <span class="text-muted" style="margin-left:8px">Scan into any barcode box and press Enter to save.</span>
    </div>
    <div class="box-body table-responsive"><table class="table table-striped" id="bc-table">
        <thead><tr><th style="width:30px"><input type="checkbox" id="all"></th><th>Product</th><th>SKU</th><th>Price</th><th>Stock</th><th style="width:300px">Barcode</th></tr></thead><tbody>
        @foreach ($products as $p)
            <tr><td><input type="checkbox" class="sel" value="{{ $p->id }}"></td><td><b>{{ $p->name }}</b></td><td>{{ $p->sku }}</td><td>{{ number_format($p->selling_price, 2) }}</td><td>{{ $variants->has($p->id) ? '(variants)' : $p->qty }}</td>
            <td>@if (!$variants->has($p->id))
                <div class="input-group"><input class="form-control bc" data-type="product" data-id="{{ $p->id }}" value="{{ $p->barcode }}" placeholder="scan / type"><span class="input-group-btn"><button class="btn btn-default gen" type="button" title="Generate">&#9881;</button></span></div>
            @else <span class="text-muted">per variant below</span> @endif</td></tr>
            @foreach ($variants->get($p->id, collect()) as $v)
                <tr style="background:#fcf6fa"><td></td><td style="padding-left:30px">&#8627; {{ $v->name }}</td><td>{{ $v->sku }}</td><td>{{ number_format($v->selling_price ?? $v->price, 2) }}</td><td>{{ $v->manage_stock ? $v->qty : '-' }}</td>
                <td><div class="input-group"><input class="form-control bc" data-type="variant" data-id="{{ $v->id }}" value="{{ $v->barcode }}" placeholder="scan / type"><span class="input-group-btn"><button class="btn btn-default gen" type="button">&#9881;</button></span></div></td></tr>
            @endforeach
        @endforeach</tbody></table>{{ $products->links() }}</div></div>
@endsection
@push('scripts')
<script>
(function () {
    var token = '{{ csrf_token() }}';
    function ean(seed) { var d = ('200' + String(seed).padStart(9, '0')).slice(0, 12), s = 0; d.split('').forEach(function (c, i) { s += c * (i % 2 ? 3 : 1); }); return d + ((10 - s % 10) % 10); }
    function save(inp) {
        fetch('{{ route('admin.pos.barcodes.save') }}', {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token}, body: JSON.stringify({type: inp.dataset.type, id: inp.dataset.id, barcode: inp.value})})
            .then(function (r) { return r.json().then(function (d) { return [r.ok, d]; }); })
            .then(function (x) { inp.style.outline = x[0] ? '2px solid #1f9d63' : '2px solid #d32f2f'; if (!x[0]) alert(x[1].message); });
    }
    document.querySelectorAll('.bc').forEach(function (i) { i.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); save(i); var all = [].slice.call(document.querySelectorAll('.bc')); var n = all[all.indexOf(i) + 1]; if (n) n.focus(); } }); i.addEventListener('change', function () { save(i); }); });
    document.querySelectorAll('.gen').forEach(function (b) { b.addEventListener('click', function () { var i = b.closest('.input-group').querySelector('.bc'); i.value = ean(Date.now() % 1000000000); save(i); }); });
    document.getElementById('all').addEventListener('change', function (e) { document.querySelectorAll('.sel').forEach(function (c) { c.checked = e.target.checked; }); });
    document.getElementById('print-sel').addEventListener('click', function () { var ids = [].slice.call(document.querySelectorAll('.sel:checked')).map(function (c) { return c.value; }); if (!ids.length) return alert('Select products first.'); var n = prompt('Copies per label', '1') || 1; window.open('{{ route('admin.pos.labels') }}?p=' + ids.join(',') + '&copies=' + n, '_blank'); });
})();
</script>
@endpush
