@php
    $bfD = \Illuminate\Support\Facades\DB::table('product_details')->where('product_id', $product->id)->first();
    $bfCharts = \Illuminate\Support\Facades\DB::table('size_charts')->orderBy('name')->get();
@endphp
<div class="box box-primary" id="bf-box" style="margin-bottom:15px">
    <div class="box-header with-border" style="cursor:pointer" onclick="document.getElementById('bf-body').style.display = document.getElementById('bf-body').style.display === 'none' ? '' : 'none'"><h4 style="margin:0">Beauty &amp; Fashion details <small>ingredients, how to use, material, care, size guide (click to expand)</small></h4></div>
    <div class="box-body" id="bf-body" style="display:none">
        <form id="bf-form" action="{{ route('admin.beauty.details.update', $product->id) }}">
            @include('beautyfashion::admin._details_fields', ['d' => $bfD, 'charts' => $bfCharts])
            <button type="submit" class="btn btn-primary">Save details</button> <span id="bf-msg" style="margin-left:10px"></span>
        </form>
    </div>
</div>
<script>
document.getElementById('bf-form').addEventListener('submit', function (e) {
    e.preventDefault(); var fd = new FormData(e.target); fd.append('_method', 'PUT'); fd.append('_token', '{{ csrf_token() }}');
    fetch(e.target.action, {method: 'POST', body: fd, headers: {'Accept': 'application/json'}}).then(function (r) { var m = document.getElementById('bf-msg'); m.textContent = r.ok ? 'Saved' : 'Could not save'; m.style.color = r.ok ? '#1f9d63' : '#d32f2f'; });
});
</script>
