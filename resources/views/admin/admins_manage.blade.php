@extends('admin/master')
@section('container')
<!-- 公告-20 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>管理提权 / Manage Admins</h2>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">UID</span>
  </div>
  <input type="number" class="form-control" placeholder="UID" id="uid">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <label class="input-group-text" for="rid">权限RID</label>
  </div>
  <select class="custom-select" id="rid">
    <option selected>选择权限</option>
    @foreach($rights as $right)
    <option value="{{ $right->rid }}" title="{{ $right->description }}">{{ $right->rname }}</option>
    @endforeach
  </select>
</div>

<p class="clearfix">
  <button class="btn btn-primary float-right mr-2" id="btn" name="button" onclick="javascript:add();">增加权限</button>
</p>

@endsection
@section('script')
<script type="text/javascript">
function add() {
  let uid   = $('#uid').val();
  let rid   = $('#rid').val();
  if (uid == '' || rid == '选择权限') {
    alert('请正确填写信息！');
    return false;
  }
  $.ajax({
    url: '/api/admin/rights/add',
    type: 'post',
    data: {
      'uid': uid,
      'rid': rid
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
        alert('增加完成！');
      }else{
        alert(data.error);
      }
    }
  });
}

</script>
@endsection
