<?php

namespace Modules\GlowPoints\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\Cart\Facades\Cart;
use Modules\GlowPoints\Entities\GlowPointTransaction;
use Modules\GlowPoints\Services\GlowPoints;

class GlowPointsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Account page: balance + history. */
    public function index()
    {
        abort_unless(GlowPoints::enabled(), 404);

        return view('glowpoints::public.account', [
            'balance' => GlowPoints::balance(auth()->id()),
            'name' => GlowPoints::name(),
            'pointValue' => GlowPoints::pointValue(),
            'transactions' => GlowPointTransaction::where('customer_id', auth()->id())->latest('id')->paginate(15),
        ]);
    }

    public function summary()
    {
        return response()->json(GlowPoints::summary());
    }

    public function redeem()
    {
        abort_unless(GlowPoints::enabled(), 404);

        $points = (int) request('points');
        $summary = GlowPoints::summary();

        if ($points < $summary['min_redeem']) {
            return response()->json(['message' => "Minimum {$summary['min_redeem']} {$summary['name']} required."], 422);
        }

        if ($points > $summary['balance']) {
            return response()->json(['message' => "You only have {$summary['balance']} {$summary['name']}."], 422);
        }

        if ($points > $summary['max_points']) {
            return response()->json(['message' => "You can use up to {$summary['max_points']} {$summary['name']} on this order."], 422);
        }

        GlowPoints::setRedemption($points);

        return response()->json(['cart' => Cart::instance(), 'summary' => GlowPoints::summary()]);
    }

    public function remove()
    {
        GlowPoints::clearRedemption();

        return response()->json(['cart' => Cart::instance(), 'summary' => GlowPoints::summary()]);
    }
}
