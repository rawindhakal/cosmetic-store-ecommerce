@php $g = fn ($k) => old($k, $d->$k ?? ''); @endphp
<div class="row">
    <div class="col-md-6">
        <h4>Cosmetics &amp; skin care</h4>
        <div class="form-group"><label>Ingredients (INCI list)</label><textarea name="ingredients" class="form-control" rows="3">{{ $g('ingredients') }}</textarea></div>
        <div class="form-group"><label>How to use</label><textarea name="how_to_use" class="form-control" rows="3">{{ $g('how_to_use') }}</textarea></div>
        <div class="form-group"><label>Benefits</label><textarea name="benefits" class="form-control" rows="2">{{ $g('benefits') }}</textarea></div>
        <div class="row">
            <div class="col-xs-4"><label>PAO (months)</label><input type="number" name="pao_months" class="form-control" value="{{ $g('pao_months') }}" title="Period after opening"></div>
            <div class="col-xs-4"><label>Shelf life (months)</label><input type="number" name="shelf_life_months" class="form-control" value="{{ $g('shelf_life_months') }}"></div>
            <div class="col-xs-4"><label>Net content</label><input name="net_content" class="form-control" value="{{ $g('net_content') }}" placeholder="50 ml"></div>
        </div>
    </div>
    <div class="col-md-6">
        <h4>Fashion</h4>
        <div class="form-group"><label>Material / fabric composition</label><input name="material" class="form-control" value="{{ $g('material') }}" placeholder="100% Cotton"></div>
        <div class="form-group"><label>Care instructions</label><textarea name="care_instructions" class="form-control" rows="3">{{ $g('care_instructions') }}</textarea></div>
        <div class="form-group"><label>Model info</label><input name="model_info" class="form-control" value="{{ $g('model_info') }}" placeholder="Model is 5'7&quot; and wears size S"></div>
        <div class="form-group"><label>Size chart</label>
            <select name="size_chart_id" class="form-control"><option value="">- none -</option>@foreach ($charts as $c)<option value="{{ $c->id }}" {{ $g('size_chart_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select></div>
        <div class="form-group"><label>Country of origin</label><input name="country_of_origin" class="form-control" value="{{ $g('country_of_origin') }}"></div>
    </div>
</div>
