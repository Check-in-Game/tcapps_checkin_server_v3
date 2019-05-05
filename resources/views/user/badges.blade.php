@extends('user/master')
@section('before_nav')
@endsection

@section('container')
<!-- 公告-25 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<div class="alert alert-primary mt-4" role="alert">
  当前允许佩戴的勋章数量为 {{ $limit }} 枚。勋章增益计算时，小数位将被四舍五入。
</div>

<div class="row">

  <table class="table table-striped table-hover text-center">
    <thead>
      <tr>
        <th scope="col">勋章</th>
        <th scope="col">名称</th>
        <th scope="col">增益</th>
        <th scope="col">获得时间</th>
        <th scope="col">操作</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($charts as $key => $chart)
          <tr>
            <th scope="row">
              @if( !empty($chart->image) )
              <img src="{{ $chart->image }}" alt="勋章预览" height="18px">
              @else
              <span class="badge badge-dark" style="color: {{ $chart->fgcolor }};background-color: {{ $chart->bgcolor }};">{{ $chart->bname }}</span>
              @endif
            </th>
            <th scope="row">
              {{ $chart->bname }}
            </th>
            <th scope="row">
              {{ $chart->times }}倍
            </th>
            <th scope="row">
              {{ $chart->purchase_time }}
            </th>
            <th scope="row">
              @if( in_array($chart->bid, $wear) )
              <div class="badge badge-secondary" onclick="javascript:takeoff({{ $chart->bid }});">取消佩戴</div>
              @else
              <div class="badge badge-primary" onclick="javascript:wear({{ $chart->bid }});">点击佩戴</div>
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
@section('script')
<script type="text/javascript">
  window.onload = function(){
    $('.pagination').addClass('justify-content-center');
  }
  function wear(bid) {
    $.ajax({
      url: '/api/user/badge/wear',
      type: 'post',
      data: {
        'bid': bid,
      },
      dataType: 'json',
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        if (status == 'timeout') {
          m_alert('请求超时，请稍候再试！', 'warning');
          return false;
        }
      },
      success: function(data) {
        if (data.errno === 0) {
          m_alert('佩戴成功！', 'success');
          location.href = '';
        }else{
          console.log(data.error);
          if(data.errno === 3404) {
            m_alert('佩戴失败，佩戴数量超出上限！', 'danger');
          }else{
            m_alert('佩戴失败，请刷新后再试！', 'danger');
          }
        }
      }
    });
  }
  function takeoff(bid) {
    $.ajax({
      url: '/api/user/badge/takeoff',
      type: 'post',
      data: {
        'bid': bid,
      },
      dataType: 'json',
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        if (status == 'timeout') {
          m_alert('请求超时，请稍候再试！', 'warning');
          return false;
        }
      },
      success: function(data) {
        if (data.errno === 0) {
          m_alert('取消佩戴成功！', 'success');
          location.href = '';
        }else{
          console.log(data.error);
          m_alert('取消佩戴失败，请刷新后再试！', 'danger');

        }
      }
    });
  }
</script>
@endsection
