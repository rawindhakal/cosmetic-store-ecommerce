@php use Modules\Pos\Services\Code128; $cur = setting('default_currency') ?: 'NPR'; @endphp
<!doctype html><html><head><meta charset="utf-8"><title>Barcode labels</title>
<style>@page{size:{{ $w }}mm {{ $h }}mm;margin:0}*{box-sizing:border-box}body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#eee}
.l{width:{{ $w }}mm;height:{{ $h }}mm;padding:1.5mm 2mm;background:#fff;overflow:hidden;page-break-after:always;display:flex;flex-direction:column;justify-content:space-between;margin:6px auto}
.n{font-size:8.5pt;font-weight:700;line-height:1.1;max-height:2.3em;overflow:hidden}.v{font-size:7pt}.p{font-size:10pt;font-weight:800}.b{height:{{ max(8, $h - 17) }}mm}.b svg{width:100%;height:100%}.c{font-size:6.5pt;text-align:center;letter-spacing:1px}
.np{text-align:center;padding:10px}@media print{body{background:#fff}.l{margin:0}.np{display:none}}</style></head><body>
<div class="np"><button onclick="print()">Print {{ count($items) * $copies }} labels</button> Label size {{ $w }}x{{ $h }}mm (use &amp;w=50&amp;h=30 in URL to change)</div>
@foreach ($items as $it) @for ($i = 0; $i < $copies; $i++)
<div class="l"><div class="n">{{ $it['name'] }}</div>@if ($it['variant'])<div class="v">{{ $it['variant'] }}</div>@endif<div class="p">{{ $cur }} {{ number_format($it['price'], 0) }}</div>
<div class="b">{!! Code128::svg((string) $it['code'], 40) !!}</div><div class="c">{{ $it['code'] }}</div></div>
@endfor @endforeach
@if (request('print'))<script>addEventListener('load',()=>setTimeout(print,250))</script>@endif</body></html>
