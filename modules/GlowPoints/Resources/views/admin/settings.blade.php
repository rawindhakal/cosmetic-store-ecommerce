@extends('admin::layout')

@section('title', $values['name'] . ' Settings')

@section('content_header')
    <h3>{{ $values['name'] }} Settings</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.glowpoints.index') }}">{{ $values['name'] }}</a></li>
        <li class="active">Settings</li>
    </ol>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.glowpoints.settings.update') }}" class="form-horizontal">
        {{ csrf_field() }}
        {{ method_field('put') }}

        <div class="box box-primary">
            <div class="box-body">
                @if ($errors->any())
                    <div class="alert alert-danger">@foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
                @endif

                @php
                    $field = function ($key, $label, $help = null, $type = 'number', $step = 'any') use ($values) {
                        $v = old($key, $values[$key]);
                        echo '<div class="form-group"><label class="col-md-3 control-label">'.e($label).'</label><div class="col-md-6">'
                           . '<input type="'.$type.'" step="'.$step.'" name="'.$key.'" value="'.e($v).'" class="form-control">'
                           . ($help ? '<span class="help-block">'.e($help).'</span>' : '') . '</div></div>';
                    };
                @endphp

                <h4>General</h4>
                <div class="form-group">
                    <label class="col-md-3 control-label">Enable program</label>
                    <div class="col-md-6">
                        <div class="checkbox"><input type="checkbox" name="enabled" id="gp-enabled" value="1" {{ old('enabled', $values['enabled']) ? 'checked' : '' }}><label for="gp-enabled">Customers can earn and redeem points</label></div>
                    </div>
                </div>
                @php $field('name', 'Points name', 'Shown to customers everywhere, e.g. GlowPoints.', 'text'); @endphp

                <hr><h4>Earning</h4>
                @php
                    $field('earn_points', 'Points earned', 'Number of points awarded ...');
                    $field('earn_per_amount', 'For every amount spent', '... for every this much spent (in the store currency). Example: 1 point per 100.');
                @endphp
                <div class="form-group">
                    <label class="col-md-3 control-label">Earn on</label>
                    <div class="col-md-6">
                        <select name="earn_base" class="form-control">
                            <option value="subtotal" {{ old('earn_base', $values['earn_base']) === 'subtotal' ? 'selected' : '' }}>Subtotal after discounts (excludes shipping & tax)</option>
                            <option value="total" {{ old('earn_base', $values['earn_base']) === 'total' ? 'selected' : '' }}>Order total</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-3 control-label">Award points when order becomes</label>
                    <div class="col-md-6">
                        @foreach ($statuses as $key => $label)
                            <div class="checkbox"><input type="checkbox" name="earn_statuses[]" id="st-{{ $key }}" value="{{ $key }}" {{ in_array($key, (array) old('earn_statuses', $values['earn_statuses'])) ? 'checked' : '' }}><label for="st-{{ $key }}">{{ $label }}</label></div>
                        @endforeach
                        <span class="help-block">Points are awarded once per order, the first time it reaches a selected status. Canceled/refunded orders reverse them.</span>
                    </div>
                </div>
                @php
                    $field('signup_bonus', 'Welcome bonus', 'Points given when a customer registers (0 = off).', 'number', '1');
                    $field('first_order_bonus', 'First order bonus', 'Extra points on a customer\'s first rewarded order (0 = off).', 'number', '1');
                    $field('expiry_days', 'Points expire after (days)', 'Earned points expire this many days after being awarded (0 = never).', 'number', '1');
                @endphp

                <hr><h4>Redeeming</h4>
                @php
                    $field('redeem_value', 'Value of 1 point', 'Discount per point in the store currency. Example: 1 means 100 points = 100 off.');
                    $field('min_redeem', 'Minimum points per redemption', null, 'number', '1');
                    $field('max_redeem_percent', 'Max % of order payable with points', 'Applied to subtotal after coupons (0-100).');
                @endphp
            </div>

            <div class="box-footer">
                <button type="submit" class="btn btn-primary">Save settings</button>
            </div>
        </div>
    </form>
@endsection
