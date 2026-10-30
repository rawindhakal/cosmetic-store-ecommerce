@php
    $manualMethods = ['qr_payment' => new \Modules\Payment\Gateways\QrPayment, 'bank_transfer' => new \Modules\Payment\Gateways\BankTransfer];
    $gw = $manualMethods[$order->getRawOriginal('payment_method')] ?? null;
    $proofs = $gw ? \Illuminate\Support\Facades\DB::table('order_payment_proofs')->where('order_id', $order->id)->latest('id')->get() : collect();
@endphp
@if ($gw)
    <div class="container" style="max-width:720px;margin:20px auto">
        <div style="border:1px solid #f3c6dd;background:#fff7fb;border-radius:12px;padding:18px 20px;text-align:left">
            <h4 style="margin:0 0 6px;color:#c2127a">{{ $gw->label }} &mdash; payment pending</h4>
            <p style="margin:0 0 8px">Please pay <b>{{ \Modules\Support\Money::inDefaultCurrency($order->total->amount())->convertToCurrentCurrency()->format() }}</b> using the details below. Your order will be confirmed once we verify the payment.</p>
            <div>{!! $gw->instructions !!}</div>

            @if (session('proof_saved'))
                <div style="background:#e8f7ef;color:#16794a;padding:10px 12px;border-radius:8px;margin-top:12px">{{ session('proof_saved') }}</div>
            @endif
            @if ($errors->any())
                <div style="background:#fde8e8;color:#b42318;padding:10px 12px;border-radius:8px;margin-top:12px">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
            @endif

            @if ($order->status === \Modules\Order\Entities\Order::PENDING_PAYMENT)
                <form method="POST" action="{{ route('payment.proof.store', $order->id) }}" enctype="multipart/form-data" style="margin-top:14px">
                    {{ csrf_field() }}
                    <label style="font-weight:700">Transaction / reference no. *</label>
                    <input name="reference" required class="form-control" style="width:100%;margin:4px 0 10px;padding:10px;border:1px solid #ddd;border-radius:8px" placeholder="e.g. eSewa ID, bank voucher no.">
                    <label style="font-weight:700">Payment screenshot (optional)</label>
                    <input type="file" name="screenshot" accept="image/*" style="display:block;margin:4px 0 10px">
                    <label style="font-weight:700">Note (optional)</label>
                    <input name="note" class="form-control" style="width:100%;margin:4px 0 12px;padding:10px;border:1px solid #ddd;border-radius:8px">
                    <button class="btn btn-primary" type="submit">Submit payment details</button>
                </form>
            @endif

            @foreach ($proofs as $p)
                <div style="margin-top:10px;font-size:13px;color:#555">Submitted: <b>{{ $p->reference }}</b> &middot; {{ \Carbon\Carbon::parse($p->created_at)->format('d M Y h:i A') }}</div>
            @endforeach
        </div>
    </div>
@endif
