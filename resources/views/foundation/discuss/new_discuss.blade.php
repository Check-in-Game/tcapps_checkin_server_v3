@extends('user/master')
@section('meta')
@endsection
@section('header')
议事大厅：新议项
@endsection
@section('body')
<div class="card">
  <div class="card-header">
    <h4>创建新议项</h4>
  </div>
  <div class="card-body">
    <div class="form-group row mb-4">
      <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">主题</label>
      <div class="col-sm-12 col-md-7">
        <input type="text" class="form-control" id="topic">
      </div>
    </div>
    <div class="form-group row mb-4">
      <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">类型</label>
      <div class="col-sm-12 col-md-7">
        <select class="form-control selectric" id="type">
          <option value="1">创意</option>
          <option value="2">问题</option>
          <option value="3">Bug反馈</option>
        </select>
      </div>
    </div>
    <div class="form-group row mb-4">
      <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">内容</label>
      <div class="col-sm-12 col-md-7">
        <textarea class="form-control" style="height: 120px;" id="content"></textarea>
      </div>
    </div>
    <div class="form-group row mb-4">
      <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
      <div class="col-sm-12 col-md-7">
        <button class="btn btn-primary float-right" onclick="javascript:post_discuss();">提交</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('extraModalContent')

@endsection
@section('script')
<script type="text/javascript">
$(function(){
  $('.selectric').selectric();
});
function post_discuss() {
  let topic = $('#topic').val();
  let type = $('#type').val();
  let content = $('#content').val();
  if (topic == '') {
    m_alert('请填写主题');
    return false;
  }
  if (content == '') {
    m_alert('请填写内容');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/foundation/discuss/new',
    type: 'post',
    dataType: 'json',
    data: {
      'topic': topic,
      'type': type,
      'content': content
    },
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status === 'timeout') {
        m_alert('响应超时，请稍候再试！');
      }
    },
    success: function(data){
      if (data.errno == 0) {
        m_alert('提交成功！', 'success');
      }else{
        if (data.errno == 5901) {
          info = "您的账户仍然处于实习期（一周）或状态异常，暂时无法发布话题。";
        }else if(data.errno == 5902) {
          info = "您今日创建的话题过多！";
        }else if(data.errno == 5903) {
          info = "请选择正确的话题类型！";
        }else{
          info = "系统繁忙，请稍候再试！";
        }
        m_alert(info, 'danger');
      }
    }
  });
}
</script>
@endsection
