@php
    $bcProduct = \Illuminate\Support\Facades\DB::table('products')->where('id', $product->id)->first(['id', 'barcode']);
    $bcVariants = \Illuminate\Support\Facades\DB::table('product_variants')->where('product_id', $product->id)->whereNull('deleted_at')->orderBy('position')->get(['id', 'name', 'barcode']);
@endphp
<div class="box box-primary" id="pos-barcode-box" style="margin-bottom:15px">
    <div class="box-header with-border"><h4 style="margin:0">Barcode (POS) <small>scan with a barcode reader, or type, then press Enter</small></h4></div>
    <div class="box-body">
        @if ($bcVariants->isEmpty())
            <div class="input-group" style="max-width:520px">
                <input type="text" class="form-control pos-bc" data-type="product" data-id="{{ $bcProduct->id }}" value="{{ $bcProduct->barcode }}" placeholder="Scan barcode...">
                <span class="input-group-btn"><button type="button" class="btn btn-default pos-gen">Generate</button></span>
            </div>
        @else
            <p class="text-muted">Each variant has its own barcode.</p>
            @foreach ($bcVariants as $v)
                <div class="input-group" style="max-width:520px;margin-bottom:6px">
                    <span class="input-group-addon" style="min-width:150px;text-align:left">{{ $v->name }}</span>
                    <input type="text" class="form-control pos-bc" data-type="variant" data-id="{{ $v->id }}" value="{{ $v->barcode }}" placeholder="Scan barcode...">
                    <span class="input-group-btn"><button type="button" class="btn btn-default pos-gen">Generate</button></span>
                </div>
            @endforeach
        @endif
        <a class="btn btn-default btn-sm" target="_blank" href="{{ route('admin.pos.labels', ['p' => $product->id, 'copies' => 1, 'print' => 1]) }}" style="margin-top:8px">Print barcode label</a>
        <span id="pos-bc-msg" class="text-success" style="margin-left:10px"></span>
    </div>
</div>
<script>
(function () {
    function ean(seed) { var d = ('200' + String(seed).padStart(9, '0')).slice(0, 12), s = 0; d.split('').forEach(function (c, i) { s += c * (i % 2 ? 3 : 1); }); return d + ((10 - s % 10) % 10); }
    function save(inp) {
        fetch('{{ route('admin.pos.barcodes.save') }}', {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: JSON.stringify({type: inp.dataset.type, id: inp.dataset.id, barcode: inp.value})})
        .then(function (r) { return r.json().then(function (d) { return [r.ok, d]; }); })
        .then(function (x) { document.getElementById('pos-bc-msg').textContent = x[0] ? 'Barcode saved' : x[1].message; document.getElementById('pos-bc-msg').className = x[0] ? 'text-success' : 'text-danger'; });
    }
    document.querySelectorAll('#pos-barcode-box .pos-bc').forEach(function (i) { i.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); save(i); } }); i.addEventListener('change', function () { save(i); }); });
    document.querySelectorAll('#pos-barcode-box .pos-gen').forEach(function (b) { b.addEventListener('click', function () { var i = b.closest('.input-group').querySelector('.pos-bc'); i.value = ean(Date.now() % 1000000000); save(i); }); });
})();
</script>
