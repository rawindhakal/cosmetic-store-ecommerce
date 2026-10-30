@extends('admin::layout')
@section('title', 'Size Chart')
@section('content_header')
    <h3>Size Chart</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li>Beauty &amp; Fashion</li><li class="active">Size Chart</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ $c ? route('admin.beauty.sizecharts.update', $c->id) : route('admin.beauty.sizecharts.store') }}">{{ csrf_field() }}@if ($c){{ method_field('put') }}@endif
    <div class="box box-primary"><div class="box-body">
        <div class="form-group"><label>Name</label><input name="name" class="form-control" value="{{ old('name', $c->name ?? '') }}" required></div>
        <div class="form-group"><label>Note (shown above the chart)</label><input name="note" class="form-control" value="{{ old('note', $c->note ?? '') }}"></div>
        <div class="form-group"><label>Column headers (comma separated)</label><input name="headers" class="form-control" value="{{ old('headers', $c ? implode(', ', json_decode($c->headers, true)) : 'Size, Bust, Waist, Hip') }}" required></div>
        <div class="form-group"><label>Rows (one per line, values comma separated)</label><textarea name="rows" class="form-control" rows="8" required>{{ old('rows', $c ? collect(json_decode($c->rows, true))->map(fn ($r) => implode(', ', $r))->implode("\n") : "S, 34, 28, 36\nM, 36, 30, 38") }}</textarea></div>
    </div><div class="box-footer"><button class="btn btn-primary">Save</button> <a class="btn btn-default" href="{{ route('admin.beauty.sizecharts') }}">Cancel</a></div></div></form>
@endsection
