@extends('admin/master')
@section('container')
<!-- 公告-22 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>管理勋章 / Manage Badge</h2>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">勋章BID</span>
  </div>
  <input type="number" class="form-control" placeholder="BID" id="bid">
  <div class="input-group-append">
    <button class="btn btn-outline-secondary" type="button" onclick="javascript:search();">查</button>
  </div>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">勋章名称</span>
  </div>
  <input type="text" class="form-control" placeholder="Badge's name" id="bname">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">展示链接</span>
  </div>
  <input type="text" class="form-control" placeholder="Image link" id="image">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">前景色</span>
  </div>
  <input type="text" class="form-control" placeholder="Fg color" id="fgcolor" value="#f2f2f2">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">背景色</span>
  </div>
  <input type="text" class="form-control" placeholder="Bg color" id="bgcolor" value="#202020">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">商品GID</span>
  </div>
  <input type="number" class="form-control" placeholder="GID" id="gid">
  <div class="input-group-append">
    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">选择商品</button>
    <div class="dropdown-menu">
      @foreach($goods as $good)
      <a class="dropdown-item" href="javascript:$('#gid').val({{ $good->gid }});" title="{{ $good->description }}">#{{ $good->gid }}: {{ $good->gname }}</a>
      @endforeach
    </div>
  </div>
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <label class="input-group-text" for="eid">效果EID</label>
  </div>
  <select class="custom-select" id="eid">
    <option selected>选择效果</option>
    @foreach($effects as $effect)
    <option value="{{ $effect->eid }}" title="{{ $effect->description }}">#{{ $effect->eid }}:{{ $effect->description }}</option>
    @endforeach
  </select>
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
  let bid       = $('#bid').val();
  if (bid === '') {
    alert('请填写BID！');
    return false;
  }
  $.getJSON('/api/admin/badges/search/' + bid, function(data){
    if (data.errno === 0) {
      $('#bid').val(data.body.data.bid);
      $('#bname').val(data.body.data.bname);
      $('#image').val(data.body.data.image);
      $('#fgcolor').val(data.body.data.fgcolor);
      $('#bgcolor').val(data.body.data.bgcolor);
      $('#gid').val(data.body.data.gid);
      $('#eid').val(data.body.data.eid);
      $('#status').val(data.body.data.status);
    }else{
      alert(data.error);
    }
  });
}
function add() {
  let bname     = $('#bname').val();
  let image     = $('#image').val();
  let fgcolor   = $('#fgcolor').val();
  let bgcolor   = $('#bgcolor').val();
  let gid       = $('#gid').val();
  let eid       = $('#eid').val();
  let status    = $('#status').val();
  if (bname == '' || fgcolor == '' || bgcolor == '' || gid == '选择商品' || eid == '选择效果' || status == '') {
    alert('请填写信息！');
    return false;
  }
  $.ajax({
    url: '/api/admin/badges/add',
    type: 'post',
    data: {
      'bname': bname,
      'image': image,
      'fgcolor': fgcolor,
      'bgcolor': bgcolor,
      'gid': gid,
      'eid': eid,
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
        $('#bid').val('');
        $('#bname').val('');
        $('#image').val('');
        $('#fgcolor').val('');
        $('#bgcolor').val('');
        $('#gid').val('');
        $('#eid').val('');
        $('#status').val(1);
        alert('增加完成！');
      }else{
        alert(data.error);
      }
    }
  });
}
function update() {
  let bid       = $('#bid').val();
  let bname     = $('#bname').val();
  let image     = $('#image').val();
  let fgcolor   = $('#fgcolor').val();
  let bgcolor   = $('#bgcolor').val();
  let gid       = $('#gid').val();
  let eid       = $('#eid').val();
  let status    = $('#status').val();
  if (bname == '' || fgcolor == '' || bgcolor == '' || gid == '选择商品' || eid == '选择效果' || status == '') {
    alert('请填写信息！');
    return false;
  }
  $.ajax({
    url: '/api/admin/badges/update',
    type: 'post',
    data: {
      'bid': bid,
      'bname': bname,
      'image': image,
      'fgcolor': fgcolor,
      'bgcolor': bgcolor,
      'gid': gid,
      'eid': eid,
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
  let bid       = $('#bid').val();
  if (bid == '') {
    alert('请填写BID！');
    return false;
  }
  $.ajax({
    url: '/api/admin/badges/delete',
    type: 'post',
    data: {
      'bid': bid
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
        $('#bid').val('');
        $('#bname').val('');
        $('#image').val('');
        $('#fgcolor').val('');
        $('#bgcolor').val('');
        $('#gid').val('');
        $('#eid').val('');
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
