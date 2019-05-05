@extends('admin/master')
@section('container')
<!-- 公告-27 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>商品一览 / Goods</h2>
<table class="table table-striped table-hover text-center">
  <thead>
    <tr>
      <th scope="col">GID</th>
      <th scope="col">名称</th>
      <th scope="col">售价</th>
      <th scope="col">状态</th>
    </tr>
  </thead>
  <tbody>
      @foreach ($charts as $key => $chart)
        <tr>
          <th scope="row">
            #{{ $chart->gid }}
          </th>
          <th scope="row">
            {{ $chart->gname }}
          </th>
          <th scope="row">
            {{ $chart->cost }}
          </th>
          <th scope="row">
            {{ $chart->status }}
          </th>
        </tr>
      @endforeach
    </tr>
  </tbody>
</table>
<nav style="text-align: center">
  {{ $charts->links() }}
</nav>
@endsection
@section('script')
<script type="text/javascript">
  window.onload = function(){
    $('.pagination').addClass('justify-content-center');
  }
</script>
@endsection
