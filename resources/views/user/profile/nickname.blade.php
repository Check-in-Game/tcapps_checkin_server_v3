@extends('user/master')
@section('header')
修改昵称
@endsection
@section('body')

  <div class="alert alert-primary mt-4" role="alert">
    <h4 class="alert-heading">昵称修改说明</h4>
    1、昵称无法在账户信息（如密码、邮箱等）修改后1天内进行修改。
    <br>
    2、昵称信息1天只能修改1次。
    <br>
    3、昵称不可作为您的登录方式。
    <br>
    <strong>
      4、不可使用与国家领导人、系统、管理员、GM等相关词语，一经发现，立刻封号。
    </strong>
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">昵称</span>
    </div>
    <input type="text" class="form-control" id="nickname" max="16" value="{{ $user->nickname }}">
  </div>

  <p class="clearfix">
    <button class="btn btn-primary float-right" onclick="change_nickname();">确认修改</button>
  </p>

@endsection
@section('script')
<script type="text/javascript">
  function change_nickname(){
    let nickname = $('#nickname').val();
    m_loading();
    $.ajax({
      url: '/api/user/profile/nickname',
      type: 'post',
      dataType: 'json',
      data: {
        'nickname': nickname
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
          m_alert('修改成功！', 'success');
        }else{
          if (data.errno == 6301) {
            m_alert('该用户名已经存在', 'danger');
          }else if(data.errno == 6302) {
            m_alert('无法在一天内频繁修改账户信息', 'danger');
          }else{
            m_alert('系统繁忙，请稍候再试', 'danger');
          }
        }
      }
    });
  }
</script>
@endsection
