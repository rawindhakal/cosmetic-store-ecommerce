<?php

namespace Modules\GlowPoints\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\GlowPoints\Services\GlowPoints;
use Modules\Order\Entities\Order;

class GlowPointSettingController extends Controller
{
    private array $numeric = ['earn_points', 'earn_per_amount', 'redeem_value', 'min_redeem', 'max_redeem_percent', 'signup_bonus', 'first_order_bonus', 'expiry_days'];

    public function edit()
    {
        $values = [];

        foreach (array_merge($this->numeric, ['enabled', 'name', 'earn_base', 'earn_statuses']) as $key) {
            $values[$key] = GlowPoints::setting($key);
        }

        return view('glowpoints::admin.settings', [
            'values' => $values,
            'statuses' => trans('order::statuses'),
        ]);
    }

    public function update()
    {
        $data = request()->validate([
            'name' => 'required|string|max:40',
            'earn_points' => 'required|numeric|min:0',
            'earn_per_amount' => 'required|numeric|min:0.01',
            'redeem_value' => 'required|numeric|min:0.0001',
            'min_redeem' => 'required|integer|min:0',
            'max_redeem_percent' => 'required|numeric|min:0|max:100',
            'signup_bonus' => 'required|integer|min:0',
            'first_order_bonus' => 'required|integer|min:0',
            'expiry_days' => 'required|integer|min:0',
            'earn_base' => 'required|in:subtotal,total',
            'earn_statuses' => 'required|array|min:1',
            'earn_statuses.*' => 'string',
        ]);

        $save = [];
        foreach ($data as $key => $value) {
            $save["glowpoints_{$key}"] = $value;
        }
        $save['glowpoints_enabled'] = request()->boolean('enabled') ? 1 : 0;

        setting($save);

        return back()->with('success', 'GlowPoints settings saved.');
    }
}
