<?php

namespace Modules\Pos\Http\Controllers\Admin;

use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\GlowPoints\Services\GlowPoints;
use Modules\Pos\Services\Pos;

class PosController extends Controller
{
    /** The touch terminal. */
    public function index()
    {
        $user = auth()->user();

        return view('pos::admin.terminal', [
            'register' => Pos::currentRegister($user->id),
            'cashier' => trim("{$user->first_name} {$user->last_name}"),
            'storeName' => Pos::storeName(),
            'config' => [
                'currency' => setting('default_currency') ?: 'NPR',
                'taxMode' => Pos::setting('tax_mode'), 'taxRate' => (float) Pos::setting('tax_rate'), 'taxLabel' => Pos::setting('tax_label'),
                'round' => (bool) Pos::setting('round_total'), 'autoPrint' => (bool) Pos::setting('auto_print'),
                'requireRegister' => (bool) Pos::setting('require_register'), 'lowStock' => (int) Pos::setting('low_stock'),
                'allowPriceOverride' => (bool) Pos::setting('allow_price_override', true),
                'points' => ['enabled' => GlowPoints::enabled(), 'name' => GlowPoints::name(), 'value' => GlowPoints::pointValue(), 'min' => (int) GlowPoints::setting('min_redeem'), 'maxPercent' => (float) GlowPoints::setting('max_redeem_percent')],
            ],
        ]);
    }

    public function catalog()
    {
        return response()->json(Pos::catalog());
    }

    public function barcode(string $code)
    {
        $hit = Pos::findByCode(trim($code));

        return $hit ? response()->json($hit) : response()->json(['message' => "No product with barcode {$code}"], 404);
    }

    public function sale()
    {
        try {
            return response()->json(Pos::createSale(request()->json()->all(), auth()->user()));
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /* customers */

    public function customers()
    {
        $q = trim((string) request('q'));

        $rows = DB::table('users')->join('user_roles', 'user_roles.user_id', '=', 'users.id')->where('user_roles.role_id', setting('customer_role'))
            ->when($q, fn ($x) => $x->where(fn ($w) => $w->where('first_name', 'like', "%$q%")->orWhere('last_name', 'like', "%$q%")->orWhere('phone', 'like', "%$q%")->orWhere('email', 'like', "%$q%")))
            ->orderBy('first_name')->limit(20)->get(['users.id', 'first_name', 'last_name', 'email', 'phone']);

        return response()->json($rows->map(fn ($u) => [
            'id' => $u->id, 'name' => trim("{$u->first_name} {$u->last_name}"), 'phone' => $u->phone, 'email' => $u->email,
            'points' => GlowPoints::enabled() ? GlowPoints::balance($u->id) : 0,
        ]));
    }

    public function storeCustomer()
    {
        $data = request()->validate(['name' => 'required|string|max:80', 'phone' => 'required|string|max:20', 'email' => 'nullable|email']);
        [$first, $last] = array_pad(explode(' ', trim($data['name']), 2), 2, '-');
        $email = $data['email'] ?? (preg_replace('/\D/', '', $data['phone']) . '@customer.pos');

        if (DB::table('users')->where('email', $email)->orWhere('phone', $data['phone'])->exists()) {
            return response()->json(['message' => 'A customer with this phone/email already exists.'], 422);
        }

        $user = app(\Modules\User\Contracts\Authentication::class)->registerAndActivate(['first_name' => $first, 'last_name' => $last, 'email' => $email, 'phone' => $data['phone'], 'password' => str()->random(16)]);
        $user->roles()->attach(\Modules\User\Entities\Role::find(setting('customer_role')));
        GlowPoints::signupBonus($user->id);

        return response()->json(['id' => $user->id, 'name' => $data['name'], 'phone' => $data['phone'], 'email' => $email, 'points' => GlowPoints::balance($user->id)]);
    }

    /* holds */

    public function holds()
    {
        return response()->json(DB::table('pos_holds')->latest('id')->get()->map(fn ($h) => ['id' => $h->id, 'label' => $h->label, 'created_at' => $h->created_at, 'payload' => json_decode($h->payload, true)]));
    }

    public function hold()
    {
        $id = DB::table('pos_holds')->insertGetId([
            'user_id' => auth()->id(), 'label' => request('label') ?: 'Hold ' . now()->format('H:i'),
            'payload' => json_encode(request('payload')), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return response()->json(['id' => $id]);
    }

    public function deleteHold($id)
    {
        DB::table('pos_holds')->where('id', $id)->delete();

        return response()->json(['ok' => true]);
    }

    /* quick add product (simple product) */

    public function quickProduct()
    {
        $d = request()->validate([
            'name' => 'required|string|max:150', 'price' => 'required|numeric|min:0', 'qty' => 'required|integer|min:0',
            'barcode' => 'nullable|string|max:64', 'category_id' => 'required|integer|exists:categories,id',
        ]);

        if (!empty($d['barcode']) && (DB::table('products')->where('barcode', $d['barcode'])->exists() || DB::table('product_variants')->where('barcode', $d['barcode'])->exists())) {
            return response()->json(['message' => 'That barcode is already used by another product.'], 422);
        }

        $slug = str()->slug($d['name']);
        while (DB::table('products')->where('slug', $slug)->exists()) {
            $slug .= '-' . rand(10, 99);
        }

        $id = DB::table('products')->insertGetId([
            'tax_class_id' => DB::table('tax_classes')->value('id'), 'slug' => $slug, 'price' => $d['price'], 'selling_price' => $d['price'],
            'sku' => 'ED-' . strtoupper(substr(md5($slug . microtime()), 0, 7)), 'barcode' => $d['barcode'] ?: \Modules\Pos\Services\Code128::generateEan13(DB::table('products')->max('id') + 100001),
            'manage_stock' => 1, 'qty' => $d['qty'], 'in_stock' => $d['qty'] > 0 ? 1 : 0, 'is_active' => 1, 'is_virtual' => 0, 'viewed' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('product_translations')->insert(['product_id' => $id, 'locale' => 'en', 'name' => $d['name'], 'description' => '<p>' . e($d['name']) . '</p>', 'short_description' => $d['name']]);
        $cat = DB::table('categories')->where('id', $d['category_id'])->first();
        foreach (array_filter([$cat->id, $cat->parent_id]) as $c) {
            DB::table('product_categories')->insert(['product_id' => $id, 'category_id' => $c]);
        }

        return response()->json(['id' => $id]);
    }

    /* register */

    public function openRegister()
    {
        if (Pos::currentRegister(auth()->id())) {
            return response()->json(['message' => 'Register already open.'], 422);
        }

        $id = DB::table('pos_registers')->insertGetId([
            'user_id' => auth()->id(), 'opening_cash' => max(0, (float) request('opening_cash')), 'opened_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return response()->json(['id' => $id]);
    }

    public function registerSummary()
    {
        $r = Pos::currentRegister(auth()->id());

        return $r ? response()->json(['register' => $r, 'summary' => Pos::registerSummary($r)]) : response()->json(['register' => null]);
    }

    public function cashMovement()
    {
        $r = Pos::currentRegister(auth()->id());
        abort_unless($r, 422, 'No open register.');
        $d = request()->validate(['type' => 'required|in:in,out', 'amount' => 'required|numeric|min:0.01', 'reason' => 'nullable|string|max:120']);
        DB::table('pos_cash_movements')->insert($d + ['register_id' => $r->id, 'user_id' => auth()->id(), 'created_at' => now(), 'updated_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function closeRegister()
    {
        $r = Pos::currentRegister(auth()->id());
        abort_unless($r, 422, 'No open register.');
        $summary = Pos::registerSummary($r);
        DB::table('pos_registers')->where('id', $r->id)->update([
            'closing_cash' => (float) request('closing_cash'), 'expected_cash' => $summary['expected_cash'], 'note' => request('note'), 'closed_at' => now(), 'updated_at' => now(),
        ]);

        return response()->json(['id' => $r->id]);
    }
}
