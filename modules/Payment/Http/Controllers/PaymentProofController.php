<?php

namespace Modules\Payment\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Order\Entities\Order;

class PaymentProofController extends Controller
{
    /** Customer tells us how/when they paid so the admin can match it to the deposit. */
    public function store($orderId)
    {
        $order = Order::findOrFail($orderId);
        $placed = session('placed_order');

        abort_unless(
            ($placed && $placed->id == $order->id) || (auth()->check() && $order->customer_id == auth()->id()),
            403
        );
        abort_unless(in_array($order->getRawOriginal('payment_method'), ['qr_payment', 'bank_transfer'], true), 404);

        $data = request()->validate([
            'reference' => 'required|string|max:120',
            'note' => 'nullable|string|max:190',
            'screenshot' => 'nullable|image|max:4096',
        ]);

        $path = null;
        if (request()->hasFile('screenshot')) {
            $path = request()->file('screenshot')->store('payment-proofs', 'public_storage');
        }

        DB::table('order_payment_proofs')->insert([
            'order_id' => $order->id, 'reference' => $data['reference'], 'note' => $data['note'] ?? null,
            'file_path' => $path, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('proof_saved', 'Thanks! We received your payment details and will confirm your order after verifying the payment.');
    }
}
