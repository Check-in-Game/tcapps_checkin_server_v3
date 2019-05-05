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
  <input type="number" class="form-control" placeholder="UID" id="uid" value="{{ request()->get('uid') }}">
  <div class="input-group-append">
    <button class="btn btn-outline-secondary" type="button" onclick="javascript:location.href='?uid=' + $('#uid').val();">查</button>
  </div>
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

@if(isset($have_rights))
<h3>拥有权限一览</h3>
<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th scope="col">RID</th>
      <th scope="col">描述</th>
      <th scope="col">操作</th>
    </tr>
  </thead>
  <tbody>
      @foreach ($have_rights as $right)
        <tr id="tr_{{ $right->rid }}">
          <th scope="row">
            {{ $right->rid }}
          </th>
          <th scope="row" class="text-truncate">
            {{ $right->description }}
          </th>
          <th scope="row">
            <a href="javascript:;" onclick="del({{ $right->rid }});">移除</a>
          </th>
        </tr>
      @endforeach
    </tr>
  </tbody>
</table>
@endif

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
  m_loading();
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
      m_loading(false);
      if (status == 'timeout') {
        m_alert('请求超时，请稍候再试！', 'warning');
        return false;
      }
    },
    success: function(data) {
      if (data.errno === 0) {
        alert('增加完成！');
      }else{
        m_alert(data.error, 'danger');
      }
    }
  });
}

function del(rid) {
  let uid   = $('#uid').val();
  if (uid == '') {
    alert('请正确填写信息！');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/admin/rights',
    type: 'delete',
    data: {
      'uid': uid,
      'rid': rid
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
        $('#tr_' + rid).remove();
        m_alert('成功删除！', 'success');
      }else{
        m_alert(data.error, 'danger');
      }
    }
  });
}

</script>
@endsection
