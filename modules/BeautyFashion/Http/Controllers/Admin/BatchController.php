<?php

namespace Modules\BeautyFashion\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function index()
    {
        $filter = request('f', 'attention');
        $today = today();

        $q = DB::table('product_batches as b')->join('product_translations as t', fn ($j) => $j->on('t.product_id', '=', 'b.product_id')->where('t.locale', 'en'))
            ->leftJoin('product_variants as v', 'v.id', '=', 'b.variant_id')
            ->select('b.*', 't.name', 'v.name as variant')
            ->when($filter === 'expired', fn ($x) => $x->where('b.expiry_date', '<', $today))
            ->when($filter === 'soon', fn ($x) => $x->whereBetween('b.expiry_date', [$today, $today->copy()->addDays(90)]))
            ->when($filter === 'attention', fn ($x) => $x->where('b.expiry_date', '<=', $today->copy()->addDays(90)))
            ->when(request('q'), fn ($x, $s) => $x->where(fn ($w) => $w->where('t.name', 'like', "%$s%")->orWhere('b.batch_no', 'like', "%$s%")))
            ->orderBy('b.expiry_date');

        return view('beautyfashion::admin.batches', [
            'batches' => $q->paginate(30)->withQueryString(), 'filter' => $filter,
            'expired' => DB::table('product_batches')->where('expiry_date', '<', $today)->count(),
            'soon' => DB::table('product_batches')->whereBetween('expiry_date', [$today, $today->copy()->addDays(90)])->count(),
            'total' => DB::table('product_batches')->count(),
            'expiredQty' => (int) DB::table('product_batches')->where('expiry_date', '<', $today)->sum('qty'),
            'products' => DB::table('products')->join('product_translations as t', fn ($j) => $j->on('t.product_id', '=', 'products.id')->where('t.locale', 'en'))->whereNull('products.deleted_at')->orderBy('t.name')->get(['products.id', 't.name', 'products.sku']),
        ]);
    }

    public function store()
    {
        $d = request()->validate(['product_id' => 'required|integer|exists:products,id', 'batch_no' => 'required|string|max:60', 'mfg_date' => 'nullable|date', 'expiry_date' => 'required|date', 'qty' => 'required|integer|min:1', 'note' => 'nullable|string|max:190', 'add_stock' => 'nullable']);

        DB::table('product_batches')->insert(collect($d)->except('add_stock')->all() + ['created_at' => now(), 'updated_at' => now()]);

        if (request()->boolean('add_stock')) {
            DB::table('products')->where('id', $d['product_id'])->where('manage_stock', 1)->update(['qty' => DB::raw('qty + ' . (int) $d['qty']), 'in_stock' => 1]);
        }

        return back()->with('success', 'Batch added.');
    }

    /** Remove an expired batch from stock (reduces the product quantity) and delete the batch. */
    public function writeOff($id)
    {
        $b = DB::table('product_batches')->where('id', $id)->firstOrFail();
        DB::table('products')->where('id', $b->product_id)->where('manage_stock', 1)->update(['qty' => DB::raw('GREATEST(qty - ' . (int) $b->qty . ', 0)')]);
        DB::table('products')->where('id', $b->product_id)->where('manage_stock', 1)->where('qty', '<=', 0)->update(['in_stock' => 0]);
        DB::table('product_batches')->where('id', $id)->delete();

        return back()->with('success', "Batch {$b->batch_no} written off ({$b->qty} units removed from stock).");
    }

    public function destroy($id)
    {
        DB::table('product_batches')->where('id', $id)->delete();

        return back()->with('success', 'Batch deleted.');
    }
}
