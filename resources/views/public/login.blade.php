@extends('public.master')
@section('headerExtraContent')
  <!-- 幕布 -->
  <div class="jumbotron">
      <div class="container">
        <h1 class="display-4">Check-in Game 登录</h1>
        <p class="lead">签到排行榜实时更新，签到每隔5分钟即可进行一次，只需简单注册账户即可开始游戏！</p>
        <p class="lead">
          <a class="btn btn-primary" href="./index.php" target="_self" role="button">首页</a>
          <a class="btn btn-success" href="./register.html" target="_blank" role="button">注册账户</a>
        </p>
      </div>
    </div>
  <div class="container">
@endsection
@section('container')
  <div class="alert alert-success" role="alert">
    <h4 class="alert-heading">登录</h4>
    <p>登录后将开放更多有趣、便捷的功能。</p>
  </div>

  <!-- 登录 -->
  <h2>登录 / Login</h2>
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
    <button class="btn btn-success float-right" id="btn" name="button" onclick="javascript:login();">登录</button>
  </p>

  <div class="alert alert-warning" role="alert">
    <h4 class="alert-heading">安全提示</h4>
    <p>管理员不会向您索要您帐号的密码，请勿将密码透露给任何人！</p>
  </div>

@endsection
<script src="{{ asset('js/base64.js') }}" charset="utf-8"></script>
<script type="text/javascript">
  function login() {
    let username = $('#username').val();
    let password = $('#password').val();
    let b64password = new Base64().encode(password);
    if (username.length < 5 || username.length > 16) {
      alert('用户名或密码错误');
      return false;
    }
    if (password.length < 8 || password.length > 16) {
      alert('用户名或密码错误');
      return false;
    }
    $.getJSON('/api/login/' + username + '/' + b64password, function(data){
      if (data.errno === 0) {
        location.href = '/user';
      }else{
        alert('登录失败，请检查您的用户名密码是否填写错误！')
      }
    });
  }
</script>
