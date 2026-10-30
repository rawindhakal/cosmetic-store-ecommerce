@extends('storefront::public.account.layout')
@section('title', 'Skin Analysis')
@section('account_breadcrumb')<li class="active">Skin Analysis</li>@endsection
@section('panel')
    <div class="panel">
        <div class="panel-header"><h4>My Skin Analysis</h4></div>
        <div class="panel-body">
            <a class="btn btn-primary" href="{{ route('skin_analysis.index') }}">New analysis</a>
            <div class="table-responsive" style="margin-top:16px"><table class="table table-borderless">
                <thead><tr><th>Date</th><th>Score</th><th>Skin type</th><th>Shade</th><th>Top concerns</th></tr></thead><tbody>
                @forelse ($rows as $r)
                    @php $c = collect(json_decode($r->concerns, true))->where('score', '>=', 30)->sortByDesc('score')->take(3); @endphp
                    <tr><td>{{ \Carbon\Carbon::parse($r->created_at)->format('M d, Y') }}</td><td><b>{{ $r->overall_score }}</b>/100</td><td>{{ $r->skin_type }}</td><td>{{ $r->tone_shade }}</td>
                    <td>{{ $c->map(fn ($x) => ($labels[$x['key']] ?? $x['key']).' ('.$x['score'].')')->implode(', ') ?: 'None' }}</td></tr>
                @empty<tr><td colspan="5">No saved analyses yet.</td></tr>@endforelse
                </tbody></table></div>
            {{ $rows->links() }}
        </div>
    </div>
@endsection
