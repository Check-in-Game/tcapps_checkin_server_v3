@extends('admin/master')
@section('container')
<!-- 公告-20 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>活动一览 / Activities</h2>
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
<nav style="text-align: center">
  {{ $charts->links() }}
</nav>
@endsection
@section('script')
<script type="text/javascript">
function add() {
  let starttime = $('#starttime').val();
  let endtime   = $('#endtime').val();
  let min       = $('#min').val();
  let max       = $('#max').val();
  if (starttime == '' || endtime == '' || min == '' || max == '') {
    alert('请填写信息！');
    return false;
  }
  $('#btn').attr('disabled', 'disabled');
  $.getJSON('/api/admin/activity/add/' + starttime + '/' + endtime + '/' + min + '/' + max, function(data){
    if (data.errno === 0) {
      alert('增加成功！');
      $('#btn').removeAttr('disabled');
    }else{
      alert(data.error);
      $('#btn').removeAttr('disabled');
    }
  });
}
</script>
@endsection
