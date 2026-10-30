@php
    use Modules\Pos\Services\Pos;
    use Modules\Pos\Services\Code128;
    $w = (int) Pos::setting('paper_width');
    $printable = $w - 8;
    $fs = (int) Pos::setting('font_size');
    $money = fn ($v) => number_format((float) $v, 2);
    $cur = setting('default_currency') ?: 'NPR';
    $isReturn = (bool) $return;
    $title = $isReturn ? 'CREDIT NOTE / RETURN' : (Pos::setting('receipt_title') ?: 'INVOICE');
    $logo = null;
    if (Pos::setting('show_logo') && setting('storefront_header_logo')) {
        $f = \Modules\Media\Entities\File::find(setting('storefront_header_logo'));
        $logo = $f ? $f->path : null;
    }
    $mrpTotal = 0; $items = 0;
    foreach ($lines as $l) { $mrpTotal += max($l['mrp'], $l['unit']) * $l['qty']; $items += $l['qty']; }
    $savings = $mrpTotal - collect($lines)->sum('line_total') + ($order->discount - collect($lines)->sum('line_discount'));
    $method = ['cash' => 'Cash', 'card' => 'Card', 'qr' => 'QR Payment', 'bank' => 'Bank Transfer', 'other' => 'Other'];
    $pendingPay = ($order->status ?? '') === 'pending_payment';
@endphp
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $sale->invoice_no }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    @page { size: {{ $w }}mm auto; margin: 0; }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; background: #e9e9e9; }
    body { font-family: Arial, "Helvetica Neue", Helvetica, sans-serif; color: #000; font-size: {{ $fs }}px; line-height: 1.35; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .paper { width: {{ $w }}mm; background: #fff; margin: 12px auto; padding: 4mm; }
    .c { text-align: center; } .r { text-align: right; } .b { font-weight: 700; }
    .logo { max-width: 55%; max-height: 16mm; margin: 0 auto 2px; display: block; }
    .store { font-size: {{ $fs + 5 }}px; font-weight: 800; letter-spacing: .3px; text-transform: uppercase; }
    .muted { font-size: {{ $fs - 2 }}px; }
    .title { font-weight: 800; letter-spacing: 1px; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 3px 0; margin: 6px 0; text-align: center; font-size: {{ $fs + 1 }}px; }
    .meta { width: 100%; font-size: {{ $fs - 1 }}px; border-collapse: collapse; }
    .meta td { padding: 1px 0; vertical-align: top; } .meta td:last-child { text-align: right; }
    hr { border: 0; border-top: 1px dashed #000; margin: 5px 0; }
    .items { width: 100%; border-collapse: collapse; }
    .items th { text-align: left; font-size: {{ $fs - 2 }}px; text-transform: uppercase; border-bottom: 1px solid #000; padding: 2px 0; }
    .items th:last-child, .items td:last-child { text-align: right; }
    .items td { padding: 2px 0; vertical-align: top; }
    .items .name { font-weight: 700; }
    .items .sub { font-size: {{ $fs - 2 }}px; color: #000; }
    .tot { width: 100%; border-collapse: collapse; }
    .tot td { padding: 1.5px 0; } .tot td:last-child { text-align: right; white-space: nowrap; }
    .grand td { font-size: {{ $fs + 4 }}px; font-weight: 800; border-top: 1px solid #000; border-bottom: 1px solid #000; padding: 4px 0; }
    .bar { margin: 6px auto 0; width: 80%; } .bar svg { display: block; }
    .box { border: 1px solid #000; padding: 3px 5px; margin-top: 5px; text-align: center; font-weight: 700; }
    .noprint { text-align: center; margin: 10px; }
    .noprint button, .noprint a { font: 600 14px Arial; padding: 10px 18px; border: 0; border-radius: 8px; background: #e91e8c; color: #fff; text-decoration: none; cursor: pointer; margin: 0 4px; }
    @media print { html, body { background: #fff; } .paper { margin: 0; width: {{ $w }}mm; } .noprint { display: none !important; } }
</style>
</head>
<body>
<div class="noprint">
    <button onclick="window.print()">Print</button>
    <a href="{{ route('admin.pos.sales') }}">Sales</a>
    <a href="{{ route('admin.pos.index') }}" style="background:#333">Back to POS</a>
</div>

<div class="paper">
    @if ($logo) <img class="logo" src="{{ $logo }}" alt=""> @endif
    <div class="c store">{{ Pos::storeName() }}</div>
    <div class="c muted">
        @if (Pos::setting('address')) {{ Pos::setting('address') }}<br> @endif
        @if (Pos::setting('phone')) Tel: {{ Pos::setting('phone') }}<br> @endif
        @if (Pos::setting('tax_id')) {{ Pos::setting('tax_label') === 'VAT' ? 'PAN/VAT' : Pos::setting('tax_label') }} No: {{ Pos::setting('tax_id') }} @endif
    </div>

    <div class="title">{{ $title }}@if ($reprint) <span class="muted">(DUPLICATE)</span> @endif</div>

    <table class="meta">
        <tr><td>{{ $isReturn ? 'Return No' : 'Bill No' }}</td><td class="b">{{ $isReturn ? $return->return_no : $sale->invoice_no }}</td></tr>
        @if ($isReturn) <tr><td>Against Bill</td><td>{{ $sale->invoice_no }}</td></tr> @endif
        <tr><td>Date</td><td>{{ \Carbon\Carbon::parse($isReturn ? $return->created_at : $sale->created_at)->format('d M Y, h:i A') }}</td></tr>
        <tr><td>Cashier</td><td>{{ $cashier }}</td></tr>
        <tr><td>Customer</td><td>{{ $customer ? trim($customer->first_name.' '.$customer->last_name) : 'Walk-in' }}</td></tr>
        @if ($customer && $customer->phone) <tr><td>Phone</td><td>{{ $customer->phone }}</td></tr> @endif
    </table>

    <hr>

    @if ($isReturn)
        @php $retItems = json_decode($return->items, true); @endphp
        <table class="items">
            <thead><tr><th>Item</th><th>Amount</th></tr></thead>
            <tbody>
            @foreach ($retItems as $it)
                <tr><td><div class="name">{{ $it['name'] }}</div><div class="sub">@if ($it['variant']) {{ $it['variant'] }} &middot; @endif{{ $it['qty'] }} x {{ $money($it['unit_net']) }}</div></td><td class="r">{{ $money($it['amount']) }}</td></tr>
            @endforeach
            </tbody>
        </table>
        <hr>
        <table class="tot grand"><tr><td>REFUND</td><td>{{ $cur }} {{ $money($return->amount) }}</td></tr></table>
        <table class="tot">
            <tr><td>Refund method</td><td>{{ ucfirst($return->refund_method) }}</td></tr>
            @if ($return->reason) <tr><td>Reason</td><td>{{ $return->reason }}</td></tr> @endif
        </table>
    @else
        <table class="items">
            <thead><tr><th style="width:56%">Item</th><th style="width:14%">Qty</th><th style="width:30%">Amount</th></tr></thead>
            <tbody>
            @foreach ($lines as $l)
                <tr>
                    <td colspan="3" class="name" style="padding-bottom:0">{{ $loop->iteration }}. {{ $l['name'] }}</td>
                </tr>
                <tr>
                    <td class="sub" style="padding-top:0">@if ($l['variant']) {{ $l['variant'] }}<br> @endif {{ $money($l['unit']) }} @if ($l['mrp'] > $l['unit']) <s>{{ $money($l['mrp']) }}</s> @endif</td>
                    <td class="sub" style="padding-top:0">x {{ $l['qty'] }}</td>
                    <td class="r" style="padding-top:0">{{ $money($l['unit'] * $l['qty']) }}@if ($l['line_discount'] > 0)<div class="sub">- {{ $money($l['line_discount']) }}</div>@endif</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <hr>
        <table class="tot">
            <tr><td>Items / Qty</td><td>{{ count($lines) }} / {{ $items }}</td></tr>
            <tr><td>Sub total</td><td>{{ $money($order->sub_total) }}</td></tr>
            @if (($order->discount - ($order->glow_points_discount ?? 0)) > 0.001)
                <tr><td>Discount @if ($sale->discount_label) ({{ $sale->discount_label }}) @endif</td><td>- {{ $money($order->discount - ($order->glow_points_discount ?? 0)) }}</td></tr>
            @endif
            @if (($order->glow_points_discount ?? 0) > 0)
                <tr><td>{{ $points['name'] }} ({{ $points['redeemed'] }} pts)</td><td>- {{ $money($order->glow_points_discount) }}</td></tr>
            @endif
            @if ($sale->tax_amount > 0)
                <tr><td>{{ Pos::setting('tax_label') }} {{ rtrim(rtrim(number_format((float) Pos::setting('tax_rate'), 2), '0'), '.') }}% {{ Pos::setting('tax_mode') === 'inclusive' ? '(incl.)' : '' }}</td><td>{{ $money($sale->tax_amount) }}</td></tr>
            @endif
            @if ($sale->rounding != 0)
                <tr><td>Rounding</td><td>{{ $sale->rounding > 0 ? '+' : '' }}{{ $money($sale->rounding) }}</td></tr>
            @endif
        </table>
        <table class="tot grand"><tr><td>TOTAL</td><td>{{ $cur }} {{ $money($order->total) }}</td></tr></table>

        <table class="tot" style="margin-top:4px">
            @foreach ($payments as $p)
                <tr><td>{{ $method[$p['method']] ?? ucfirst($p['method']) }}@if (!empty($p['ref'])) <span class="muted">({{ $p['ref'] }})</span>@endif</td><td>{{ $money($p['amount']) }}</td></tr>
            @endforeach
            @if ($sale->change_due > 0)
                <tr><td class="b">Change</td><td class="b">{{ $money($sale->change_due) }}</td></tr>
            @endif
        </table>

        @if ($pendingPay)
            <div class="box" style="margin-top:6px;border-width:2px">PAYMENT PENDING VERIFICATION<div class="muted" style="font-weight:400">Order is confirmed after the payment is verified.</div></div>
        @endif

        @if (Pos::setting('show_savings') && $savings > 0.5)
            <div class="box">YOU SAVED {{ $cur }} {{ $money($savings) }}</div>
        @endif

        @if ($points['enabled'] && $customer)
            <div class="c muted" style="margin-top:6px">
                {{ $points['name'] }}: @if ($points['earned'] > 0) +{{ $points['earned'] }} earned @endif @if ($points['redeemed'] > 0) &middot; {{ $points['redeemed'] }} used @endif<br>
                Balance: <b>{{ number_format($points['balance']) }}</b>
            </div>
        @endif
    @endif

    <hr>
    @if (Pos::setting('return_policy')) <div class="c muted">{{ Pos::setting('return_policy') }}</div> @endif
    @if (Pos::setting('receipt_footer')) <div class="c b" style="margin-top:4px">{{ Pos::setting('receipt_footer') }}</div> @endif

    @if (Pos::setting('show_barcode'))
        <div class="bar">{!! Code128::svg($sale->invoice_no, 34) !!}<div class="c muted">{{ $sale->invoice_no }}</div></div>
    @endif
    <div class="c muted" style="margin-top:6px">Powered by EmpressDreams POS</div>
</div>

@if (request('print') && Pos::setting('auto_print'))
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 250); });</script>
@endif
</body>
</html>
