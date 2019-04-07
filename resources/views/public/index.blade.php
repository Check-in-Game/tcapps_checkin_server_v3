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
        <a class="btn btn-primary" href="{{ action('PublicController@webCheckin') }}" target="_self" role="button">【推荐】在线端</a>
        <a class="btn btn-primary" href="https://appsmirror.twocola.com/checkin.exe" target="_blank" role="button">客户端</a>
        <a class="btn btn-info" href="https://jq.qq.com/?_wv=1027&k=5ax4j23" target="_blank" role="button">加入交流QQ群：887304185</a>
      </p>
      <div class="alert alert-warning md-0" role="alert">
        客户端将不再被官方支持，但是仍然接受社区维护，存在无法签到的情况请耐心等待社区维护。
        <br />
        欢迎向客户端<a href="https://github.com/jokin1999/tcapps-checkin" target="_blank">仓库</a>提交PR，每次审核通过的PR可以获得500积分奖励，不计上限。
        <br />
        资料：<a href="https://github.com/jokin1999/tcapps-checkin/wiki/APIv1" target="_blank">API-v1协议</a>
        |
        <a href="https://github.com/jokin1999/tcapps-checkin/wiki/APIv2" target="_blank">API-v2协议（新）</a>
      </div>
    </div>
  </div>
  <div class="container">
@endsection
@section('container')
    <!-- 活动 -->
    <div class="alert alert-primary" role="alert">
      <h4 class="alert-heading">活动公告</h4>
      <p>
        <strong>2019年4月8日：</strong>用户中心、积分兑换中心开通与神秘积分兑换活动开启。
      </p>
      <hr>
      <p class="mb-0">活动以具体情况为准。</p>
    </div>
    <div class="alert alert-success" role="alert">
      排行榜目前实时更新，显示前100名。
    </div>

    <!-- 排行榜 -->
    <h2>排行榜</h2>

    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th scope="col">排名</th>
          <th scope="col">UID</th>
          <th scope="col">用户名</th>
          <th scope="col">签到积分</th>
        </tr>
      </thead>
      <tbody>
          @foreach ($charts as $key => $chart)
            <tr>
              <th scope="row">
                {{ $key+1 }}
              </th>
              <th scope="row">
                {{ $chart->uid }}
              </th>
              <th scope="row">
                {{ $chart->username }}
                @if(in_array($chart->uid, $nc_badge) )
                <span class="badge badge-dark">内测</span>
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

    <h2>其他</h2>

    <div class="alert alert-primary" role="alert">
      <h4 class="alert-heading">鸣谢</h4>
      <p><strong>2019-04-01</strong> 感谢<strong>FallArk</strong>同学提交Bug反馈！奖励10积分。</p>
      <p><strong>2019-03-31</strong> 感谢<strong>luogu.org</strong>同学提交运行错误反馈！奖励5积分。</p>
      <p><strong>2019-03-31</strong> 感谢<strong>luogu.org</strong>同学提交Bug反馈！奖励100积分。</p>
      <p><strong>2019-03-29</strong> 感谢<strong>FallArk</strong>同学参与内部测试并协助修复了若干Bug！</p>
      <hr />
      <p class="mb-0">感谢以上同学对本项目的支持！</p>
    </div>

    <div class="alert alert-warning" role="alert">
      <h4 class="alert-heading">提交建议 / 加入开发</h4>
      <p>这款游戏是开发者Jokin在闲暇时间开发的，因为上线匆忙，没有考虑游戏的可玩性，后期需要的开发工作可能较大，所以如果您有兴趣可以进行开发投稿或者加入开发组。</p>
      <p>联系方式：jokin@twocola.com</p>
      <hr />
      <p class="mb-0">期待您的加入！</p>
    </div>


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
