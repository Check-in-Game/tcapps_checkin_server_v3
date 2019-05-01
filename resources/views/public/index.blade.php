@extends('public.master')
@section('headerExtraContent')
  <!-- 幕布 -->
  <div class="jumbotron pb-2">
    <div class="container">
      <h1 class="display-4">Check-in Game</h1>
      <p class="lead">签到排行榜实时更新，签到每隔5分钟即可进行一次，只需简单注册账户即可开始游戏！</p>
      <hr class="my-4">
      <p class="lead">
        <a class="btn btn-success" href="{{ action('PublicController@register') }}" target="_blank" role="button">注册账户</a>
        <a class="btn btn-primary" href="{{ action('UserController@user') }}" target="_self" role="button">用户中心</a>
        <a class="btn btn-primary" href="{{ action('PublicController@webCheckin') }}" target="_self" role="button">在线端</a>
        <a class="btn btn-secondary" href="https://github.com/jokin1999/tcapps-checkin" target="_blank" role="button">Python客户端</a>
        <a class="btn btn-info" href="https://jq.qq.com/?_wv=1027&k=5ax4j23" target="_blank" role="button">加入交流QQ群：887304185</a>
      </p>
    </div>
  </div>
  <div class="container">
@endsection
@section('container')

    <!-- 公告-1 -->
    @foreach($_notices as $notice)
    <div class="alert alert-{{ $notice['color'] }}" role="alert">
      @if (!empty($notice['title']))
      <h4 class="alert-heading">{{ $notice['title'] }}</h4>
      @endif
      {{ $notice['content'] }}
    </div>
    @endforeach

    <div class="alert alert-success">
      周活跃榜显示自现在起，过去7*24小时内的签到积分。
    </div>

    <!-- 排行榜 -->
    <h2>周活跃榜</h2>

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
            <tr>
              <th scope="row">
                # {{ $key+1 }}
              </th>
              <th scope="row">
                {{ $chart->username }}
                @if( isset($badges[$chart->uid]) )
                  @foreach($badges[$chart->uid] as $badge)
                    @if( !empty($badge['image']) )
                    <img src="{{ $badge['image'] }}" alt="勋章预览" height="18px" title="{{ $badge['bname'] }}">
                    @else
                    <span class="badge badge-dark" style="color: {{ $badge['fgcolor'] }};background-color: {{ $badge['bgcolor'] }};">{{ $badge['bname'] }}</span>
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
