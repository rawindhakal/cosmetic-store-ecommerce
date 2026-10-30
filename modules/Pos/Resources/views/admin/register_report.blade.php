@php use Modules\Pos\Services\Pos; $w=(int)Pos::setting('paper_width'); $fs=(int)Pos::setting('font_size'); $m=fn($v)=>number_format((float)$v,2); @endphp
<!doctype html><html><head><meta charset="utf-8"><title>Z-Report #{{ $r->id }}</title>
<style>@page{size:{{ $w }}mm auto;margin:0}*{box-sizing:border-box}body{margin:0;background:#eee;font:{{ $fs }}px/1.4 Arial,Helvetica,sans-serif;color:#000}.paper{width:{{ $w }}mm;background:#fff;margin:10px auto;padding:4mm}
.c{text-align:center}.t{width:100%;border-collapse:collapse}.t td{padding:2px 0}.t td:last-child{text-align:right}hr{border:0;border-top:1px dashed #000;margin:6px 0}.h{font-weight:800;font-size:{{ $fs+3 }}px}
.np{text-align:center;margin:8px}@media print{body{background:#fff}.paper{margin:0}.np{display:none}}</style></head><body>
<div class="np"><button onclick="print()">Print</button> <a href="{{ route('admin.pos.registers') }}">Back</a></div>
<div class="paper"><div class="c h">{{ Pos::storeName() }}</div><div class="c">REGISTER REPORT (Z)</div><hr>
<table class="t"><tr><td>Register #</td><td>{{ $r->id }}</td></tr><tr><td>Cashier</td><td>{{ $user->first_name }} {{ $user->last_name }}</td></tr><tr><td>Opened</td><td>{{ $r->opened_at }}</td></tr><tr><td>Closed</td><td>{{ $r->closed_at ?: 'Still open' }}</td></tr></table><hr>
<table class="t"><tr><td>Bills</td><td>{{ $summary['sales_count'] }}</td></tr><tr><td>Total sales</td><td><b>{{ $m($summary['total_sales']) }}</b></td></tr>
@foreach ($summary['by_method'] as $k => $v)<tr><td>&nbsp;&nbsp;{{ ucfirst($k) }}</td><td>{{ $m($v) }}</td></tr>@endforeach</table><hr>
<table class="t"><tr><td>Opening cash</td><td>{{ $m($r->opening_cash) }}</td></tr><tr><td>Cash in</td><td>{{ $m($summary['cash_in']) }}</td></tr><tr><td>Cash out</td><td>{{ $m($summary['cash_out']) }}</td></tr><tr><td>Cash refunds</td><td>{{ $m($summary['cash_refunds']) }}</td></tr>
<tr><td><b>Expected cash</b></td><td><b>{{ $m($r->expected_cash ?? $summary['expected_cash']) }}</b></td></tr>
@if ($r->closing_cash !== null)<tr><td>Counted cash</td><td>{{ $m($r->closing_cash) }}</td></tr><tr><td><b>Variance</b></td><td><b>{{ $m($r->closing_cash - $r->expected_cash) }}</b></td></tr>@endif</table>
@foreach ($movements as $mv)<div style="font-size:{{ $fs-2 }}px">{{ strtoupper($mv->type) }} {{ $m($mv->amount) }} {{ $mv->reason }}</div>@endforeach
<hr><div class="c">Signature: ____________________</div></div>
@if (request('print'))<script>addEventListener('load',()=>setTimeout(print,250))</script>@endif</body></html>
