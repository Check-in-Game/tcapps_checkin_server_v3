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
  <input type="text" class="form-control" placeholder="Goods' name" id="name">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">商品售价</span>
  </div>
  <input type="number" class="form-control" placeholder="Goods' cost" id="cost">
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
    <span class="input-group-text">商品类型编号</span>
  </div>
  <input type="number" class="form-control" placeholder="TID" id="tid" value='1'>
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

<p class="clearfix">
  <button class="btn btn-success float-right" id="btn" name="button" onclick="javascript:add();">增加</button>
</p>
@endsection
<script type="text/javascript">
function add() {
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
  if (name == '' || cost == '' || starttime == '' || endtime == '' || tid == '' || sid == '' || rebuy == '' || all_count == '' || description == '') {
    alert('请填写信息！');
  }
  image = image == '' ? 'null' : image;
  // $('#btn').attr('disabled', 'disabled');
  $.getJSON('/api/admin/goods/add/' + name + '/' + cost + '/' + starttime + '/' + endtime + '/' + tid + '/' + sid + '/' + rebuy + '/' + all_count + '/' + description + '/' + image, function(data){
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
