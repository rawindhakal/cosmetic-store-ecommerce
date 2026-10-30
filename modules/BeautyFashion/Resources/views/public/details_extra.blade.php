@php
    $bf = \Illuminate\Support\Facades\DB::table('product_details')->where('product_id', $product->id)->first();
    $bfChart = $bf && $bf->size_chart_id ? \Illuminate\Support\Facades\DB::table('size_charts')->where('id', $bf->size_chart_id)->first() : null;
    $bfBeauty = $bf && ($bf->ingredients || $bf->how_to_use || $bf->benefits);
    $bfFashion = $bf && ($bf->material || $bf->care_instructions || $bf->model_info || $bfChart);
@endphp
@if ($bfChart || ($bf && $bf->pao_months))
    <div style="margin:10px 0 4px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
        @if ($bfChart)
            <a href="#" onclick="event.preventDefault();document.getElementById('bf-modal').style.display='flex'" style="display:inline-flex;align-items:center;gap:6px;font-weight:700;color:#c2127a;text-decoration:underline">&#128207; Size guide</a>
        @endif
        @if ($bf && $bf->pao_months)
            <span style="background:#fff7fb;border:1px solid #f3dce8;border-radius:999px;padding:4px 12px;font-size:12.5px;font-weight:700;color:#8a0f55">&#9203; Use within {{ $bf->pao_months }}M of opening</span>
        @endif
    </div>
    @if ($bfChart)
        <div id="bf-modal" onclick="if(event.target===this)this.style.display='none'" style="display:none;position:fixed;inset:0;background:#241b2199;z-index:9999;align-items:center;justify-content:center;padding:16px">
            <div style="background:#fff;border-radius:16px;max-width:620px;width:100%;max-height:90vh;overflow:auto;padding:20px 22px">
                <div style="display:flex;justify-content:space-between;align-items:center"><h4 style="margin:0">Size guide &mdash; {{ $bfChart->name }}</h4><button type="button" onclick="document.getElementById('bf-modal').style.display='none'" style="border:0;background:#f2e9ee;border-radius:50%;width:34px;height:34px;font-size:18px">&times;</button></div>
                <div style="margin-top:12px">@include('beautyfashion::public._chart', ['chart' => $bfChart])</div>
                <p style="font-size:12px;color:#777">Tip: measure over light clothing. If you're between two sizes, choose the larger one.</p>
            </div>
        </div>
    @endif
@endif
