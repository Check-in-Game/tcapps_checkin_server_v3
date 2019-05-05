@extends('admin/master')
@section('container')
<!-- 公告-17 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>公告管理 / Manage Notices</h2>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">公告NID</span>
  </div>
  <input type="number" class="form-control" placeholder="NID" id="nid">
  <div class="input-group-append">
    <button class="btn btn-outline-secondary" type="button" onclick="javascript:search();">查</button>
  </div>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">PlaceID</span>
  </div>
  <input type="number" class="form-control" placeholder="PlaceID" id="place_id">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">标题</span>
  </div>
  <input type="text" class="form-control" placeholder="标题" id="title">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">内容</span>
  </div>
  <textarea class="form-control" rows=3 aria-label="content" id="content"></textarea>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <label class="input-group-text" for="color">颜色Class</label>
  </div>
  <select class="custom-select" id="color">
    <option selected>选择颜色Class</option>
    <option value="primary" class="bg-primary text-white">primary</option>
    <option value="secondary" class="bg-secondary text-white">secondary</option>
    <option value="success" class="bg-success text-white">success</option>
    <option value="danger" class="bg-danger text-white">danger</option>
    <option value="warning" class="bg-warning text-dark">warning</option>
    <option value="info" class="bg-info text-white">info</option>
    <option value="light" class="bg-light text-muted">light</option>
    <option value="dark" class="bg-dark text-white">dark</option>
  </select>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">Priority</span>
  </div>
  <input type="number" class="form-control" placeholder="Priority" id="priority" value=1>
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
    <span class="input-group-text">Status</span>
  </div>
  <input type="number" class="form-control" placeholder="Status" id="status" value=1>
</div>

<p class="clearfix">
  <button class="btn btn-danger float-left" id="btn" name="button" onclick="javascript:del();">删除</button>
  <button class="btn btn-success float-right" id="btn" name="button" onclick="javascript:add();">增加</button>
  <button class="btn btn-secondary float-right mr-2" id="btn" name="button" onclick="javascript:update();">修改</button>
</p>
@endsection
@section('script')
<script type="text/javascript">
function search() {
  let nid = $('#nid').val();
  if (nid == '') {
    m_alert('请输入NID后查询', 'warning');
    return false;
  }
  $.getJSON('/api/admin/notices/search/' + nid, function(data){
    if (data.errno === 0) {
      let place_id  = data.body.data.place_id;
      let title     = data.body.data.title;
      let content   = data.body.data.content;
      let color     = data.body.data.color;
      let priority  = data.body.data.priority;
      let starttime = data.body.data.starttime;
      let endtime   = data.body.data.endtime;
      let status    = data.body.data.status;
      $('#place_id').val(place_id);
      $('#title').val(title);
      $('#content').val(content);
      $('#color').val(color);
      $('#priority').val(priority);
      $('#starttime').val(starttime);
      $('#endtime').val(endtime);
      $('#status').val(status);
    }else{
      $('#place_id').val('');
      $('#title').val('');
      $('#content').val('');
      $('#color').val('');
      $('#priority').val('');
      $('#starttime').val('');
      $('#endtime').val('');
      $('#status').val('');
      m_alert(data.error, 'danger');
    }
  });
}
function add() {
  let place_id  = $('#place_id').val();
  let title     = $('#title').val();
  let content   = $('#content').val();
  let color     = $('#color').val();
  let priority  = $('#priority').val();
  let starttime = $('#starttime').val();
  let endtime   = $('#endtime').val();
  let status    = $('#status').val();
  if (place_id == '' || title == '' || content == '' || color == '选择颜色Class' || priority == '' || starttime == '' || endtime == '' || status == '') {
    m_alert('请填写信息！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/admin/notices/add',
    type: 'post',
    data: {
      'place_id': place_id,
      'title': title,
      'content': content,
      'color': color,
      'priority': priority,
      'starttime': starttime,
      'endtime': endtime,
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
        $('#place_id').val('');
        $('#title').val('');
        $('#content').val('');
        $('#color').val('');
        $('#priority').val('');
        $('#starttime').val('');
        $('#endtime').val('');
        $('#status').val('');
        m_alert('添加完成！', 'success')
      }else{
        m_alert(data.error, 'danger');
      }
    }
  });
}
function update() {
  let nid       = $('#nid').val();
  let place_id  = $('#place_id').val();
  let title     = $('#title').val();
  let content   = $('#content').val();
  let color     = $('#color').val();
  let priority  = $('#priority').val();
  let starttime = $('#starttime').val();
  let endtime   = $('#endtime').val();
  let status    = $('#status').val();
  if (nid == '' || place_id == '' || title == '' || content == '' || color == '选择颜色Class' || priority == '' || starttime == '' || endtime == '' || status == '') {
    m_alert('请填写信息！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/admin/notices/update',
    type: 'post',
    data: {
      'nid': nid,
      'place_id': place_id,
      'title': title,
      'content': content,
      'color': color,
      'priority': priority,
      'starttime': starttime,
      'endtime': endtime,
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
        m_alert('修改完成！', 'success')
      }else{
        m_alert(data.error, 'danger');
      }
    }
  });
}
function del() {
  let nid       = $('#nid').val();
  if (nid == '') {
    m_alert('请填写信息！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/admin/notices/delete',
    type: 'post',
    data: {
      'nid': nid,
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
        $('#nid').val('');
        $('#place_id').val('');
        $('#title').val('');
        $('#content').val('');
        $('#color').val('');
        $('#priority').val('');
        $('#starttime').val('');
        $('#endtime').val('');
        $('#status').val('');
        m_alert('成功删除！', 'success')
      }else{
        m_alert(data.error, 'danger');
      }
    }
  });
}
</script>
@endsection
