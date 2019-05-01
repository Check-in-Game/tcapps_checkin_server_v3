@extends('user/master')
@section('before_nav')
@endsection

@section('container')
  <!-- 公告-26 -->
  @foreach($_notices as $notice)
  <div class="alert alert-{{ $notice['color'] }}" role="alert">
    @if (!empty($notice['title']))
    <h4 class="alert-heading">{{ $notice['title'] }}</h4>
    @endif
    {{ $notice['content'] }}
  </div>
  @endforeach

  <div class="alert alert-danger" role="alert">
    <h4 class="alert-heading">修改用户名需知</h4>
    您需要使用<strong>新的用户名</strong>参与登录、签到等活动，其他数据不会被影响。
    <br />
    如出现忘记新用户名等情况，UID和您的密码是唯一的找回用户名的方式。
    <br />
    <strong>用户名只能修改1次，请谨慎修改。用户名只能是字母、数字与下划线的组合。</strong>
  </div>

  @if( $_user->status === 0 )
  <h2>修改用户名 / Modify Username</h2>
  
  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">UID</span>
    </div>
    <input type="number" class="form-control" id="uid" value="{{ $_user->uid }}" readonly>
  </div>

  <div class="input-group mb-3">
    <div class="input-group-prepend">
      <span class="input-group-text">新用户名</span>
    </div>
    <input type="text" class="form-control" id="username">
  </div>
  <p class="clearfix">
    <button type="button" class="btn btn-danger float-right" onclick="javascript:modify();" id="btn">修改</button>
  </p>
  @else
  <div class="alert alert-success">
    您的账户不需要修改用户名。
  </div>
  @endif


  <div class="alert alert-warning mt-4" role="alert">
    如果您需要额外的帮助，请移步首页加入QQ群后寻找管理帮助！
  </div>
@endsection
@section('script')
<script type="text/javascript">
  function modify(){
    let username = $('#username').val();
    // 检查密码长度
    if (username.length < 5 || username.length > 16 ){
      alert('用户名长度要求至少5位，最多16位！');
      return false;
    }
    $('#btn').attr('disabled', 'disabled');
    let ajax = $.ajax({
      url: '/api/user/security/username',
      type: 'post',
      dataType: 'json',
      data: {
        'username': username
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
          location.href = '/user';
        }else{
          if (data.errno === 3501) {
            alert('不合法的用户名！');
          }else if(data.errno === 3502) {
            alert('用户状态异常，请联系管理员！');
          }else if(data.errno === 3503) {
            alert('您的用户状态正常，无需修改用户名！');
          }else{
            alert('网络状态不佳，请稍候再试');
          }
        }
      }
    });
  }
</script>
@endsection
