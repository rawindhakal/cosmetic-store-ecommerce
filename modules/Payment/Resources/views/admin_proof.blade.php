@php
    $manual = in_array($order->getRawOriginal('payment_method'), ['qr_payment', 'bank_transfer'], true);
    $proofs = $manual ? \Illuminate\Support\Facades\DB::table('order_payment_proofs')->where('order_id', $order->id)->latest('id')->get() : collect();
@endphp
@if ($manual)
    <div style="margin:15px;padding:15px 18px;border:2px solid {{ $order->status === 'pending_payment' ? '#e08a00' : '#1f9d63' }};border-radius:10px;background:#fffaf0">
        <h4 style="margin:0 0 6px">{{ $order->getRawOriginal('payment_method') === 'qr_payment' ? 'QR payment' : 'Bank transfer' }} &mdash; manual verification</h4>
        <p style="margin:0 0 8px">
            @if ($order->status === 'pending_payment')
                <b style="color:#b26a00">Awaiting verification.</b> Check your bank/wallet for <b>{{ $order->total->format() }}</b>, then set the order status below to <b>Processing</b> (or Completed). Cancel it if the payment never arrives.
            @else
                Current status: <b>{{ ucwords(str_replace('_', ' ', $order->status)) }}</b>.
            @endif
        </p>
        @forelse ($proofs as $p)
            <div style="padding:8px 0;border-top:1px dashed #ddd">
                <b>Reference:</b> {{ $p->reference }} &middot; {{ \Carbon\Carbon::parse($p->created_at)->format('d M Y h:i A') }}
                @if ($p->note) <br><b>Note:</b> {{ $p->note }} @endif
                @if ($p->file_path) <br><a href="{{ asset('storage/'.$p->file_path) }}" target="_blank"><img src="{{ asset('storage/'.$p->file_path) }}" style="max-height:160px;border:1px solid #ddd;border-radius:6px;margin-top:6px"></a> @endif
            </div>
        @empty
            <div style="color:#888">The customer has not submitted payment details yet.</div>
        @endforelse
    </div>
@endif
