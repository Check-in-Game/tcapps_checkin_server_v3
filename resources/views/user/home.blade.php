@extends('user/master')
@section('before_nav')
@endsection

@section('container')
<!-- 公告-5 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

@if($count <= 200)
<div class="alert alert-success">
  您当前处于新手模式，前200次签到时若积分小于100分有{{ $buff }}倍新手加成。
</div>
@endif

<div class="row">

  <!-- 基本信息 -->
  <div class="col-md-4 col-sm-12 mb-3 text-center">
    <div class="card border-dark mb-3">
      <div class="card-header">
        基本信息
      </div>
      <div class="card-body text-dark">
        <p class="card-text">UID： <span class="badge badge-primary">{{ $uid }}</span></p>
        <p class="card-text">签到积分： <span class="badge badge-primary">{{ $all_worth }}</span></p>
        <p class="card-text">可用积分： <span class="badge badge-primary">{{ $all_worth - $cost }}</span></p>
      </div>
    </div>
  </div>

  <!-- 获取积分 -->
  <div class="col-md-4 col-sm-12 mb-3 text-center">
    <div class="card border-dark mb-3">
      <div class="card-header">
        获取积分
      </div>
      <div class="card-body text-dark">
        <a class="btn btn-primary btn-block" href="{{ action('PublicController@webCheckin') }}" target="_blank">在线签到</a>
        <a class="btn btn-success btn-block" href="{{ action('UserController@badges') }}" target="_self">佩戴勋章</a>
      </div>
    </div>
  </div>

  <!-- 账户安全 -->
  <div class="col-md-4 col-sm-12 mb-3 text-center">
    <div class="card border-dark mb-3">
      <div class="card-header">
        账户安全
      </div>
      <div class="card-body text-dark">
        <a class="btn btn-danger btn-block" href="{{ action('UserController@security_change_password') }}">修改密码</a>
      </div>
    </div>
  </div>

</div>

<div class="alert alert-success" role="alert">
  <h4 class="alert-heading">更快的获取积分</h4>
  <p>
    您可以在<a href="{{ action('PublicController@index') }}" target="_self">首页</a>加入QQ交流群获取一手活动预告信息，您也可以在用户中心的活动中心查询活动一览以更好的把握时机。
  </p>
  <p>
    如果您在使用过程中发现Bug或由其他的意见和建议，加入QQ群向群主反映（Bug请私聊），会有丰厚的积分奖励。
  </p>
  <p>
    QQ群会不定时发放礼包，礼包内容包含一系列增益道具、直接积分奖励等。
  </p>
  <p>
    游戏是在不断更新的，更多玩法正在紧锣密鼓的布置中，为了更好的游戏体验，欢迎加入QQ群共同讨论、建设、开发。
  </p>
  <hr>
  <p class="mb-0">兑换中心将于近日上线。</p>
</div>
@endsection
