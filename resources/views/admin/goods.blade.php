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

<h2>创建商品 / Create Goods</h2>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">商品名称</span>
  </div>
  <input type="text" class="form-control" placeholder="Good's name" id="name">
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
  <button class="btn btn-success float-right" id="btn" name="button" onclick="javascript:add();">增加</button>
</p>
@endsection
@section('script')
<script type="text/javascript">
function add() {
  $('#btn').attr('disabled', 'disabled');
  let name        = $('#name').val();
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
  if (name == '' || cost == '' || starttime == '' || endtime == '' || tid == '' || sid == '' || rebuy == '' || all_count == '' || description == '' || captcha == '') {
    alert('请填写信息！');
    return false;
  }
  image = image == '' ? 'null' : image;
  // $('#btn').attr('disabled', 'disabled');
  $.ajax({
    url: '/api/admin/goods/add',
    type: 'post',
    data: {
      'name': name,
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
      $('#btn').removeAttr('disabled');
      if (status == 'timeout') {
        alert('请求超时，请稍候再试！');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        alert('增加成功！');
      }else{
        $('#captcha_img').click();
        $('#captcha').val('');
        alert(data.error);
      }
    }
  });
}
</script>
@endsection
