<?php

namespace Modules\GlowPoints\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\GlowPoints\Entities\GlowPointTransaction;
use Modules\GlowPoints\Services\GlowPoints;
use Modules\User\Entities\User;

class GlowPointController extends Controller
{
    public function index()
    {
        $transactions = GlowPointTransaction::with('customer')->latest('id');

        if ($email = request('email')) {
            $transactions->whereHas('customer', fn ($q) => $q->where('email', 'like', "%{$email}%"));
        }

        return view('glowpoints::admin.index', [
            'transactions' => $transactions->paginate(25)->withQueryString(),
            'name' => GlowPoints::name(),
            'totalOutstanding' => (int) GlowPointTransaction::sum('points'),
            'totalIssued' => (int) GlowPointTransaction::where('points', '>', 0)->sum('points'),
            'totalRedeemed' => abs((int) GlowPointTransaction::where('type', 'redeem')->sum('points')),
        ]);
    }

    public function adjust()
    {
        $data = request()->validate([
            'email' => 'required|email|exists:users,email',
            'points' => 'required|integer|not_in:0',
            'note' => 'nullable|string|max:190',
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();

        if ($data['points'] < 0 && GlowPoints::balance($user->id) + $data['points'] < 0) {
            return back()->withErrors(['points' => 'That would make the balance negative.'])->withInput();
        }

        GlowPoints::adjust($user->id, (int) $data['points'], $data['note'] ?? null);

        return back()->with('success', 'Balance updated.');
    }
}
