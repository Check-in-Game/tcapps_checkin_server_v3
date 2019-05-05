@extends('admin/master')
@section('container')
<!-- 公告-24 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>管理效果 / Manage Effects</h2>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">效果EID</span>
  </div>
  <input type="number" class="form-control" placeholder="EID" id="eid">
  <div class="input-group-append">
    <button class="btn btn-outline-secondary" type="button" onclick="javascript:search();">查</button>
  </div>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">倍率</span>
  </div>
  <input type="text" class="form-control" placeholder="Times" id="times">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">效果描述</span>
  </div>
  <input type="text" class="form-control" placeholder="Description" id="description">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">Status</span>
  </div>
  <input type="number" class="form-control" placeholder="Status" id="status" value=1>
</div>

<p class="clearfix">
  <button class="btn btn-success float-right" id="btn" name="button" onclick="javascript:add();">增加</button>
  <button class="btn btn-secondary float-right mr-2" id="btn" name="button" onclick="javascript:update();">修改</button>
  <button class="btn btn-danger float-left" id="btn" name="button" onclick="javascript:del();">删除</button>
</p>

@endsection
@section('script')
<script type="text/javascript">
function search() {
  let eid       = $('#eid').val();
  if (eid === '') {
    m_alert('请填写EID！', 'warning');
    return false;
  }
  $.getJSON('/api/admin/effects/search/' + eid, function(data){
    if (data.errno === 0) {
      $('#eid').val(data.body.data.eid);
      $('#times').val(data.body.data.times);
      $('#description').val(data.body.data.description);
      $('#status').val(data.body.data.status);
    }else{
      m_alert(data.error, 'danger');
    }
  });
}
function add() {
  let times         = $('#times').val();
  let description   = $('#description').val();
  let status        = $('#status').val();
  if (times == '' || description == '' || status == '') {
    m_alert('请填写信息！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/admin/effects/add',
    type: 'post',
    data: {
      'times': times,
      'description': description,
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
        $('#eid').val('');
        $('#times').val('');
        $('#description').val('');
        $('#status').val(1);
        m_alert('增加完成！', 'success');
      }else{
        m_alert(data.error, 'danger');
      }
    }
  });
}
function update() {
  let eid           = $('#eid').val();
  let times         = $('#times').val();
  let description   = $('#description').val();
  let status        = $('#status').val();
  if (eid == '' || times == '' || description == '' || status == '') {
    m_alert('请填写信息！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/admin/effects/update',
    type: 'post',
    data: {
      'eid': eid,
      'times': times,
      'description': description,
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
        m_alert('修改完成！', 'success');
      }else{
        m_alert(data.error, 'danger');
      }
    }
  });
}
function del() {
  let eid       = $('#eid').val();
  if (eid == '') {
    m_alert('请填写EID！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/admin/effects/delete',
    type: 'post',
    data: {
      'eid': eid
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
        $('#eid').val('');
        $('#times').val('');
        $('#description').val('');
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
