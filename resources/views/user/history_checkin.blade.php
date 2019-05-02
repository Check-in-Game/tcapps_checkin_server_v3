@extends('user/master')
@section('before_nav')
@endsection

@section('container')
<!-- 公告-8 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<div class="alert alert-primary mt-4 text-center" role="alert">
  目前记录可查询最多 {{ $limit }} @if( $unit === 'day' ) 天 @elseif( $unit === 'week' ) 周 @elseif( $unit === 'month' ) 个月 @elseif( $unit === 'year' ) 年 @endif
</div>

<div class="row">

  <table class="table table-striped table-hover text-center">
    <thead>
      <tr>
        <th scope="col">签到ID</th>
        <th scope="col">时间</th>
        <th scope="col">积分</th>
        <th scope="col">类型</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($charts as $key => $chart)
          <tr>
            <th scope="row">
              #{{ $chart->cid }}
            </th>
            <th scope="row">
              {{ $chart->check_time }}
            </th>
            <th scope="row">
              {{ $chart->worth }}
            </th>
            <th scope="row">
              @if( $chart->tid === 0 )
              <span class="badge badge-success">签到</span>
              @elseif( $chart->tid === 1 )
              <span class="badge badge-warning">活动</span>
              @elseif( $chart->tid === 2 )
              <span class="badge badge-primary">系统</span>
              @elseif( $chart->tid === 3 )
              <span class="badge badge-secondary">补偿</span>
              @elseif( $chart->tid === 4 )
              <span class="badge badge-success">签补</span>
              @elseif( $chart->tid === 5 )
              <span class="badge badge-info">结算</span>
              @elseif( $chart->tid === 6 )
              <span class="badge badge-primary">赞助</span>
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
</script>
@endsection
