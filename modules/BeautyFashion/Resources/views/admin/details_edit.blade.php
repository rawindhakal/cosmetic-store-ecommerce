@extends('admin::layout')
@section('title', 'Edit Product Details')
@section('content_header')
    <h3>Edit Product Details</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li>Beauty &amp; Fashion</li><li class="active">Edit Product Details</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ route('admin.beauty.details.update', $p->id) }}">{{ csrf_field() }}{{ method_field('put') }}
    <div class="box box-primary"><div class="box-header"><h4>{{ $p->name }}</h4></div><div class="box-body">@include('beautyfashion::admin._details_fields')</div>
    <div class="box-footer"><button class="btn btn-primary">Save</button> <a class="btn btn-default" href="{{ route('admin.beauty.details') }}">Back</a> <a class="btn btn-default" href="{{ route('admin.products.edit', $p->id) }}">Open product</a></div></div></form>
@endsection
