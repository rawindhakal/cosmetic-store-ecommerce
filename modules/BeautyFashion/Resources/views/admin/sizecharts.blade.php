@extends('admin::layout')
@section('title', 'Size Charts')
@section('content_header')
    <h3>Size Charts</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li>Beauty &amp; Fashion</li><li class="active">Size Charts</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <p><a class="btn btn-primary" href="{{ route('admin.beauty.sizecharts.create') }}">New size chart</a></p>
    @foreach ($charts as $c) @php $h = json_decode($c->headers, true); $r = json_decode($c->rows, true); @endphp
    <div class="box box-primary"><div class="box-header"><h4 style="display:inline">{{ $c->name }}</h4> <small>{{ $used[$c->id] ?? 0 }} products</small>
        <span class="pull-right"><a class="btn btn-default btn-sm" href="{{ route('admin.beauty.sizecharts.edit', $c->id) }}">Edit</a>
        <form method="POST" action="{{ route('admin.beauty.sizecharts.destroy', $c->id) }}" style="display:inline" onsubmit="return confirm('Delete this chart?')">{{ csrf_field() }}{{ method_field('delete') }}<button class="btn btn-danger btn-sm">Delete</button></form></span></div>
    <div class="box-body table-responsive"><table class="table table-bordered table-condensed" style="width:auto"><tr>@foreach ($h as $x)<th>{{ $x }}</th>@endforeach</tr>@foreach ($r as $row)<tr>@foreach ($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>@endforeach</table></div></div>
    @endforeach
@endsection
