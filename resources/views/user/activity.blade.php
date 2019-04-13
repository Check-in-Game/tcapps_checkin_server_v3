@extends('user/master')
@section('before_nav')
@endsection

@section('container')
<!-- 公告-10 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<div class="alert alert-primary mt-4 text-center" role="alert">
  结算时，活动只能同时参加1个，两个活动冲突时，默认参与活动ID靠前的一个。
</div>

<div class="row">

  <table class="table table-striped table-hover text-center">
    <thead>
      <tr>
        <th scope="col">活动ID</th>
        <th scope="col">开始时间</th>
        <th scope="col">结束时间</th>
        <th scope="col">积分区间</th>
        <th scope="col">状态</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($charts as $key => $chart)
          <tr>
            <th scope="row">
              #{{ $chart->aid }}
            </th>
            <th scope="row">
              {{ $chart->starttime }}
            </th>
            <th scope="row">
              {{ $chart->endtime }}
            </th>
            <th scope="row">
              {{ $chart->min_worth }} - {{ $chart->max_worth }}
            </th>
            <th scope="row">
              @if( strtotime($chart->starttime) <= time() && strtotime($chart->endtime) >= time() )
              <span class="badge badge-success">正在进行</span>
              @elseif(strtotime($chart->endtime) >= time())
              <span class="badge badge-primary">即将开始</span>
              @else
              <span class="badge badge-secondary">已经结束</span>
              @endif
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
<script type="text/javascript">
  window.onload = function(){
    $('.pagination').addClass('justify-content-center');
  }
</script>
