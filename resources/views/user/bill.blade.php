@extends('user/master')
@section('before_nav')
@endsection

@section('container')
<!-- 公告-9 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

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
@section('script')
<script type="text/javascript">
  window.onload = function(){
    $('.pagination').addClass('justify-content-center');
  }
</script>
@endsection
