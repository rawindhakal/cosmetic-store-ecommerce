@extends('admin::layout')
@section('title', 'Product Details')
@section('content_header')
    <h3>Product Details</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li>Beauty &amp; Fashion</li><li class="active">Product Details</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div class="box box-primary"><div class="box-header"><form class="form-inline" method="GET"><input name="q" class="form-control" placeholder="Search product" value="{{ request('q') }}"> <label style="margin:0 8px"><input type="checkbox" name="missing" value="1" {{ request('missing') ? 'checked' : '' }}> Missing details only</label> <button class="btn btn-default">Filter</button></form></div>
    <div class="box-body table-responsive"><table class="table table-striped"><thead><tr><th>Product</th><th>Ingredients</th><th>Material</th><th>PAO</th><th>Size chart</th><th></th></tr></thead><tbody>
    @foreach ($rows as $r)<tr><td>{{ $r->name }}</td><td>{!! $r->ingredients ? '&#10003;' : '-' !!}</td><td>{{ $r->material ?: '-' }}</td><td>{{ $r->pao_months ? $r->pao_months.'M' : '-' }}</td><td>{!! $r->size_chart_id ? '&#10003;' : '-' !!}</td><td class="text-right"><a class="btn btn-default btn-sm" href="{{ route('admin.beauty.details.edit', $r->id) }}">Edit</a></td></tr>@endforeach
    </tbody></table>{{ $rows->links() }}</div></div>
@endsection
