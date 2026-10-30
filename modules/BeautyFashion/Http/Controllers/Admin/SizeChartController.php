<?php

namespace Modules\BeautyFashion\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SizeChartController extends Controller
{
    public function index()
    {
        return view('beautyfashion::admin.sizecharts', ['charts' => DB::table('size_charts')->orderBy('name')->get(), 'used' => DB::table('product_details')->whereNotNull('size_chart_id')->groupBy('size_chart_id')->selectRaw('size_chart_id, count(*) c')->pluck('c', 'size_chart_id')]);
    }

    public function create()
    {
        return view('beautyfashion::admin.sizechart_form', ['c' => null]);
    }

    public function edit($id)
    {
        return view('beautyfashion::admin.sizechart_form', ['c' => DB::table('size_charts')->where('id', $id)->firstOrFail()]);
    }

    private function payload(): array
    {
        $d = request()->validate(['name' => 'required|string|max:120', 'note' => 'nullable|string|max:190', 'headers' => 'required|string', 'rows' => 'required|string']);
        $headers = array_map('trim', explode(',', $d['headers']));
        $rows = collect(preg_split('/\r?\n/', trim($d['rows'])))->filter()->map(fn ($l) => array_map('trim', explode(',', $l)))->values()->all();

        return ['name' => $d['name'], 'note' => $d['note'] ?? null, 'headers' => json_encode($headers), 'rows' => json_encode($rows), 'updated_at' => now()];
    }

    public function store()
    {
        DB::table('size_charts')->insert($this->payload() + ['created_at' => now()]);

        return redirect()->route('admin.beauty.sizecharts')->with('success', 'Size chart created.');
    }

    public function update($id)
    {
        DB::table('size_charts')->where('id', $id)->update($this->payload());

        return redirect()->route('admin.beauty.sizecharts')->with('success', 'Size chart updated.');
    }

    public function destroy($id)
    {
        DB::table('product_details')->where('size_chart_id', $id)->update(['size_chart_id' => null]);
        DB::table('size_charts')->where('id', $id)->delete();

        return back()->with('success', 'Size chart deleted.');
    }
}
