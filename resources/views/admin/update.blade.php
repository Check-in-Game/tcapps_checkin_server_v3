@extends('admin/master')
@section('header')
升级管理
@endsection
@section('body')
<div class="row">
  <div class="col-3">
    <button class="btn btn-danger btn-block" type="button" onclick="migrate('points');">积分迁移</button>
  </div>
  <div class="col-3">
    <button class="btn btn-danger btn-block" type="button" onclick="migrate('badges');">勋章迁移</button>
  </div>
</div>
@endsection
@section('extraModalContent')
@endsection
@section('script')
<script type="text/javascript">
  function migrate(migrate) {
    m_loading();
    $.getJSON('/api/admin/migrate/' + migrate, function(data){
      m_loading(false);
      if (data.errno == 0) {
        m_alert('迁移成功！', 'success');
      }else{
        m_alert(data.errno + ':' + data.error, 'danger');
      }
    });
  }
</script>
@endsection
