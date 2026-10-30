<?php

namespace Modules\BeautyFashion\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class DetailsController extends Controller
{
    public const FIELDS = ['ingredients', 'how_to_use', 'benefits', 'pao_months', 'shelf_life_months', 'net_content', 'country_of_origin', 'material', 'care_instructions', 'model_info', 'size_chart_id'];

    public function index()
    {
        $q = trim((string) request('q'));

        $rows = DB::table('products')->join('product_translations as t', fn ($j) => $j->on('t.product_id', '=', 'products.id')->where('t.locale', 'en'))
            ->leftJoin('product_details as d', 'd.product_id', '=', 'products.id')->whereNull('products.deleted_at')
            ->when($q, fn ($x) => $x->where('t.name', 'like', "%$q%"))
            ->when(request('missing'), fn ($x) => $x->whereNull('d.product_id'))
            ->orderBy('t.name')->select('products.id', 't.name', 'd.ingredients', 'd.material', 'd.size_chart_id', 'd.pao_months')->paginate(30)->withQueryString();

        return view('beautyfashion::admin.details_index', compact('rows'));
    }

    public function edit($id)
    {
        $p = DB::table('products')->join('product_translations as t', fn ($j) => $j->on('t.product_id', '=', 'products.id')->where('t.locale', 'en'))->where('products.id', $id)->firstOrFail(['products.id', 't.name']);

        return view('beautyfashion::admin.details_edit', ['p' => $p, 'd' => DB::table('product_details')->where('product_id', $id)->first(), 'charts' => DB::table('size_charts')->orderBy('name')->get()]);
    }

    public function update($id)
    {
        $data = request()->validate([
            'ingredients' => 'nullable|string|max:3000', 'how_to_use' => 'nullable|string|max:3000', 'benefits' => 'nullable|string|max:3000',
            'pao_months' => 'nullable|integer|min:0|max:120', 'shelf_life_months' => 'nullable|integer|min:0|max:120', 'net_content' => 'nullable|string|max:60',
            'country_of_origin' => 'nullable|string|max:80', 'material' => 'nullable|string|max:190', 'care_instructions' => 'nullable|string|max:2000',
            'model_info' => 'nullable|string|max:190', 'size_chart_id' => 'nullable|integer',
        ]);
        $data = array_map(fn ($v) => $v === '' ? null : $v, $data);

        DB::table('product_details')->updateOrInsert(['product_id' => $id], $data + ['updated_at' => now(), 'created_at' => now()]);

        return request()->expectsJson() ? response()->json(['ok' => true]) : back()->with('success', 'Details saved.');
    }
}
