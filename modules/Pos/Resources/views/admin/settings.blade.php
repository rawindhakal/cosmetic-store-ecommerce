@extends('admin::layout')
@section('title', 'POS Settings')
@section('content_header')
    <h3>POS Settings</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li><a href="{{ route('admin.pos.index') }}">POS</a></li><li class="active">POS Settings</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ route('admin.pos.settings.update') }}" class="form-horizontal">{{ csrf_field() }}{{ method_field('put') }}
    <div class="box box-primary"><div class="box-body">
    @php
        $t = function ($k, $label, $help = null, $type = 'text') use ($v) { echo '<div class="form-group"><label class="col-md-3 control-label">'.e($label).'</label><div class="col-md-6"><input type="'.$type.'" step="any" name="'.$k.'" value="'.e(old($k, $v[$k])).'" class="form-control">'.($help ? '<span class="help-block">'.e($help).'</span>' : '').'</div></div>'; };
        $c = function ($k, $label) use ($v) { echo '<div class="form-group"><div class="col-md-6 col-md-offset-3"><div class="checkbox"><input type="checkbox" id="'.$k.'" name="'.$k.'" value="1" '.(old($k, $v[$k]) ? 'checked' : '').'><label for="'.$k.'">'.e($label).'</label></div></div></div>'; };
    @endphp
    <h4>Receipt header</h4>
    @php $t('store_name', 'Business name', 'Blank = store name from Settings'); $t('address', 'Address'); $t('phone', 'Phone'); $t('tax_id', 'PAN / VAT number'); $t('receipt_title', 'Bill title', 'e.g. TAX INVOICE'); @endphp
    <hr><h4>Tax &amp; totals</h4>
    <div class="form-group"><label class="col-md-3 control-label">Tax mode</label><div class="col-md-6"><select name="tax_mode" class="form-control">@foreach (['inclusive' => 'Prices include tax', 'exclusive' => 'Add tax on top', 'none' => 'No tax'] as $k => $l)<option value="{{ $k }}" {{ old('tax_mode', $v['tax_mode']) === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div></div>
    @php $t('tax_rate', 'Tax rate %'); $t('tax_label', 'Tax label (VAT)'); $c('round_total', 'Round total to nearest whole amount'); $c('allow_price_override', 'Allow cashier to change unit price'); $c('allow_oversell', 'Allow selling below zero stock'); $c('require_register', 'Require an open cash register before selling'); $t('low_stock', 'Low-stock warning at', null, 'number'); $t('invoice_prefix', 'Invoice prefix'); @endphp
    <hr><h4>Printing (thermal)</h4>
    <div class="form-group"><label class="col-md-3 control-label">Paper width</label><div class="col-md-6"><select name="paper_width" class="form-control"><option value="80" {{ old('paper_width', $v['paper_width']) == 80 ? 'selected' : '' }}>80 mm</option><option value="58" {{ old('paper_width', $v['paper_width']) == 58 ? 'selected' : '' }}>58 mm</option></select></div></div>
    @php $t('font_size', 'Font size (px)', 'Try 12 for 80mm, 11 for 58mm', 'number'); $c('auto_print', 'Auto-print receipt after each sale'); $c('show_logo', 'Print logo'); $c('show_barcode', 'Print bill barcode'); $c('show_savings', 'Print "You saved" box'); $t('return_policy', 'Return policy line'); $t('receipt_footer', 'Footer message'); @endphp
    </div><div class="box-footer"><button class="btn btn-primary">Save settings</button> <a class="btn btn-default" target="_blank" href="{{ route('admin.pos.sales') }}">View sales</a></div></div></form>
@endsection
