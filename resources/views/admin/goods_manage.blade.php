@extends('admin/master')
@section('container')
<!-- 公告-14 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>管理商品 / Manage Goods</h2>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">商品GID</span>
  </div>
  <input type="number" class="form-control" placeholder="GID" id="gid">
  <div class="input-group-append">
    <button class="btn btn-outline-secondary" type="button" onclick="javascript:search();">查</button>
  </div>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">商品名称</span>
  </div>
  <input type="text" class="form-control" placeholder="Good's name" id="gname">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">商品售价</span>
  </div>
  <input type="number" class="form-control" placeholder="Good's cost" id="cost">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">开始时间</span>
  </div>
  <input type="text" class="form-control" placeholder="Start time" id="starttime" value="{{ date('Y-m-d H:i:s') }}">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">结束时间</span>
  </div>
  <input type="text" class="form-control" placeholder="End time" id="endtime" value="1970-01-01 00:00:00">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <label class="input-group-text" for="tid">商品类型</label>
  </div>
  <select class="custom-select" id="tid">
    <option value="1" selected>勋章</option>
  </select>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">展示类型编号</span>
  </div>
  <input type="number" class="form-control" placeholder="SID" id="sid" value='1'>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">总销售数量</span>
  </div>
  <input type="number" class="form-control" placeholder="All count" id="all_count" value="0">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">个人购买上限</span>
  </div>
  <input type="number" class="form-control" placeholder="Re-buy limitation" id="rebuy" value="1">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">商品描述</span>
  </div>
  <input type="text" class="form-control" placeholder="Description" id="description">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">展示链接</span>
  </div>
  <input type="text" class="form-control" placeholder="Image link" id="image">
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
  <button class="btn btn-success float-right" name="button" onclick="javascript:add();">增加</button>
  <button class="btn btn-secondary float-right mr-2" name="button" onclick="javascript:update();">修改</button>
  <button class="btn btn-danger float-left" name="button" onclick="javascript:del();">删除</button>
</p>
@endsection
@section('script')
<script type="text/javascript">
function search() {
  let gid       = $('#gid').val();
  if (gid === '') {
    m_alert('请填写GID！', 'warning');
    return false;
  }
  $.getJSON('/api/admin/goods/search/' + gid, function(data){
    if (data.errno === 0) {
      $('#gid').val(data.body.data.gid);
      $('#gname').val(data.body.data.gname);
      $('#cost').val(data.body.data.cost)
      $('#starttime').val(data.body.data.starttime)
      $('#endtime').val(data.body.data.endtime)
      $('#tid').val(data.body.data.tid)
      $('#sid').val(data.body.data.sid)
      $('#all_count').val(data.body.data.all_count)
      $('#rebuy').val(data.body.data.rebuy)
      $('#description').val(data.body.data.description)
      $('#image').val(data.body.data.image);
      $('#status').val(data.body.data.status);
    }else{
      m_alert(data.error, 'danger');
    }
  });
}
function add() {
  let gname       = $('#gname').val();
  let cost        = $('#cost').val();
  let starttime   = $('#starttime').val();
  let endtime     = $('#endtime').val();
  let tid         = $('#tid').val();
  let sid         = $('#sid').val();
  let rebuy       = $('#rebuy').val();
  let all_count   = $('#all_count').val();
  let description = $('#description').val();
  let image       = $('#image').val();
  let captcha     = $('#captcha').val();
  if (gname == '' || cost == '' || starttime == '' || endtime == '' || tid == '' || sid == '' || rebuy == '' || all_count == '' || description == '' || captcha == '') {
    m_alert('请填写信息！', 'warning');
    return false;
  }
  image = image == '' ? 'null' : image;
  m_loading();
  $.ajax({
    url: '/api/admin/goods/add',
    type: 'post',
    data: {
      'gname': gname,
      'cost': cost,
      'starttime': starttime,
      'endtime': endtime,
      'tid': tid,
      'sid': sid,
      'rebuy': rebuy,
      'all_count': all_count,
      'description': description,
      'image': image,
      'captcha': captcha
    },
    dataType: 'json',
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status == 'timeout') {
        m_alert('请求超时，请稍候再试！', 'danger');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        m_alert('增加成功！', 'success');
      }else{
        $('#captcha_img').click();
        $('#captcha').val('');
        m_alert(data.error, 'danger');
      }
    }
  });
}
function update() {
  let gid         = $('#gid').val();
  let gname       = $('#gname').val();
  let cost        = $('#cost').val();
  let starttime   = $('#starttime').val();
  let endtime     = $('#endtime').val();
  let tid         = $('#tid').val();
  let sid         = $('#sid').val();
  let rebuy       = $('#rebuy').val();
  let all_count   = $('#all_count').val();
  let description = $('#description').val();
  let image       = $('#image').val();
  let captcha     = $('#captcha').val();
  if (gid == '' || gname == '' || cost == '' || starttime == '' || endtime == '' || tid == '' || sid == '' || rebuy == '' || all_count == '' || description == '' || captcha == '') {
    m_alert('请填写信息！', 'warning');
    return false;
  }
  image = image == '' ? 'null' : image;
  m_loading();
  $.ajax({
    url: '/api/admin/goods/update',
    type: 'post',
    data: {
      'gid': gid,
      'gname': gname,
      'cost': cost,
      'starttime': starttime,
      'endtime': endtime,
      'tid': tid,
      'sid': sid,
      'rebuy': rebuy,
      'all_count': all_count,
      'description': description,
      'image': image,
      'captcha': captcha
    },
    dataType: 'json',
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status == 'timeout') {
        m_alert('请求超时，请稍候再试！', 'danger');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        m_alert('修改成功！', 'success');
      }else{
        $('#captcha_img').click();
        $('#captcha').val('');
        m_alert(data.error, 'danger');
      }
    }
  });
}
function del() {
  let gid       = $('#gid').val();
  if (gid == '') {
    m_alert('请填写GID！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/admin/goods/delete',
    type: 'post',
    data: {
      'gid': gid
    },
    dataType: 'json',
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status == 'timeout') {
        m_alert('请求超时，请稍候再试！', 'danger');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        $('#gid').val('');
        $('#gname').val('');
        $('#cost').val('')
        $('#tid').val('')
        $('#sid').val('')
        $('#all_count').val('')
        $('#rebuy').val('')
        $('#description').val('')
        $('#image').val('');
        $('#status').val(1);
        m_alert('删除成功！', 'success');
      }else{
        m_alert(data.error, 'danger');
      }
    }
  });
}
</script>
@endsection
