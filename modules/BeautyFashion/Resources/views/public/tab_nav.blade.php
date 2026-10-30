@php
    $bf = \Illuminate\Support\Facades\DB::table('product_details')->where('product_id', $product->id)->first();
    $bfChart = $bf && $bf->size_chart_id ? \Illuminate\Support\Facades\DB::table('size_charts')->where('id', $bf->size_chart_id)->first() : null;
    $bfBeauty = $bf && ($bf->ingredients || $bf->how_to_use || $bf->benefits);
    $bfFashion = $bf && ($bf->material || $bf->care_instructions || $bf->model_info || $bfChart);
@endphp
@if ($bfBeauty)
    <li class="nav-item" role="presentation"><a href="#bf-beauty" data-bs-toggle="tab" class="nav-link">Ingredients &amp; Usage</a></li>
@endif
@if ($bfFashion)
    <li class="nav-item" role="presentation"><a href="#bf-fashion" data-bs-toggle="tab" class="nav-link">Fabric &amp; Care</a></li>
@endif
