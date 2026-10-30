@php
    $bf = \Illuminate\Support\Facades\DB::table('product_details')->where('product_id', $product->id)->first();
    $bfChart = $bf && $bf->size_chart_id ? \Illuminate\Support\Facades\DB::table('size_charts')->where('id', $bf->size_chart_id)->first() : null;
    $bfBeauty = $bf && ($bf->ingredients || $bf->how_to_use || $bf->benefits);
    $bfFashion = $bf && ($bf->material || $bf->care_instructions || $bf->model_info || $bfChart);
@endphp
