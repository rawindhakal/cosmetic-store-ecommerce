<?php

namespace Modules\Pos\Http\Controllers\Admin;

use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\GlowPoints\Services\GlowPoints;
use Modules\Order\Entities\Order;
use Modules\Pos\Services\Code128;
use Modules\Pos\Services\Pos;

class PosAdminController extends Controller
{
    /* ---------------------------------------------------------------- receipts */

    public function receipt($orderId)
    {
        return view('pos::admin.receipt', Pos::receipt((int) $orderId, request('return') ? (int) request('return') : null) + ['reprint' => (bool) request('reprint')]);
    }

    /** Admin confirms a QR / bank-transfer payment arrived: completes the order (which also awards GlowPoints). */
    public function verify($orderId)
    {
        $order = Order::findOrFail($orderId);

        if ($order->status === Order::PENDING_PAYMENT) {
            $order->update(['status' => Order::COMPLETED]);
            event(new \Modules\Order\Events\OrderStatusChanged($order->fresh()));
        }

        return back()->with('success', "Payment verified for order #{$orderId}.");
    }

    public function sales()
    {
        $q = trim((string) request('q'));

        $sales = DB::table('pos_sales')->join('orders', 'orders.id', '=', 'pos_sales.order_id')
            ->leftJoin('users as c', 'c.id', '=', 'pos_sales.cashier_id')
            ->when($q, fn ($x) => $x->where(fn ($w) => $w->where('invoice_no', 'like', "%$q%")->orWhere('orders.customer_first_name', 'like', "%$q%")->orWhere('orders.customer_phone', 'like', "%$q%")))
            ->when(request('date'), fn ($x, $d) => $x->whereDate('pos_sales.created_at', $d))
            ->orderByDesc('pos_sales.id')
            ->select('pos_sales.*', 'orders.total', 'orders.status', 'orders.customer_first_name', 'orders.customer_last_name', 'c.first_name as cashier_name')
            ->paginate(25)->withQueryString();

        $today = DB::table('pos_sales')->join('orders', 'orders.id', '=', 'pos_sales.order_id')->whereDate('pos_sales.created_at', today())->selectRaw('count(*) c, coalesce(sum(orders.total),0) t')->first();

        return view('pos::admin.sales', ['sales' => $sales, 'today' => $today]);
    }

    public function returnForm($orderId)
    {
        $data = Pos::receipt((int) $orderId);
        $returned = [];
        foreach (DB::table('pos_returns')->where('order_id', $orderId)->get() as $r) {
            foreach (json_decode($r->items, true) as $it) {
                $returned[$it['order_product_id']] = ($returned[$it['order_product_id']] ?? 0) + $it['qty'];
            }
        }

        return view('pos::admin.return', $data + ['returned' => $returned, 'returns' => DB::table('pos_returns')->where('order_id', $orderId)->latest('id')->get()]);
    }

    public function processReturn($orderId)
    {
        $data = Pos::receipt((int) $orderId);
        $request = request()->validate(['qty' => 'required|array', 'refund_method' => 'required|in:cash,card,qr,points,other', 'reason' => 'nullable|string|max:120']);
        $order = Order::findOrFail($orderId);

        $already = [];
        foreach (DB::table('pos_returns')->where('order_id', $orderId)->get() as $r) {
            foreach (json_decode($r->items, true) as $it) {
                $already[$it['order_product_id']] = ($already[$it['order_product_id']] ?? 0) + $it['qty'];
            }
        }

        $sumLines = array_sum(array_column($data['lines'], 'line_total'));
        $orderTotal = (float) $order->total->amount();
        $items = [];
        $amount = 0.0;

        foreach ($data['lines'] as $l) {
            $qty = (int) ($request['qty'][$l['order_product_id']] ?? 0);
            $max = $l['qty'] - ($already[$l['order_product_id']] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            if ($qty > $max) {
                return back()->withErrors(["Cannot return more than {$max} of {$l['name']}."]);
            }

            // net value of one unit after line + bill discounts (and tax when added on top)
            $net = ($l['line_total'] / $l['qty']) * ($orderTotal / max(0.01, $sumLines));
            $items[] = ['order_product_id' => $l['order_product_id'], 'product_id' => $l['product_id'], 'variant_id' => $l['variant_id'], 'name' => $l['name'], 'variant' => $l['variant'], 'qty' => $qty, 'unit_net' => round($net, 2), 'amount' => round($net * $qty, 2)];
            $amount += $net * $qty;
        }

        if (!$items) {
            return back()->withErrors(['Select at least one item to return.']);
        }

        DB::transaction(function () use ($items, $amount, $orderId, $request, $order) {
            foreach ($items as $it) {
                if ($it['variant_id']) {
                    DB::table('product_variants')->where('id', $it['variant_id'])->where('manage_stock', 1)->update(['qty' => DB::raw('qty + ' . $it['qty']), 'in_stock' => 1]);
                } else {
                    DB::table('products')->where('id', $it['product_id'])->where('manage_stock', 1)->update(['qty' => DB::raw('qty + ' . $it['qty']), 'in_stock' => 1]);
                }
            }

            $id = DB::table('pos_returns')->insertGetId([
                'order_id' => $orderId, 'user_id' => auth()->id(), 'return_no' => 'RET-' . str_pad((string) (DB::table('pos_returns')->count() + 1), 6, '0', STR_PAD_LEFT),
                'items' => json_encode($items), 'amount' => round($amount, 2), 'refund_method' => $request['refund_method'], 'reason' => $request['reason'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            session(['pos_last_return' => $id]);

            // fully returned? mark order refunded and reverse points
            $returnedTotal = DB::table('pos_returns')->where('order_id', $orderId)->sum('amount');
            if ($returnedTotal + 0.5 >= (float) $order->total->amount()) {
                $order->update(['status' => Order::REFUNDED]);
                GlowPoints::reverseForOrder($order->fresh());
            }
        });

        return redirect(route('admin.pos.receipt', ['order' => $orderId, 'return' => session('pos_last_return')]));
    }

    /* --------------------------------------------------------------- registers */

    public function registers()
    {
        $rows = DB::table('pos_registers')->leftJoin('users', 'users.id', '=', 'pos_registers.user_id')->orderByDesc('pos_registers.id')
            ->select('pos_registers.*', 'users.first_name')->paginate(20);

        return view('pos::admin.registers', ['rows' => $rows]);
    }

    public function registerReport($id)
    {
        $r = DB::table('pos_registers')->where('id', $id)->firstOrFail();

        return view('pos::admin.register_report', [
            'r' => $r, 'summary' => Pos::registerSummary($r), 'user' => DB::table('users')->find($r->user_id),
            'movements' => DB::table('pos_cash_movements')->where('register_id', $id)->get(),
        ]);
    }

    /* ---------------------------------------------------------------- barcodes */

    public function barcodes()
    {
        $q = trim((string) request('q'));

        $products = DB::table('products')->join('product_translations as t', fn ($j) => $j->on('t.product_id', '=', 'products.id')->where('t.locale', 'en'))
            ->whereNull('products.deleted_at')
            ->when($q, fn ($x) => $x->where(fn ($w) => $w->where('t.name', 'like', "%$q%")->orWhere('products.sku', 'like', "%$q%")->orWhere('products.barcode', 'like', "%$q%")
                ->orWhereExists(fn ($s) => $s->select(DB::raw(1))->from('product_variants')->whereColumn('product_variants.product_id', 'products.id')->where('barcode', 'like', "%$q%"))))
            ->when(request('missing'), fn ($x) => $x->whereNull('products.barcode'))
            ->orderBy('t.name')->select('products.id', 'products.sku', 'products.barcode', 'products.qty', 'products.selling_price', 't.name')->paginate(30)->withQueryString();

        $variants = DB::table('product_variants')->whereIn('product_id', $products->pluck('id'))->whereNull('deleted_at')->orderBy('position')->get()->groupBy('product_id');

        return view('pos::admin.barcodes', compact('products', 'variants'));
    }

    public function saveBarcode()
    {
        $d = request()->validate(['type' => 'required|in:product,variant', 'id' => 'required|integer', 'barcode' => 'nullable|string|max:64']);
        $code = trim((string) $d['barcode']) ?: null;

        if ($code) {
            $dupe = DB::table('products')->where('barcode', $code)->when($d['type'] === 'product', fn ($q) => $q->where('id', '!=', $d['id']))->exists()
                || DB::table('product_variants')->where('barcode', $code)->when($d['type'] === 'variant', fn ($q) => $q->where('id', '!=', $d['id']))->exists();
            if ($dupe) {
                return response()->json(['message' => 'Barcode already in use.'], 422);
            }
        }

        DB::table($d['type'] === 'product' ? 'products' : 'product_variants')->where('id', $d['id'])->update(['barcode' => $code]);

        return response()->json(['ok' => true]);
    }

    public function generateBarcodes()
    {
        $n = 0;
        $seed = (int) max(DB::table('products')->max('id'), DB::table('product_variants')->max('id')) * 3 + 500000;
        foreach (DB::table('products')->whereNull('barcode')->get(['id']) as $p) {
            DB::table('products')->where('id', $p->id)->update(['barcode' => Code128::generateEan13(++$seed)]);
            $n++;
        }
        foreach (DB::table('product_variants')->whereNull('barcode')->get(['id']) as $v) {
            DB::table('product_variants')->where('id', $v->id)->update(['barcode' => Code128::generateEan13(++$seed)]);
            $n++;
        }

        return back()->with('success', "Generated {$n} barcodes.");
    }

    public function labels()
    {
        $items = [];
        foreach (array_filter(explode(',', (string) request('p'))) as $id) {
            $p = DB::table('products')->join('product_translations as t', fn ($j) => $j->on('t.product_id', '=', 'products.id')->where('t.locale', 'en'))->where('products.id', $id)->first(['products.*', 't.name']);
            if (!$p) {
                continue;
            }
            $vs = DB::table('product_variants')->where('product_id', $id)->whereNull('deleted_at')->get();
            if ($vs->isNotEmpty()) {
                foreach ($vs as $v) {
                    $items[] = ['name' => $p->name, 'variant' => $v->name, 'price' => $v->selling_price ?? $v->price, 'code' => $v->barcode ?: $v->sku];
                }
            } else {
                $items[] = ['name' => $p->name, 'variant' => null, 'price' => $p->selling_price, 'code' => $p->barcode ?: $p->sku];
            }
        }
        $copies = max(1, (int) request('copies', 1));

        return view('pos::admin.labels', ['items' => $items, 'copies' => $copies, 'w' => (int) request('w', 50), 'h' => (int) request('h', 30)]);
    }

    /* ---------------------------------------------------------------- settings */

    public function settings()
    {
        $keys = ['store_name', 'address', 'phone', 'tax_id', 'tax_mode', 'tax_rate', 'tax_label', 'receipt_title', 'receipt_footer', 'return_policy', 'show_logo', 'show_barcode', 'show_savings', 'paper_width', 'font_size', 'auto_print', 'round_total', 'low_stock', 'invoice_prefix', 'require_register', 'allow_price_override', 'allow_oversell'];
        $v = [];
        foreach ($keys as $k) {
            $v[$k] = Pos::setting($k, false);
        }

        return view('pos::admin.settings', ['v' => $v]);
    }

    public function updateSettings()
    {
        $d = request()->validate([
            'store_name' => 'nullable|string|max:80', 'address' => 'nullable|string|max:200', 'phone' => 'nullable|string|max:40', 'tax_id' => 'nullable|string|max:40',
            'tax_mode' => 'required|in:inclusive,exclusive,none', 'tax_rate' => 'required|numeric|min:0|max:100', 'tax_label' => 'required|string|max:20',
            'receipt_title' => 'nullable|string|max:40', 'receipt_footer' => 'nullable|string|max:200', 'return_policy' => 'nullable|string|max:300',
            'paper_width' => 'required|integer|in:58,80', 'font_size' => 'required|integer|min:9|max:16', 'low_stock' => 'required|integer|min:0', 'invoice_prefix' => 'nullable|string|max:10',
        ]);

        $save = [];
        foreach ($d as $k => $v) {
            $save["pos_{$k}"] = $v ?? '';
        }
        foreach (['show_logo', 'show_barcode', 'show_savings', 'auto_print', 'round_total', 'require_register', 'allow_price_override', 'allow_oversell'] as $k) {
            $save["pos_{$k}"] = request()->boolean($k) ? 1 : 0;
        }
        setting($save);

        return back()->with('success', 'POS settings saved.');
    }
}
