@extends('public.master')
@section('headerExtraContent')
  <!-- 幕布 -->
  <div class="jumbotron">
      <div class="container">
        <h1 class="display-4">Check-in Game 在线签到器</h1>
        <p class="lead">签到排行榜实时更新，签到每隔5分钟即可进行一次，只需简单注册账户即可开始游戏！</p>
        <p class="lead">
          <a class="btn btn-primary" href="{{ action('PublicController@index') }}">首页</a>
          <a class="btn btn-success" href="{{ action('PublicController@register') }}">注册账户</a>
        </p>
      </div>
    </div>
  <div class="container">
@endsection
@section('container')
<div class="alert alert-success" role="alert">
    <h4 class="alert-heading">签到器说明</h4>
    <p>使用签到器签到请将您的浏览器加入后台常驻白名单，否则被进程杀死后将不能进行签到。</p>
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

  <div class="alert alert-dark" role="alert">
    <h4 class="alert-heading">签到技巧</h4>
    <p>这款游戏会根据开发者的心情开放活动，经常关注官网能尽早的获取活动预告以提前准备。</p>
    <p>签到运行时，请确保网络环境稳定，签到程序在签到失败后会再重试3次，全部失败后程序将不再重试，需要玩家手动再次点击启动。</p>
    <p>充分了解游戏运行机制会帮助您更快的获得签到次数。</p>
    <hr />
    <p class="mb-0">最后，祝您游戏愉快！</p>
  </div>

  <div class="alert alert-warning" role="alert">
    <h4 class="alert-heading">提交建议 / 加入开发</h4>
    <p>这款游戏是开发者Jokin在闲暇时间开发的，因为上线匆忙，没有考虑游戏的可玩性，后期需要的开发工作可能较大，所以如果您有兴趣可以进行开发投稿或者加入开发组。</p>
    <p>联系方式：jokin@twocola.com</p>
    <hr />
    <p class="mb-0">期待您的来信！</p>
  </div>
@endsection
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
          counter = 5 * 60 + 1;
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
        status('签到成功！');
      }else{
        status('签到失败，延迟5分钟！');
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
