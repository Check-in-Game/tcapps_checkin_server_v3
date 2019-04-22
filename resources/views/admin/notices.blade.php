@extends('admin/master')
@section('container')
<!-- 公告-16 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>公告一览 / All Notices</h2>
<div class="row">

  <table class="table table-striped table-hover text-center">
    <thead>
      <tr>
        <th scope="col">NID</th>
        <th scope="col">PID</th>
        <th scope="col">标题</th>
        <th scope="col">开始</th>
        <th scope="col">结束</th>
        <th scope="col">状态</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($charts as $key => $chart)
          <tr>
            <th scope="row">
              #{{ $chart->nid }}
            </th>
            <th scope="row">
              {{ $chart->place_id }}
            </th>
            <th scope="row">
              {{ $chart->title }}
            </th>
            <th scope="row">
              {{ $chart->starttime }}
            </th>
            <th scope="row">
              {{ $chart->endtime }}
            </th>
            <th scope="row">
              {{ $chart->status }}
            </th>
          </tr>
        @endforeach
      </tr>
    </tbody>
  </table>

</div>

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
