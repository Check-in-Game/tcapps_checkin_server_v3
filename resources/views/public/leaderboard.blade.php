@extends('master_panel')
@section('headerExtraContent')
  <!-- 幕布 -->
  <div class="jumbotron pb-2">
    <div class="container">
      <h1 class="display-4">{{ $typeName }}</h1>
      <p class="lead">签到排行榜实时更新，显示前100名。</p>
    </div>
  </div>
  <div class="container">
@endsection
@section('container')

    <!-- 公告-28 -->
    @foreach($_notices as $notice)
    <div class="alert alert-{{ $notice['color'] }}" role="alert">
      @if (!empty($notice['title']))
      <h4 class="alert-heading">{{ $notice['title'] }}</h4>
      @endif
      {{ $notice['content'] }}
    </div>
    @endforeach

    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th scope="col">排名</th>
          <th scope="col">用户名</th>
          <th scope="col">签到积分</th>
        </tr>
      </thead>
      <tbody>
          @foreach ($charts as $key => $chart)
            <tr style="line-height:26px;">
              <th scope="row">
                # {{ $key+1 }}
              </th>
              <th scope="row">
                {{ $chart->username }}
                @if( isset($badges[$chart->uid]) )
                  @foreach($badges[$chart->uid] as $badge)
                    @if( !empty($badge['image']) )
                    <img src="{{ $badge['image'] }}" alt="{{ $badge['bname'] }}勋章" height="26px" data-toggle="tooltip"  title="{{ $badge['bname'] }}勋章" name="badge">
                    @else
                    <span class="badge badge-dark" style="color: {{ $badge['fgcolor'] }};background-color: {{ $badge['bgcolor'] }};" data-toggle="tooltip" title="{{ $badge['bname'] }}勋章" name="badge">{{ $badge['bname'] }}</span>
                    @endif
                  @endforeach
                @endif
              </th>
              <th scope="row">
                {{ $chart->allWorth }}
              </th>
            </tr>
          @endforeach
        </tr>
      </tbody>
    </table>

    <hr />

    <div class="alert alert-info" role="alert">
      <h4 class="alert-heading">联系我们</h4>
      <p>
        官方QQ群：887304185
        <br />
        意见或建议提交：jokin@twocola.com
      </p>
      <hr />
      <p class="mb-0">感谢支持！</p>
    </div>
@endsection
@section('script')
<script type="text/javascript">
  $(function(){
    $('[name=badge]').tooltip('enable');
  });
</script>
@endsection
