@extends('admin/master')
@section('container')
<div class="row text-center">
  <div class="col-sm mb-4">
    <button type="button" class="btn btn-warning btn-block" onclick="javascript:optimize('users');">删除冗余用户</button>
  </div>
  <div class="col-sm mb-4">
    <button type="button" class="btn btn-danger btn-block" onclick="javascript:optimize('inactive_users');">删除不活跃用户</button>
  </div>
</div>
@endsection
<script type="text/javascript">
  function optimize(project) {
    $.getJSON('/api/admin/optimize/' + project, function(data){
      if (data.errno === 0) {
        alert('优化成功！');
      }else{
        alert(data.error);
      }
    });
  }
</script>
