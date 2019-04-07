@extends('admin/master')
@section('container')
<h2>创建活动 / Create Activity</h2>
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
  <input type="text" class="form-control" placeholder="End time" id="endtime" value="{{ date('Y-m-d H:i:s') }}">
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
  <input type="number" class="form-control" placeholder="Maximum" id="max" value="2">
</div>

<p class="clearfix">
  <button class="btn btn-success float-right" id="btn" name="button" onclick="javascript:add();">增加</button>
</p>
@endsection
<script type="text/javascript">
function add() {
  let starttime = $('#starttime').val();
  let endtime   = $('#endtime').val();
  let min       = $('#min').val();
  let max       = $('#max').val();
  if (starttime == '' || endtime == '' || min == '' || max == '') {
    alert('请填写信息！');
  }
  $('#btn').attr('disabled', 'disabled');
  $.getJSON('/api/admin/activity/add/' + starttime + '/' + endtime + '/' + min + '/' + max, function(data){
    if (data.errno === 0) {
      alert('增加成功！');
      $('#btn').removeAttr('disabled');
    }else{
      alert(data.body.msg);
      $('#btn').removeAttr('disabled');
    }
  });
}
</script>
