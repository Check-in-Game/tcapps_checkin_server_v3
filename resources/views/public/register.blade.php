@extends('public.master')
@section('container')
<!-- 公告-2 -->
@foreach($_notices as $notice)
<div class="container">
  <div class="alert alert-{{ $notice['color'] }}" role="alert">
    @if (!empty($notice['title']))
    <h4 class="alert-heading">{{ $notice['title'] }}</h4>
    @endif
    {{ $notice['content'] }}
  </div>
</div>
@endforeach

@if(isset($reg_status) && $reg_status===false)
<div class="container">
  <div class="alert alert-danger mt-4" role="alert">
    {{ $reg_error }}
  </div>
</div>
@endif

<!-- 登录 -->
<div class="mt-4 text-center">
  <img src="https://checkin-static.twocola.com/cdn/common/icons/logo_256.png" alt="logo" width="100px">
</div>
<div class="mx-auto my-4 text-center" style="max-width:400px;">
  <h2>注册 / Register</h2>
  <form class="" action="{{ action('PublicController@register') }}" method="post" id="form">
    @csrf
    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">用户名</span>
      </div>
      <input type="text" class="form-control" placeholder="Username" aria-label="Username" name="username" id="username">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">密码</span>
      </div>
      <input type="password" class="form-control" placeholder="Password" aria-label="Password" name="password" id="password">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">确认密码</span>
      </div>
      <input type="password" class="form-control" placeholder="Password" name="comfirm" id="comfirm">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">验证码</span>
      </div>
      <input type="text" class="form-control" placeholder="Captcha" name="captcha" id="captcha" maxlength="6">
      <div class="input-group-append">
        <img src="{{ captcha_src() }}" alt="captcha" onclick="this.src='{{ captcha_src() }}' + Math.random();" id="captcha_img">
      </div>
    </div>

    <p class="clearfix">
      <input class="btn btn-primary btn-block" type="button" value="立刻注册" onclick="javascript:register();">
      <button class="btn btn-secondary btn-block" id="btn" name="button" onclick="javascript:location.href='{{ action('PublicController@login') }}';">有帐号？</button>
    </p>
  </form>
</div>
@endsection
@section('script')
<script type="text/javascript">
  function register($reg) {
    let form = $('#form');
    let username = $('#username').val();
    let password = $('#password').val();
    let comfirm = $('#comfirm').val();
    let captcha = $('#captcha').val();
    if (username.length < 5 || username.length > 16) {
      m_alert('用户名长度需要大于5小于16。', 'warning');
      return false;
    }
    if (password.length < 8 || password.length > 16) {
      m_alert('密码长度需要大于8小于16。', 'warning');
      return false;
    }
    if (captcha.length < 4 || captcha.length > 6) {
      m_alert('验证码错误', 'warning');
      return false;
    }
    if (password != comfirm) {
      m_alert('两次密码不一致。', 'warning');
      return false;
    }
    form.submit();
  }
</script>
@endsection
