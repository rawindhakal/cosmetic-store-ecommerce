<?php

namespace Modules\Pos\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\GlowPoints\Services\GlowPoints;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;

class Pos
{
    public static function setting(string $key, $default = null)
    {
        $value = setting("pos_{$key}");

        if ($value === null || $value === '') {
            $value = config("fleetcart.modules.pos.config.{$key}", $default);
        }

        return $value;
    }

    public static function storeName(): string
    {
        return (string) (self::setting('store_name') ?: setting('store_name'));
    }

    public static function money($amount): string
    {
        $currency = setting('default_currency') ?: 'NPR';

        return $currency . ' ' . number_format((float) $amount, 2);
    }

    /* ----------------------------------------------------------------- catalog */

    public static function catalog(): array
    {
        $categories = DB::table('categories')
            ->join('category_translations as t', fn ($j) => $j->on('t.category_id', '=', 'categories.id')->where('t.locale', 'en'))
            ->where('categories.is_active', 1)->orderBy('categories.position')
            ->get(['categories.id', 'categories.parent_id', 't.name'])->all();

        $images = DB::table('entity_files')
            ->join('files', 'files.id', '=', 'entity_files.file_id')
            ->where('entity_type', Product::class)->where('zone', 'base_image')
            ->pluck('files.path', 'entity_id');

        $catMap = DB::table('product_categories')->get()->groupBy('product_id')->map(fn ($r) => $r->pluck('category_id')->all());
        $variants = DB::table('product_variants')->whereNull('deleted_at')->where('is_active', 1)->orderBy('position')->get()->groupBy('product_id');

        $products = DB::table('products')
            ->join('product_translations as t', fn ($j) => $j->on('t.product_id', '=', 'products.id')->where('t.locale', 'en'))
            ->whereNull('products.deleted_at')->where('products.is_active', 1)->where('products.is_virtual', 0)
            ->orderBy('t.name')
            ->get(['products.*', 't.name'])
            ->map(function ($p) use ($images, $catMap, $variants) {
                $vs = ($variants[$p->id] ?? collect())->map(fn ($v) => [
                    'id' => $v->id, 'name' => $v->name, 'sku' => $v->sku, 'barcode' => $v->barcode,
                    'price' => (float) ($v->selling_price ?? $v->price), 'mrp' => (float) $v->price,
                    'stock' => $v->manage_stock ? (int) $v->qty : null,
                ])->values()->all();

                return [
                    'id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'barcode' => $p->barcode,
                    'price' => (float) $p->selling_price, 'mrp' => (float) $p->price,
                    'stock' => $p->manage_stock ? (int) $p->qty : null,
                    'image' => isset($images[$p->id]) ? asset('storage/' . $images[$p->id]) : null,
                    'cats' => $catMap[$p->id] ?? [],
                    'variants' => $vs,
                ];
            })->all();

        return compact('categories', 'products');
    }

    public static function findByCode(string $code): ?array
    {
        $variant = ProductVariant::where('barcode', $code)->orWhere('sku', $code)->first();
        if ($variant) {
            return ['product_id' => $variant->product_id, 'variant_id' => $variant->id];
        }

        $product = Product::where('barcode', $code)->orWhere('sku', $code)->first();

        return $product ? ['product_id' => $product->id, 'variant_id' => null] : null;
    }

    /* ---------------------------------------------------------------- register */

    public static function currentRegister($userId)
    {
        return DB::table('pos_registers')->where('user_id', $userId)->whereNull('closed_at')->latest('id')->first();
    }

    public static function registerSummary($register): array
    {
        $sales = DB::table('pos_sales')->where('register_id', $register->id)->get();
        $by = ['cash' => 0.0, 'card' => 0.0, 'qr' => 0.0, 'bank' => 0.0, 'other' => 0.0];
        $total = 0.0;

        foreach ($sales as $s) {
            $change = (float) $s->change_due;
            foreach (json_decode($s->payments, true) as $p) {
                $key = isset($by[$p['method']]) ? $p['method'] : 'other';
                $by[$key] += (float) $p['amount'];
                $total += (float) $p['amount'];
            }
            $by['cash'] -= $change;
            $total -= $change;
        }

        $in = (float) DB::table('pos_cash_movements')->where('register_id', $register->id)->where('type', 'in')->sum('amount');
        $out = (float) DB::table('pos_cash_movements')->where('register_id', $register->id)->where('type', 'out')->sum('amount');
        $refunds = (float) DB::table('pos_returns')->where('refund_method', 'cash')->where('created_at', '>=', $register->opened_at)
            ->when($register->closed_at, fn ($q) => $q->where('created_at', '<=', $register->closed_at))->sum('amount');

        return [
            'sales_count' => $sales->count(),
            'total_sales' => round($total, 2),
            'by_method' => array_map(fn ($v) => round($v, 2), $by),
            'cash_in' => $in, 'cash_out' => $out, 'cash_refunds' => $refunds,
            'expected_cash' => round((float) $register->opening_cash + $by['cash'] + $in - $out - $refunds, 2),
        ];
    }

    /* ------------------------------------------------------------------- sale */

    /**
     * Create a completed in-store order from the terminal payload.
     *
     * @throws Exception
     */
    public static function createSale(array $data, $user): array
    {
        $items = $data['items'] ?? [];
        if (!$items) {
            throw new Exception('Cart is empty.');
        }

        $register = self::currentRegister($user->id);
        if (self::setting('require_register') && !$register) {
            throw new Exception('Open the cash register first.');
        }

        return DB::transaction(function () use ($data, $items, $user, $register) {
            $lines = [];
            $gross = 0.0;
            $lineDiscounts = 0.0;
            $allowOverride = (bool) self::setting('allow_price_override', true);

            foreach ($items as $it) {
                $product = Product::with('translations')->findOrFail($it['product_id']);
                $variant = !empty($it['variant_id']) ? ProductVariant::where('product_id', $product->id)->findOrFail($it['variant_id']) : null;
                $qty = max(1, (int) $it['qty']);

                $stockOwner = $variant ?: $product;
                if ($stockOwner->manage_stock && $stockOwner->qty < $qty && !self::setting('allow_oversell', false)) {
                    throw new Exception("Not enough stock for {$product->name}" . ($variant ? " ({$variant->name})" : '') . ". Available: {$stockOwner->qty}");
                }

                $unit = (float) ($variant ? ($variant->selling_price->amount() ?: $variant->price->amount()) : $product->selling_price->amount());
                if ($allowOverride && isset($it['unit_price']) && is_numeric($it['unit_price']) && $it['unit_price'] >= 0) {
                    $unit = (float) $it['unit_price'];
                }

                $lineGross = round($unit * $qty, 2);
                $lineDisc = min($lineGross, max(0, round((float) ($it['line_discount'] ?? 0), 2)));

                $lines[] = [
                    'product_id' => $product->id, 'variant_id' => $variant?->id, 'name' => $product->name, 'variant' => $variant?->name,
                    'sku' => $variant->sku ?? $product->sku, 'barcode' => $variant->barcode ?? $product->barcode,
                    'qty' => $qty, 'unit' => $unit, 'mrp' => (float) ($variant ? $variant->price->amount() : $product->price->amount()),
                    'line_discount' => $lineDisc, 'line_total' => $lineGross - $lineDisc,
                ];
                $gross += $lineGross;
                $lineDiscounts += $lineDisc;
            }

            $afterLines = $gross - $lineDiscounts;

            // cart-level discount
            $cartDiscount = 0.0;
            $discountLabel = null;
            $d = $data['discount'] ?? null;
            if ($d && !empty($d['value']) && $d['value'] > 0) {
                if (($d['type'] ?? 'fixed') === 'percent') {
                    $pct = min(100, (float) $d['value']);
                    $cartDiscount = round($afterLines * $pct / 100, 2);
                    $discountLabel = rtrim(rtrim(number_format($pct, 2), '0'), '.') . '% discount';
                } else {
                    $cartDiscount = min($afterLines, round((float) $d['value'], 2));
                    $discountLabel = 'Discount';
                }
            }

            // GlowPoints redemption (only for identified customers)
            $customerId = $data['customer_id'] ?? null;
            $customer = $customerId ? DB::table('users')->where('id', $customerId)->first() : null;
            $points = 0;
            $pointsDiscount = 0.0;
            if ($customer && GlowPoints::enabled() && !empty($data['redeem_points'])) {
                $balance = GlowPoints::balance($customer->id);
                $max = GlowPoints::maxPoints($afterLines - $cartDiscount, $balance);
                $points = min((int) $data['redeem_points'], $max);
                if ($points < (int) GlowPoints::setting('min_redeem')) {
                    $points = 0;
                }
                $pointsDiscount = GlowPoints::discountFor($points);
            }

            $payable = max(0, $afterLines - $cartDiscount - $pointsDiscount);

            // tax
            $mode = self::setting('tax_mode');
            $rate = (float) self::setting('tax_rate');
            $tax = 0.0;
            if ($mode === 'inclusive' && $rate > 0) {
                $tax = round($payable * $rate / (100 + $rate), 2);
            } elseif ($mode === 'exclusive' && $rate > 0) {
                $tax = round($payable * $rate / 100, 2);
                $payable += $tax;
            }

            $rounding = 0.0;
            if (self::setting('round_total')) {
                $rounded = round($payable);
                $rounding = round($rounded - $payable, 2);
                $payable = $rounded;
            }
            $total = round($payable, 2);

            // payments
            $payments = collect($data['payments'] ?? [])->filter(fn ($p) => ($p['amount'] ?? 0) > 0)->map(fn ($p) => [
                'method' => in_array($p['method'], ['cash', 'card', 'qr', 'bank', 'other']) ? $p['method'] : 'other',
                'amount' => round((float) $p['amount'], 2),
                'ref' => $p['ref'] ?? null,
            ])->values()->all();
            $tendered = round(array_sum(array_column($payments, 'amount')), 2);

            if ($total > 0 && $tendered + 0.001 < $total) {
                throw new Exception('Payment is less than the total (' . self::money($total) . ').');
            }

            $cashPaid = array_sum(array_map(fn ($p) => $p['method'] === 'cash' ? $p['amount'] : 0, $payments));
            $change = round(max(0, $tendered - $total), 2);
            if ($change > $cashPaid + 0.001) {
                throw new Exception('Only cash can be given back as change.');
            }
            if ($total == 0 && !$payments) {
                $payments = [['method' => 'other', 'amount' => 0, 'ref' => 'free']];
            }

            // build order
            $name = $customer ? trim("{$customer->first_name} {$customer->last_name}") : 'Walk-in Customer';
            [$first, $last] = array_pad(explode(' ', $name, 2), 2, '-');
            $addr = self::setting('address') ?: 'In-store';
            $methods = array_unique(array_column($payments, 'method'));
            $order = Order::create([
                'customer_id' => $customer?->id,
                'customer_email' => $customer->email ?? 'walkin@pos.local',
                'customer_phone' => $customer->phone ?? ($data['phone'] ?? null),
                'customer_first_name' => $first, 'customer_last_name' => $last ?: '-',
                'billing_first_name' => $first, 'billing_last_name' => $last ?: '-', 'billing_address_1' => $addr, 'billing_city' => 'In-store', 'billing_state' => '-', 'billing_zip' => '-', 'billing_country' => setting('default_country') ?: 'NP',
                'shipping_first_name' => $first, 'shipping_last_name' => $last ?: '-', 'shipping_address_1' => $addr, 'shipping_city' => 'In-store', 'shipping_state' => '-', 'shipping_zip' => '-', 'shipping_country' => setting('default_country') ?: 'NP',
                'sub_total' => $gross, 'shipping_method' => 'pos', 'shipping_cost' => 0,
                'discount' => $lineDiscounts + $cartDiscount + $pointsDiscount, 'total' => $total,
                'payment_method' => 'pos_' . (count($methods) > 1 ? 'split' : ($methods[0] ?? 'cash')),
                'currency' => setting('default_currency') ?: 'NPR', 'currency_rate' => 1, 'locale' => 'en',
                'status' => Order::PENDING, 'note' => $data['note'] ?? null,
            ]);

            foreach ($lines as &$l) {
                DB::table('order_products')->insert([
                    'order_id' => $order->id, 'product_id' => $l['product_id'], 'product_variant_id' => $l['variant_id'],
                    'unit_price' => $l['unit'], 'qty' => $l['qty'], 'line_total' => $l['line_total'],
                ]);
                $l['order_product_id'] = DB::getPdo()->lastInsertId();

                // stock
                if ($l['variant_id']) {
                    DB::table('product_variants')->where('id', $l['variant_id'])->where('manage_stock', 1)->update(['qty' => DB::raw('GREATEST(qty - ' . $l['qty'] . ', 0)')]);
                    DB::table('product_variants')->where('id', $l['variant_id'])->where('manage_stock', 1)->where('qty', '<=', 0)->update(['in_stock' => 0]);
                } else {
                    DB::table('products')->where('id', $l['product_id'])->where('manage_stock', 1)->update(['qty' => DB::raw('GREATEST(qty - ' . $l['qty'] . ', 0)')]);
                    DB::table('products')->where('id', $l['product_id'])->where('manage_stock', 1)->where('qty', '<=', 0)->update(['in_stock' => 0]);
                }
            }
            unset($l);

            if ($tax > 0 && ($taxRateId = DB::table('tax_rates')->value('id'))) {
                DB::table('order_taxes')->insert(['order_id' => $order->id, 'tax_rate_id' => $taxRateId, 'amount' => $tax]);
            }

            $invoice = self::setting('invoice_prefix') . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT);

            DB::table('pos_sales')->insert([
                'order_id' => $order->id, 'register_id' => $register->id ?? null, 'cashier_id' => $user->id, 'invoice_no' => $invoice,
                'payments' => json_encode($payments), 'tendered' => $tendered, 'change_due' => $change, 'tax_amount' => $tax, 'rounding' => $rounding,
                'discount_label' => $discountLabel, 'lines' => json_encode($lines),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // QR and bank transfers are checked by an admin; until then the order stays "pending payment".
            $needsVerification = (bool) array_intersect($methods, ['qr', 'bank']);
            $order->update(['status' => $needsVerification ? Order::PENDING_PAYMENT : Order::COMPLETED]);

            if ($customer) {
                GlowPoints::redeemForOrder($order, $points, $pointsDiscount);
                if (!$needsVerification) {
                    GlowPoints::earnForOrder($order->fresh());
                }
            }

            if (!empty($data['hold_id'])) {
                DB::table('pos_holds')->where('id', $data['hold_id'])->delete();
            }

            return ['order_id' => $order->id, 'invoice_no' => $invoice, 'total' => $total, 'change' => $change, 'pending_verification' => $needsVerification];
        });
    }

    /** Receipt payload used by the 80mm template. */
    public static function receipt(int $orderId, ?int $returnId = null): array
    {
        $sale = DB::table('pos_sales')->where('order_id', $orderId)->firstOrFail();
        $order = DB::table('orders')->where('id', $orderId)->first();
        $cashier = DB::table('users')->where('id', $sale->cashier_id)->first();
        $return = $returnId ? DB::table('pos_returns')->where('id', $returnId)->first() : null;
        $customer = $order->customer_id ? DB::table('users')->where('id', $order->customer_id)->first() : null;

        return [
            'sale' => $sale, 'order' => $order, 'lines' => json_decode($sale->lines, true), 'payments' => json_decode($sale->payments, true),
            'cashier' => $cashier ? trim("{$cashier->first_name} {$cashier->last_name}") : '-',
            'customer' => $customer, 'return' => $return,
            'points' => [
                'enabled' => GlowPoints::enabled(), 'name' => GlowPoints::name(),
                'earned' => (int) ($order->glow_points_earned ?? 0), 'redeemed' => (int) ($order->glow_points_redeemed ?? 0),
                'balance' => $customer ? GlowPoints::balance($customer->id) : null,
            ],
        ];
    }
}
