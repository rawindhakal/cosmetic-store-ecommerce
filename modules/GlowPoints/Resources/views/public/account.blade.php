@extends('storefront::public.account.layout')

@section('title', $name)

@section('account_breadcrumb')
    <li class="active">{{ $name }}</li>
@endsection

@section('panel')
    <div class="panel">
        <div class="panel-header"><h4>{{ $name }}</h4></div>

        <div class="panel-body">
            <h3 class="color-primary" style="margin-bottom:4px">{{ number_format($balance) }} {{ $name }}</h3>
            <p>Worth {{ \Modules\Support\Money::inDefaultCurrency($balance * $pointValue)->convertToCurrentCurrency()->format() }} off your next order.</p>

            <div class="table-responsive" style="margin-top:20px">
                <table class="table table-borderless">
                    <thead><tr><th>Date</th><th>Activity</th><th>Points</th><th>Balance</th></tr></thead>
                    <tbody>
                    @forelse ($transactions as $t)
                        <tr>
                            <td>{{ $t->created_at->format('M d, Y') }}</td>
                            <td>{{ $t->description ?: ucfirst($t->type) }}@if ($t->expires_at && $t->points > 0)<br><small>Expires {{ $t->expires_at->format('M d, Y') }}</small>@endif</td>
                            <td class="{{ $t->points > 0 ? 'color-primary' : '' }}">{{ $t->points > 0 ? '+' : '' }}{{ $t->points }}</td>
                            <td>{{ $t->balance_after }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No activity yet. Place an order to start earning {{ $name }}.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transactions->links() }}
        </div>
    </div>
@endsection
