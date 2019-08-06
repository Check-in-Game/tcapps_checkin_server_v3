@extends('user/master')
@section('header')
验证与绑定电子邮件
@endsection
@section('body')

  <div class="alert alert-primary mt-4" role="alert">
    <h4 class="alert-heading">邮箱绑定说明</h4>
    为保障您的账户安全与防止恶意注册，我们需要验证您的电子邮箱地址，账户未绑定电子邮件前将无法进行任何操作。
    <br>
    每个电子邮箱地址只能绑定一个账户，您的电子邮箱地址也将是您找回账户的唯一凭证。
    <br>
    当系统检测到您的账户有异常操作时，可能会强制要求您再次验证您的邮箱地址。
    <br>
    如果您的电子邮件地址被他人绑定，可以发送申诉邮件至checkin-service@twocola.com。
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">电子邮件地址</span>
    </div>
    <input type="email" class="form-control" id="email">
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
    <button class="btn btn-primary float-right" id="btn-send" onclick="verify_email();">发送验证邮件</button>
  </p>

@endsection
@section('script')
<script type="text/javascript">
  function verify_email(){
    let email = $('#email').val();
    let captcha  = $('#captcha').val();
    if (captcha.length < 4 || captcha.length > 6) {
      m_alert('验证码错误', 'warning');
      return false;
    }
    match = new RegExp("^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$");
    if(!match.test(email)){
      m_alert("您的邮箱格式有误，请检查您的邮箱是否输入错误（试试全部小写）。", 'warning');
      return false;
    }
    m_loading();
    $.ajax({
      url: '/api/user/security/email',
      type: 'post',
      dataType: 'json',
      data: {
        'email': email,
        'captcha' : captcha
      },
      timeout: 60000,
      complete: function(XMLHttpRequest, status){
        m_loading(false);
        if (status === 'timeout') {
          m_alert('响应超时，请稍候再试！', 'warning');
        }
      },
      success: function(data){
        if (data.errno == 0) {
          m_alert('发送验证邮件成功，请及时前往邮箱激活账户！', 'success');
        }else{
          $('#captcha_img').click();
          $('#captcha').val('');
          if (data.errno == 5801) {
            m_alert('验证码错误', 'danger');
          }else if(data.errno == 5802) {
            m_alert('错误的邮箱地址', 'danger');
          }else if(data.errno == 5803) {
            m_alert('邮箱已被占用', 'danger');
          }else if(data.errno == 5804) {
            m_alert('账户状态异常，请刷新后再试', 'danger');
          }else if(data.errno == 5805) {
            m_alert('请求频繁，请一分钟后再试', 'danger');
          }else{
            m_alert('网络状态不佳，请稍候再试', 'danger');
          }
        }
      }
    });
  }
</script>
@endsection
