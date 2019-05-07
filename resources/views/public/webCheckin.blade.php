@extends('public.master')
@section('headerExtraContent')
  <!-- 幕布 -->
  <div class="jumbotron pb-2">
      <div class="container">
        <h1 class="display-4">Check-in Game 在线签到器</h1>
        <p class="lead">签到排行榜实时更新，签到每隔10分钟即可进行一次，只需简单注册账户即可开始游戏！</p>
        <hr class="my-4">
        <p class="lead">
          <a class="btn btn-primary" href="{{ action('PublicController@index') }}">首页</a>
          <a class="btn btn-success" href="{{ action('PublicController@register') }}">注册账户</a>
          <a class="btn btn-info" href="https://jq.qq.com/?_wv=1027&k=5ax4j23" target="_blank" role="button">加入交流QQ群：887304185</a>
        </p>
      </div>
    </div>
  <div class="container">
@endsection
@section('container')

  <!-- 公告-3 -->
  @foreach($_notices as $notice)
  <div class="alert alert-{{ $notice['color'] }}" role="alert">
    @if (!empty($notice['title']))
    <h4 class="alert-heading">{{ $notice['title'] }}</h4>
    @endif
    {{ $notice['content'] }}
  </div>
  @endforeach

  <div class="alert alert-warning" role="alert">
    <p class="mb-0">出现用户状态不正常时，请登录用户中心查看是否有解封引导，若没有解封引导，请加群联系管理员。</p>
  </div>

  <!-- 排行榜 -->
  <h2>签到器</h2>
  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">用户名</span>
    </div>
    <input type="text" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" id="username">
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">密码</span>
    </div>
    <input type="password" class="form-control" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1" id="password">
  </div>

  <p class="clearfix">
    <span class="text-danger float-left" id="status">就绪</span>
    <span class="text-info float-left" id="time">0</span>
    <button class="btn btn-success float-right" id="btn" name="button" onclick="javascript:checkin();">开始签到</button>
  </p>

  <hr />

  <div class="alert alert-success" role="alert">
    <h4 class="alert-heading">签到技巧</h4>
    <p>这款游戏会根据开发者的心情开放活动，经常关注官网能尽早的获取活动预告以提前准备。</p>
    <p>签到运行时，请确保网络环境稳定，签到程序在签到失败后会再重试3次，全部失败后程序将不再重试，需要玩家手动再次点击启动。</p>
    <p><strong>加入官方QQ交流群</strong>充分了解游戏运行机制会帮助您更快的获得签到次数。</p>
    <hr />
    <p class="mb-0">最后，祝您游戏愉快！</p>
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
@section('script')
<script src="{{ asset('js/base64.js') }}" charset="utf-8"></script>
<script type="text/javascript">
  let thread;
  let counter;
  let token;
  let base64 = new Base64;
  function checkin(){
    let username = $('#username').val();
    let password = $('#password').val();
    $('#username').attr('disabled', 'disabled');
    $('#password').attr('disabled', 'disabled');
    let btn = $('#btn');
    if (btn.text() === '开始签到'){
      btn.removeClass('btn-success');
      btn.addClass('btn-danger');
      btn.text('停止签到');
      counter = 0;
      thread = setInterval(function(){
        time(counter);
        if (counter == 0){
          counter = 10 * 60 + 1;
          // 获取Token
          let url = './api/getToken/' + username + '/' + base64.encode(password);
          console.log(url);
          $.getJSON(url, function(data){
            console.log(data);
            if (data.errno == 0){
              token = data.body.token;
              status('获取Token成功！');
              check_in(username, token);
            }else{
              status('获取Token失败，签到停止！');
              clearInterval(thread);
            }
          });
        }else{
          counter --;
        }
      }, 1000);
      return true;
    }
    if (btn.text() === '停止签到'){
      $('#username').removeAttr('disabled');
      $('#password').removeAttr('disabled');
      btn.removeClass('btn-danger');
      btn.addClass('btn-success');
      btn.text('开始签到');
      clearInterval(thread);
      counter = 0;
      time(counter);
      status('就绪');
      return true;
    }
  }
  function check_in(username, token){
    let url = './api/checkIn/' + username + '/' + token;
    $.getJSON(url, function(data){
      if (data.errno == 0){
        status('签到成功！获得' + data.body.worth + '积分！');
      }else if(data.errno == 2203){
        status('签到失败，用户状态不正常！');
      }else{
        status('签到失败，延迟10分钟！');
      }
    });
  }
  function status(text) {
    console.log(text);
    $('#status').text(text);
  }
  function time(text) {
    $('#time').text(text);
  }
</script>
@endsection
