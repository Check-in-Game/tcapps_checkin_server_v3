@extends('admin/master')
@section('container')
<!-- 公告-21 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>勋章一览 / Badges</h2>
<table class="table table-striped table-hover text-center">
  <thead>
    <tr>
      <th scope="col">勋章ID</th>
      <th scope="col">名称</th>
      <th scope="col">商品ID</th>
      <th scope="col">状态</th>
    </tr>
  </thead>
  <tbody>
      @foreach ($charts as $key => $chart)
        <tr>
          <th scope="row">
            #{{ $chart->bid }}
          </th>
          <th scope="row">
            {{ $chart->bname }}
          </th>
          <th scope="row">
            {{ $chart->gid }}
          </th>
          <th scope="row">
            @if( $chart->status === 1 )
            <span class="badge badge-success">正常</span>
            @else
            <span class="badge badge-warning">{{ $chart->status }}</span>
            @endif
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
