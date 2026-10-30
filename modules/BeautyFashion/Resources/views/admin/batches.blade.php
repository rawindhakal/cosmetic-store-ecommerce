@extends('admin::layout')
@section('title', 'Batches & Expiry')
@section('content_header')
    <h3>Batches & Expiry</h3>
    <ol class="breadcrumb"><li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li><li>Beauty &amp; Fashion</li><li class="active">Batches & Expiry</li></ol>
@endsection
@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <div class="row">
        <div class="col-md-3"><div class="box box-primary"><div class="box-body"><h4>Batches</h4><h2>{{ $total }}</h2></div></div></div>
        <div class="col-md-3"><div class="box box-danger"><div class="box-body"><h4>Expired</h4><h2 class="text-danger">{{ $expired }}</h2><small>{{ $expiredQty }} units on hand</small></div></div></div>
        <div class="col-md-3"><div class="box box-warning"><div class="box-body"><h4>Expiring in 90 days</h4><h2 style="color:#e08a00">{{ $soon }}</h2></div></div></div>
    </div>
    <div class="box box-primary"><div class="box-header"><h4>Add a batch</h4></div><div class="box-body"><form method="POST" action="{{ route('admin.beauty.batches.store') }}" class="form-inline">{{ csrf_field() }}
        <select name="product_id" class="form-control" style="max-width:260px" required><option value="">Product...</option>@foreach ($products as $p)<option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>@endforeach</select>
        <input name="batch_no" class="form-control" placeholder="Batch no." required style="width:130px">
        <input type="date" name="mfg_date" class="form-control" title="Manufactured"> <input type="date" name="expiry_date" class="form-control" title="Expiry" required>
        <input type="number" name="qty" class="form-control" placeholder="Qty" required style="width:90px">
        <label><input type="checkbox" name="add_stock" value="1"> add to stock</label> <button class="btn btn-primary">Add</button></form></div></div>
    <div class="box box-primary"><div class="box-header"><a class="btn btn-sm {{ $filter === 'attention' ? 'btn-primary' : 'btn-default' }}" href="?f=attention">Needs attention</a> <a class="btn btn-sm {{ $filter === 'expired' ? 'btn-primary' : 'btn-default' }}" href="?f=expired">Expired</a> <a class="btn btn-sm {{ $filter === 'soon' ? 'btn-primary' : 'btn-default' }}" href="?f=soon">Expiring soon</a> <a class="btn btn-sm {{ $filter === 'all' ? 'btn-primary' : 'btn-default' }}" href="?f=all">All</a>
        <form class="form-inline" style="display:inline;margin-left:10px"><input type="hidden" name="f" value="{{ $filter }}"><input name="q" class="form-control input-sm" placeholder="Search product / batch" value="{{ request('q') }}"></form></div>
    <div class="box-body table-responsive"><table class="table table-striped"><thead><tr><th>Product</th><th>Batch</th><th>Mfg</th><th>Expiry</th><th>Status</th><th>Qty</th><th></th></tr></thead><tbody>
    @forelse ($batches as $b) @php $days = (int) now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($b->expiry_date), false); @endphp
        <tr><td>{{ $b->name }} {{ $b->variant ? '('.$b->variant.')' : '' }}</td><td>{{ $b->batch_no }}</td><td>{{ $b->mfg_date }}</td><td>{{ $b->expiry_date }}</td>
        <td>@if ($days < 0)<span class="label label-danger">Expired {{ abs($days) }}d ago</span>@elseif ($days <= 90)<span class="label label-warning">{{ $days }} days left</span>@else<span class="label label-success">OK</span>@endif</td><td>{{ $b->qty }}</td>
        <td class="text-right">@if ($days < 0)<form method="POST" action="{{ route('admin.beauty.batches.writeoff', $b->id) }}" style="display:inline" onsubmit="return confirm('Remove {{ $b->qty }} units from stock?')">{{ csrf_field() }}<button class="btn btn-danger btn-sm">Write off</button></form>@endif
        <form method="POST" action="{{ route('admin.beauty.batches.destroy', $b->id) }}" style="display:inline" onsubmit="return confirm('Delete batch record?')">{{ csrf_field() }}{{ method_field('delete') }}<button class="btn btn-default btn-sm">&times;</button></form></td></tr>
    @empty<tr><td colspan="7" class="text-center">Nothing here.</td></tr>@endforelse</tbody></table>{{ $batches->links() }}</div></div>
@endsection
