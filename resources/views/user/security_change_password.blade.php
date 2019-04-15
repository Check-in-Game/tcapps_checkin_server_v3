@extends('user/master')
@section('before_nav')
@endsection

@section('container')
  <!-- 公告-7 -->
  @foreach($_notices as $notice)
  <div class="alert alert-{{ $notice['color'] }}" role="alert">
    @if (!empty($notice['title']))
    <h4 class="alert-heading">{{ $notice['title'] }}</h4>
    @endif
    {{ $notice['content'] }}
  </div>
  @endforeach


  <h2>修改密码 / Change Password</h2>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">旧密码</span>
    </div>
    <input type="password" class="form-control" id="old_password">
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">新密码</span>
    </div>
    <input type="password" class="form-control" id="new_password">
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">确认密码</span>
    </div>
    <input type="password" class="form-control" id="comfirm_password">
  </div>

  <p class="clearfix">
    <button type="button" class="btn btn-danger float-right" onclick="javascript:modify();">修改</button>
  </p>

  <div class="alert alert-danger mt-4" role="alert">
    如果您需要申诉，请移步首页加入QQ群后寻找管理帮助！
  </div>
@endsection
@section('script')
<script src="{{ asset('js/base64.js') }}" charset="utf-8"></script>
<script type="text/javascript">
  function modify(){
    let b64 = new Base64();
    let old_password = $('#old_password').val();
    let new_password = $('#new_password').val();
    let comfirm_password = $('#comfirm_password').val();
    // 检查密码长度
    if (old_password.length < 8 || old_password.length > 16
      || new_password.length < 8 || new_password.length > 16
      || comfirm_password.length < 8 || comfirm_password.length > 16
    ){
      alert('密码长度要求至少8位，最多16位！');
      return false;
    }
    $('#btn').attr('disabled', 'disabled');
    let ajax = $.ajax({
      url: '/api/user/security/password',
      type: 'post',
      dataType: 'json',
      data: {
        'old_password': b64.encode(old_password),
        'new_password': b64.encode(new_password),
        'comfirm_password': b64.encode(comfirm_password)
      },
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        $('#btn').removeAttr('disabled');
        if (status === 'timeout') {
          alert('响应超时，请稍候再试！');
        }
      },
      success: function(data){
        if (data.errno === 0) {
          alert('修改成功！');
          location.href = '';
        }else{
          if (data.errno === 2702) {
            alert('（2702）用户状态错误！');
          }else if(data.errno === 2703) {
            alert('请检查您输入的两个新密码是否一致！');
          }else if(data.errno === 2704) {
            alert('原密码授权失败！');
          }else{
            alert('网络状态不佳，请稍候再试');
          }
        }
      }
    });
  }
</script>
@endsection
