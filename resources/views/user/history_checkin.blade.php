@extends('user/master')
@section('container')
<div class="alert alert-primary mt-4 text-center" role="alert">
  目前记录可查询最多 {{ $limit }} @if( $unit === 'day' ) 天 @elseif( $unit === 'week' ) 周 @elseif( $unit === 'month' ) 个月 @elseif( $unit === 'year' ) 年 @endif
</div>

<div>
  <div class="row text-center">
    <div class="col-sm mb-3">
      <button type="button" class="btn btn-primary btn-block"  onclick="javascript:location.href='{{ action('UserController@history_checkin') }}';">签到记录</button>
    </div>
    <div class="col-sm mb-3">
      <button type="button" class="btn btn-primary btn-block" onclick="javascript:location.href='{{ action('UserController@shop') }}';">兑换中心</button>
    </div>
    <div class="col-sm mb-3">
      <button type="button" class="btn btn-primary btn-block" disabled>积分账单（建设中）</button>
    </div>
    <div class="col-sm mb-3">
      <button type="button" class="btn btn-primary btn-block" disabled>活动中心（建设中）</button>
    </div>
  </div>

  <div class="row">

    <!-- 账户评级 -->
    <table class="table table-striped table-hover text-center">
      <thead>
        <tr>
          <th scope="col">签到时间</th>
          <th scope="col">签到积分</th>
          <th scope="col">状态</th>
        </tr>
      </thead>
      <tbody>
          @foreach ($charts as $key => $chart)
            <tr>
              <th scope="row">
                {{ $chart->check_time }}
              </th>
              <th scope="row">
                {{ $chart->worth }}
              </th>
              <th scope="row">
                @if( $chart->status === 1 )
                <span class="badge badge-success">正常</span>
                @else
                <span class="badge badge-danger">异常</span>
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

</div>
@endsection
<script type="text/javascript">
  window.onload = function(){
    $('.pagination').addClass('justify-content-center');
  }
</script>
