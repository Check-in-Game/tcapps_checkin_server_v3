@extends('user/master')
@section('before_nav')
@endsection

@section('container')
<div class="alert alert-warning mt-4 text-center" role="alert">
  部分情况下可能会出现未结清的情况，系统会在稍后自动清算。未清算的记录可能会影响您后续的购买操作，刷新页面后如仍然存在未清算情况，请加群联系管理员。
</div>

<div class="row">

  <table class="table table-striped table-hover text-center">
    <thead>
      <tr>
        <th scope="col">订单号</th>
        <th scope="col">消耗时间</th>
        <th scope="col">消耗积分</th>
        <th scope="col">状态</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($charts as $key => $chart)
          <tr>
            <th scope="row">
              #{{ $chart->pid }}
            </th>
            <th scope="row">
              {{ $chart->purchase_time }}
            </th>
            <th scope="row">
              {{ $chart->cost }}
            </th>
            <th scope="row">
              @if( $chart->status === 1 )
              <span class="badge badge-success">已清算</span>
              @elseif( $chart->status === 0 )
              <span class="badge badge-warning">未结清</span>
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
@endsection
<script type="text/javascript">
  window.onload = function(){
    $('.pagination').addClass('justify-content-center');
  }
</script>
