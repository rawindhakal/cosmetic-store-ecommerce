@php
    $bf = \Illuminate\Support\Facades\DB::table('product_details')->where('product_id', $product->id)->first();
    $bfChart = $bf && $bf->size_chart_id ? \Illuminate\Support\Facades\DB::table('size_charts')->where('id', $bf->size_chart_id)->first() : null;
    $bfBeauty = $bf && ($bf->ingredients || $bf->how_to_use || $bf->benefits);
    $bfFashion = $bf && ($bf->material || $bf->care_instructions || $bf->model_info || $bfChart);
@endphp
<style>
.bf-pane h5{font-weight:700;margin:16px 0 6px}.bf-pane p{margin:0 0 6px;white-space:pre-line}
.bf-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;margin:8px 0 14px}.bf-grid div{background:#fff7fb;border:1px solid #f3dce8;border-radius:10px;padding:10px 12px}.bf-grid b{display:block;font-size:12px;color:#8a0f55;text-transform:uppercase;letter-spacing:.4px}
</style>
@if ($bfBeauty)
    <div id="bf-beauty" class="tab-pane bf-pane">
        <div class="bf-grid">
            @if ($bf->net_content)<div><b>Net content</b>{{ $bf->net_content }}</div>@endif
            @if ($bf->pao_months)<div><b>Use within</b>{{ $bf->pao_months }} months of opening (PAO)</div>@endif
            @if ($bf->shelf_life_months)<div><b>Shelf life</b>{{ $bf->shelf_life_months }} months from manufacture</div>@endif
            @if ($bf->country_of_origin)<div><b>Made in</b>{{ $bf->country_of_origin }}</div>@endif
        </div>
        @if ($bf->benefits)<h5>Benefits</h5><p>{{ $bf->benefits }}</p>@endif
        @if ($bf->how_to_use)<h5>How to use</h5><p>{{ $bf->how_to_use }}</p>@endif
        @if ($bf->ingredients)<h5>Ingredients</h5><p>{{ $bf->ingredients }}</p>@endif
        <p style="font-size:12px;color:#888;margin-top:14px">Patch-test before first use. Discontinue if irritation occurs. Store in a cool, dry place away from sunlight.</p>
    </div>
@endif
@if ($bfFashion)
    <div id="bf-fashion" class="tab-pane bf-pane">
        <div class="bf-grid">
            @if ($bf->material)<div><b>Material</b>{{ $bf->material }}</div>@endif
            @if ($bf->model_info)<div><b>Model</b>{{ $bf->model_info }}</div>@endif
            @if ($bf->country_of_origin)<div><b>Made in</b>{{ $bf->country_of_origin }}</div>@endif
        </div>
        @if ($bf->care_instructions)<h5>Care instructions</h5><p>{{ $bf->care_instructions }}</p>@endif
        @if ($bfChart)<h5>Size guide: {{ $bfChart->name }}</h5>@include('beautyfashion::public._chart', ['chart' => $bfChart])@endif
    </div>
@endif
