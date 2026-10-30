@extends('admin::layout')
@section('title', 'Skin Analysis')
@section('content_header')
    <h3>Skin Analysis</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li class="active">Skin Analysis</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <form method="POST" action="{{ route('admin.skin_analysis.update') }}" class="form-horizontal">{{ csrf_field() }}{{ method_field('put') }}
    <div class="box box-primary"><div class="box-body">
        <p class="text-muted">The analysis runs in the customer's browser (face mesh model + image-analysis engines). Photos never leave the device unless a customer ticks the optional AI second-opinion consent box. Only measurements are saved for logged-in customers.</p>
        <div class="form-group"><label class="col-md-3 control-label">Enable</label><div class="col-md-6"><div class="checkbox"><input type="checkbox" id="en" name="enabled" value="1" {{ $enabled ? 'checked' : '' }}><label for="en">Show the Skin Analysis page</label></div></div></div>
        <div class="form-group"><label class="col-md-3 control-label">Disclaimer text</label><div class="col-md-6"><textarea name="disclaimer" class="form-control" rows="2" placeholder="Default medical disclaimer is used if empty">{{ old('disclaimer', $disclaimer) }}</textarea></div></div>
        <hr><h4>Optional AI second opinion (vision LLM)</h4>
        <div class="form-group"><label class="col-md-3 control-label">Anthropic API key</label><div class="col-md-6"><input type="password" name="api_key" class="form-control" autocomplete="off" placeholder="{{ $hasKey ? 'Key saved - leave blank to keep' : 'sk-ant-...' }}">
            @if ($hasKey)<div class="checkbox"><input type="checkbox" id="ck" name="clear_key" value="1"><label for="ck">Remove saved key</label></div>@endif
            <span class="help-block">When set, customers see an opt-in "AI second opinion" button. Their photo is sent to Anthropic for that single request only.</span></div></div>
        <div class="form-group"><label class="col-md-3 control-label">Model</label><div class="col-md-6"><input name="model" class="form-control" value="{{ old('model', $model) }}"></div></div>
    </div><div class="box-footer"><button class="btn btn-primary">Save</button></div></div></form>
    <div class="box"><div class="box-header"><h4>Saved analyses ({{ $count }})</h4></div><div class="box-body table-responsive"><table class="table"><thead><tr><th>Date</th><th>Customer</th><th>Score</th><th>Skin type</th><th>Shade</th></tr></thead><tbody>
        @forelse ($recent as $r)<tr><td>{{ $r->created_at }}</td><td>#{{ $r->user_id }}</td><td>{{ $r->overall_score }}</td><td>{{ $r->skin_type }}</td><td>{{ $r->tone_shade }}</td></tr>@empty<tr><td colspan="5">None yet.</td></tr>@endforelse</tbody></table></div></div>
@endsection
