@extends('admin/master')
@section('container')
<!-- 公告-19 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>用户管理 / Manage Users</h2>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">UID</span>
  </div>
  <input type="number" class="form-control" placeholder="UID" id="uid">
  <div class="input-group-append">
    <button class="btn btn-outline-secondary" type="button" onclick="javascript:search();">查</button>
  </div>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">Username</span>
  </div>
  <input type="text" class="form-control" placeholder="Username" id="username">
  <div class="input-group-append">
    <button class="btn btn-outline-secondary" type="button" onclick="javascript:search_username();">查</button>
  </div>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">Password</span>
  </div>
  <input type="text" class="form-control" placeholder="Password" id="password">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">Status</span>
  </div>
  <input type="number" class="form-control" placeholder="Status" id="status" value=1>
</div>

<p class="clearfix">
  <button class="btn btn-secondary float-right mr-2" id="btn" name="button" onclick="javascript:update();">修改</button>
</p>

<hr>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">总积分</span>
  </div>
  <input type="number" class="form-control" id="points" readonly>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">剩余积分</span>
  </div>
  <input type="number" class="form-control" id="rest_points" readonly>
</div>

<p class="clearfix">
  <button class="btn btn-secondary float-right mr-2" name="button" onclick="javascript:getPoints();">查询</button>
</p>

<div class="alert alert-primary" role="alert">
  <h4 class="alert-heading">使用说明</h4>
  用户管理器无法查看用户密码但是可以修改用户密码，不需要修改时，留空即可。
</div>
@endsection
@section('script')
<script type="text/javascript">
function search() {
  let uid = $('#uid').val();
  if (uid == '') {
    m_alert('请输入UID后查询', 'warning');
    return false;
  }
  $.getJSON('/api/admin/users/search/' + uid, function(data){
    if (data.errno === 0) {
      let uid         = data.body.data.uid;
      let username    = data.body.data.username;
      let status      = data.body.data.status;
      $('#uid').val(uid);
      $('#username').val(username);
      $('#password').val('');
      $('#status').val(status);
    }else{
      $('#uid').val('');
      $('#username').val('');
      $('#password').val('');
      $('#status').val('');
      m_alert(data.error, 'danger');
    }
  });
}

function search_username() {
  let username = $('#username').val();
  if (username == '') {
    m_alert('请输入用户名后查询', 'warning');
    return false;
  }
  $.getJSON('/api/admin/users/search_username/' + username, function(data){
    if (data.errno === 0) {
      let uid         = data.body.data.uid;
      let username    = data.body.data.username;
      let status      = data.body.data.status;
      $('#uid').val(uid);
      $('#username').val(username);
      $('#password').val('');
      $('#status').val(status);
    }else{
      $('#uid').val('');
      $('#username').val('');
      $('#password').val('');
      $('#status').val('');
      m_alert(data.error, 'danger');
    }
  });
}

function getPoints() {
  let uid = $('#uid').val();
  if (uid == '') {
    m_alert('请输入UID后查询', 'warning');
    return false;
  }
  $.getJSON('/api/admin/users/points/' + uid, function(data){
    if (data.errno === 0) {
      let points           = data.body.data.allWorth;
      let rest_points      = data.body.data.allWorth - data.body.data.used;
      $('#points').val(points);
      $('#rest_points').val(rest_points);
    }else{
      $('#points').val('');
      $('#rest_points').val('');
      m_alert(data.error, 'danger');
    }
  });
}

function update() {
  let uid       = $('#uid').val();
  let username  = $('#username').val();
  let password  = $('#password').val();
  let status    = $('#status').val();
  if (uid == '' || username == '' || status == '') {
    m_alert('请填写信息！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/admin/users/update',
    type: 'post',
    data: {
      'uid': uid,
      'username': username,
      'password': password,
      'status': status,
    },
    dataType: 'json',
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status == 'timeout') {
        m_alert('请求超时，请稍候再试！', 'warning');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        $('#uid').val('');
        $('#username').val('');
        $('#password').val('');
        $('#status').val('');
        m_alert('修改完成！', 'success')
      }else{
        m_alert(data.error, 'danger');
      }
    }
  });
}
</script>
@endsection
