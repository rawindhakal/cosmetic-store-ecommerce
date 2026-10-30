@php $h = json_decode($chart->headers, true); $r = json_decode($chart->rows, true); @endphp
@if ($chart->note)<p style="font-size:13px;color:#666">{{ $chart->note }}</p>@endif
<div style="overflow-x:auto"><table class="table table-bordered" style="width:auto;min-width:320px;text-align:center">
    <thead><tr>@foreach ($h as $x)<th style="background:#fde7f3;text-align:center">{{ $x }}</th>@endforeach</tr></thead>
    <tbody>@foreach ($r as $row)<tr>@foreach ($row as $i => $cell)<td @if ($i === 0) style="font-weight:700" @endif>{{ $cell }}</td>@endforeach</tr>@endforeach</tbody>
</table></div>
