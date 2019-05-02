@extends('admin/master')
@section('container')
<!-- 公告-12 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>增加积分 / Compensate</h2>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">UID</span>
  </div>
  <input type="text" class="form-control" placeholder="UID" aria-label="UID" aria-describedby="basic-addon1" id="uid">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">增加数量</span>
  </div>
  <input type="number" class="form-control" placeholder="Count" aria-label="Count" aria-describedby="basic-addon1" id="count">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <label class="input-group-text" for="tid">增加类型</label>
  </div>
  <select class="custom-select" id="tid">
    <option value="1" title="参与活动获得加值">活动加值</option>
    <option value="2" title="参与系统回馈获得加值">系统加值</option>
    <option value="3" title="系统原因导致积分丢失补偿加值" selected>系统补偿</option>
    <option value="4" title="补偿签到加值">签到补偿</option>
    <option value="6" title="赞助加值">赞助加值</option>
  </select>
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
  <button class="btn btn-success float-right" id="btn" name="button" onclick="javascript:conpensate();">增加</button>
</p>
@endsection
@section('script')
<script type="text/javascript">
function conpensate() {
  let uid = $('#uid').val();
  let count = $('#count').val();
  let tid = $('#tid').val();
  let captcha = $('#captcha').val();
  if (uid == '' || count == '' || tid == '' || captcha == '') {
    alert('请填写信息！');
    return false;
  }
  $('#btn').attr('disabled', 'disabled');
  $.ajax({
    url: '/api/admin/conpensate',
    type: 'post',
    data: {
      'uid': uid,
      'count': count,
      'tid': tid,
      'captcha': captcha
    },
    dataType: 'json',
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      $('#btn').removeAttr('disabled');
      if (status == 'timeout') {
        alert('请求超时，请稍候再试！');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        alert('增加完成！');
      }else if(data.errno === 2613){
        // 验证码错误
        $('#captcha_img').click();
        $('#captcha').val('');
        alert('验证码错误');
      }else{
        alert(data.error);
      }
    }
  });
}
</script>
@endsection
