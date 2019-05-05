@extends('admin/master')
@section('container')
<!-- 公告-15 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<div class="row text-center">
  <div class="col-sm mb-4">
    <button type="button" class="btn btn-warning btn-block" onclick="javascript:optimize('users');">删除冗余用户</button>
  </div>
  <div class="col-sm mb-4">
    <button type="button" class="btn btn-danger btn-block" onclick="javascript:optimize('inactive_users');">删除不活跃用户</button>
  </div>
  <div class="col-sm mb-4">
    <button type="button" class="btn btn-primary btn-block" onclick="javascript:optimize('checkin_list_settle');">结算一个月前的签到记录</button>
  </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
  function optimize(project) {
    $.getJSON('/api/admin/optimize/' + project, function(data){
      if (data.errno === 0) {
        m_alert('优化成功！', 'success');
      }else{
        m_alert(data.error, 'danger');
      }
    });
  }
</script>
@endsection
