@extends('public.master')
@section('container')
<!-- 公告-4 -->
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
<!-- 登录 -->
<div class="mt-4 text-center">
  <img src="{{ $_system['cdn_prefix'] }}/cdn/common/icons/logo_256.png" alt="logo" width="100px">
</div>
<div class="mx-auto my-4 text-center" style="max-width:400px;">
  <h2>老用户数据迁移</h2>

  <div class="alert alert-danger text-left" role="alert">
    <h4 class="alert-heading">数据迁移公告</h4>
    <p>
      因数据安全升级，为保障账户数据安全，老用户需要填写下方所需信息进行数据迁移，原有的用户名需要按新标准重新注册，账户原有资产和资产部署情况不受影响。
    </p>
    <p>
      如果您的原用户名是以英文开头，大于等5位的长度，可以尝试在新用户名中填写原用户名进行数据迁移。
    </p>
    <p>
      请填写真实的邮箱，我们将发送激活链接至您的邮箱。
    </p>
    <p>
      <strong>
        数据迁移后，请使用新用户名进行登录。
      </strong>
    </p>
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">原用户名</span>
    </div>
    <input type="text" class="form-control" placeholder="Username" aria-label="Username" id="old_username">
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">原密码</span>
    </div>
    <input type="password" class="form-control" placeholder="Password" aria-label="Password" id="old_password">
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">新用户名</span>
    </div>
    <input type="text" class="form-control" placeholder="Username" aria-label="Username" id="username">
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">新密码</span>
    </div>
    <input type="password" class="form-control" placeholder="Password" aria-label="Password" id="password">
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">确认新密码</span>
    </div>
    <input type="password" class="form-control" placeholder="Password" aria-label="Password" id="confirm_password">
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">验证码</span>
    </div>
    <input type="text" class="form-control" placeholder="Captcha" id="captcha" maxlength="6">
    <div class="input-group-append">
      <img src="{{ captcha_src() }}" alt="captcha" onclick="this.src='{{ captcha_src() }}' + Math.random();" id="captcha_img">
    </div>
  </div>

  <p class="clearfix">
    <button class="btn btn-primary btn-block" id="btn" name="button" onclick="javascript:migrate();">资产转移</button>
    <button class="btn btn-secondary btn-block" id="btn" name="button" onclick="javascript:location.href='{{ action('PublicController@register') }}';">没有帐号？</button>
    <button class="btn btn-success btn-block" id="btn" name="button" onclick="javascript:location.href='{{ action('PublicController@login') }}';">新用户登录</button>
  </p>
</div>

@endsection
@section('script')
<script type="text/javascript">
  function migrate() {
    let old_username = $('#old_username').val();
    let old_password = $('#old_password').val();
    let username = $('#username').val();
    let password = $('#password').val();
    let confirm_password = $('#confirm_password').val();
    let captcha  = $('#captcha').val();
    // 用户名验证
    var match = new RegExp("^[a-zA-Z][a-zA-Z0-9_]{4,15}$");
    if(!match.test(username)){
      m_alert("新用户名必须由字母开头，5-16位，不可包含中文以及除下划线以外的特殊字符");
      $("#username").focus();
      return false;
    }
    // 密码验证
    match = new RegExp("^.{5,16}$");
    if(!match.test(password)){
      m_alert("新密码长度必须是6到16位");
      $("#password").focus();
      return false;
    }
    // 密码二次验证
    if (password != confirm_password) {
      m_alert('输入的两次新账户密码不一致', 'warning');
      return false;
    }
    if (old_password.length < 8 || old_password.length > 16) {
      m_alert('用户名或密码错误', 'warning');
      return false;
    }
    if (captcha.length < 4 || captcha.length > 6) {
      m_alert('验证码错误', 'warning');
      return false;
    }
    m_loading();
    $.ajax({
      url: '/api/login_old',
      type: 'post',
      data: {
        'old_username': old_username,
        'old_password': old_password,
        'username': username,
        'password': password,
        'captcha' : captcha
      },
      dataType: 'json',
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        m_loading(false);
        if (status == 'timeout') {
          alert('连接超时！');
        }
      },
      success: function(data){
        $('#captcha_img').click();
        $('#captcha').val('');
        if (data.errno == 0) {
          location.href = '/user';
        }else if(data.errno == 5701){
          m_alert('验证码错误', 'danger');
        }else if(data.errno == 5702){
          m_alert('原账户的用户名或密码错误', 'danger');
        }else if(data.errno == 5703){
          m_alert('新用户名必须由字母开头，5-16位，不可包含中文以及除下划线以外的特殊字符', 'danger');
        }else if(data.errno == 5704){
          m_alert('新密码长度必须是6到16位', 'danger');
        }else if(data.errno == 5705){
          m_alert('新账户用户名已被使用', 'danger');
        }else{
          m_alert('系统繁忙，请稍候再试', 'danger');
        }
      }
    });
  }
</script>
@endsection
