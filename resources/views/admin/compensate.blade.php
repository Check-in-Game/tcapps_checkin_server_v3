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

<h2>补偿 / Compensate</h2>
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

<p class="clearfix">
  <button class="btn btn-success float-right" id="btn" name="button" onclick="javascript:conpensate();">增加</button>
</p>
@endsection
@section('script')
<script type="text/javascript">
function conpensate() {
  let uid = $('#uid').val();
  let count = $('#count').val();
  if (uid == '' || count == '') {
    alert('请填写信息！');
    return false;
  }
  $('#btn').attr('disabled', 'disabled');
  $.getJSON('/api/admin/conpensate/' + uid + '/' + count, function(data){
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
@endsection
