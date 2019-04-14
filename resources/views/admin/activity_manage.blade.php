@extends('admin/master')
@section('container')
<!-- 公告-13 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>管理活动 / Manage Activity</h2>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">活动AID</span>
  </div>
  <input type="number" class="form-control" placeholder="AID" id="aid">
  <div class="input-group-append">
    <button class="btn btn-outline-secondary" type="button" onclick="javascript:search();">查</button>
  </div>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">开始时间</span>
  </div>
  <input type="text" class="form-control" placeholder="Start time" id="starttime" value="{{ date('Y-m-d H:00:00') }}">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">结束时间</span>
  </div>
  <input type="text" class="form-control" placeholder="End time" id="endtime" value="{{ date('Y-m-d H:00:00', strtotime('+1 day')) }}">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">最小数量</span>
  </div>
  <input type="number" class="form-control" placeholder="Minimum" id="min" value='1'>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">最大数量</span>
  </div>
  <input type="number" class="form-control" placeholder="Maximum" id="max" value="10">
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

<div class="alert alert-primary" role="alert">
  <h4 class="alert-heading">使用须知</h4>
  增加活动不需要填写AID，可以通过活动ID(AID)查询该活动的详细信息，修改后再新增。
</div>

@endsection
@section('script')
<script type="text/javascript">
function search() {
  let aid       = $('#aid').val();
  if (aid == '') {
    alert('请填写AID！');
    return false;
  }
  $.getJSON('/api/admin/activity/search/' + aid, function(data){
    if (data.errno === 0) {
      $('#starttime').val(data.body.data.starttime);
      $('#endtime').val(data.body.data.endtime);
      $('#min').val(data.body.data.min_worth);
      $('#max').val(data.body.data.max_worth);
      $('#status').val(data.body.data.status);
    }else{
      $('#starttime').val('');
      $('#endtime').val('');
      $('#min').val('');
      $('#max').val('');
      $('#status').val('');
      alert(data.error);
    }
  });
}
function add() {
  let starttime = $('#starttime').val();
  let endtime   = $('#endtime').val();
  let min       = $('#min').val();
  let max       = $('#max').val();
  let status    = $('#status').val();
  if (starttime == '' || endtime == '' || min == '' || max == '' || status == '') {
    alert('请填写信息！');
    return false;
  }
  $.ajax({
    url: '/api/admin/activity/add',
    type: 'post',
    data: {
      'starttime': starttime,
      'endtime': endtime,
      'min_worth': min,
      'max_worth': max,
      'status': status,
    },
    dataType: 'json',
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      if (status == 'timeout') {
        alert('请求超时，请稍候再试！');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        $('#starttime').val('');
        $('#endtime').val('');
        $('#min').val('');
        $('#max').val('');
        $('#status').val(1);
        alert('增加完成！');
      }else{
        alert(data.error);
      }
    }
  });
}
function update() {
  let aid       = $('#aid').val();
  let starttime = $('#starttime').val();
  let endtime   = $('#endtime').val();
  let min       = $('#min').val();
  let max       = $('#max').val();
  let status    = $('#status').val();
  if (aid == '' || starttime == '' || endtime == '' || min == '' || max == '' || status == '') {
    alert('请填写信息！');
    return false;
  }
  $.ajax({
    url: '/api/admin/activity/update',
    type: 'post',
    data: {
      'aid': aid,
      'starttime': starttime,
      'endtime': endtime,
      'min_worth': min,
      'max_worth': max,
      'status': status,
    },
    dataType: 'json',
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      if (status == 'timeout') {
        alert('请求超时，请稍候再试！');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        alert('修改完成！');
      }else{
        alert(data.error);
      }
    }
  });
}
function del() {
  let aid       = $('#aid').val();
  if (aid == '') {
    alert('请填写AID！');
    return false;
  }
  $.ajax({
    url: '/api/admin/activity/delete',
    type: 'post',
    data: {
      'aid': aid
    },
    dataType: 'json',
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      if (status == 'timeout') {
        alert('请求超时，请稍候再试！');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        $('#starttime').val('');
        $('#endtime').val('');
        $('#min').val('');
        $('#max').val('');
        $('#status').val(1);
        alert('删除成功！');
      }else{
        alert(data.error);
      }
    }
  });
}
</script>
@endsection
