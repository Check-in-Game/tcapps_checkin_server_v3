@extends('public.master')
@section('container')

  <!-- 公告-2 -->
  @foreach($_notices as $notice)
  <div class="alert alert-{{ $notice['color'] }}" role="alert">
    @if (!empty($notice['title']))
    <h4 class="alert-heading">{{ $notice['title'] }}</h4>
    @endif
    {{ $notice['content'] }}
  </div>
  @endforeach

  <div class="alert alert-success mt-4" role="alert">
    中文名称不能直接注册，可以使用签到累计的积分向管理员提交改名申请。
  </div>

  @if(isset($reg_status) && $reg_status===false)
  <div class="alert alert-danger mt-4" role="alert">
    {{ $reg_error }}
  </div>
  @endif

  <h1>注册用户</h1>

  <form class="" action="{{ action('PublicController@register') }}" method="post" id="form">
    @csrf
    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">用户名</span>
      </div>
      <input type="text" class="form-control" placeholder="Username" name="username" id="username">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">密码</span>
      </div>
      <input type="password" class="form-control" placeholder="Password" name="password" id="password">
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

    <p>
      <input class="btn btn-primary float-right" type="button" value="马上注册" onclick="javascript:register();">
    </p>
  </form>
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
