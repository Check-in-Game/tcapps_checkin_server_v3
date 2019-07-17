@extends('user/master')
@section('header')
Worker升级
@endsection
@section('body')
<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th scope="col">Worker ID</th>
      <th scope="col">等级</th>
      <th scope="col">操作</th>
    </tr>
  </thead>
  <tbody>
    @foreach($workers as $worker)
    <tr>
      <th scope="row">{{ $worker->wid }}</th>
      <td id="w_level_{{ $worker->wid }}">{{ $worker->level }}</td>
      <td>
        <a href="javascript:;" onclick="javascript:upgrade_query({{ $worker->wid }});" id="worker_upgrade_btn_{{ $worker->wid }}">升级</a>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
<nav>
  {{ $workers->links() }}
</nav>
@endsection
@section('extraModalContent')
<!-- 收获 -->
<div class="modal fade" tabindex="-1" role="dialog" id="_upgrade">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">升级需求确认</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        本次升级需要下方表格中的物品，确认后系统将扣除相应的物品与积分。
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th scope="col">物品</th>
              <th scope="col">数量</th>
            </tr>
          </thead>
          <tbody id="_upgrade_body_table">
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="javascript:;" id="btn_harvest_comfirm">确认升级</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
  $(function(){
    $('.pagination').addClass('justify-content-center');
  });
</script>
<script type="text/template" id="tpl_upgrade_table">
<tr>
  <td scope="row"><img class="lazy" src="{{ asset('img/loading-bar.svg') }}" data-src="{{ $_system['cdn_prefix'] }}==3==" data-toggle="tooltip" title="==1==" height="20px;" /></td>
  <td>==2==</td>
</tr>
</script>
<script type="text/template" id="tpl_upgrade_table_point">
<tr>
  <th scope="row">积分</th>
  <td>==2==</td>
</tr>
</script>
<script type="text/javascript">
  function upgrade_query(wid) {
    let attr = $('#worker_upgrade_btn_' + wid).attr('onclick');
    $('#worker_upgrade_btn_' + wid).removeAttr('onclick');
    let tip = m_tip('加载中，请稍候...', 'info');
    $('#_upgrade_body_table').text('');
    $.ajax({
      url: '/api/worker/upgrade_query',
      type: 'post',
      dataType: 'json',
      data: {
        'wid': wid
      },
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        $('#worker_upgrade_btn_' + wid).attr('onclick', attr);
        m_tip_close(tip);
        if (status === 'timeout') {
          m_alert('响应超时，请稍候再试！');
        }
      },
      success: function(data){
        if (data.errno == 0) {
          let items = data.body.data.items;
          let demands = data.body.data.demands;
          let point = data.body.data.point;
          let table = '';
          table += $('#tpl_upgrade_table_point').text()
                    .replace('==2==', point);
          $.each(demands, function(key, value){
            let _d = $('#tpl_upgrade_table').text();
            _d = _d.replace('==1==', items[key]['iname']);
            _d = _d.replace('==2==', value);
            _d = _d.replace('==3==', items[key]['image']);
            table += _d;
          });
          $('#_upgrade_body_table').append(table);
          $('#_upgrade').modal({
            backdrop: 'static'
          });
          $("img.lazy").Lazy({
            effect: 'fadeIn',
            effectTime: 500
          });
          $('img').tooltip();
        }else{
          if (data.errno === 4901) {
            m_alert('获取Worker信息失败，请稍候再试！', 'danger');
          }else if (data.errno === 4902){
            m_alert('已经是最高等级啦！', 'warning');
          }else if (data.errno === 4903){
            m_alert('已经是最高等级啦！', 'warning');
          }else if (data.errno === 4904){
            m_alert('已经是最高等级啦！', 'warning');
          }else{
            m_alert('网络状态不佳，请稍候再试！', 'danger');
          }
        }
      }
    });
  }
  function upgrade(wid) {
    $('#_harvest').modal('hide');
    let captcha  = $('#captcha').val();
    if (captcha === '') {
      m_alert('验证码错误', 'warning');
      return false;
    }
    m_loading();
    $.ajax({
      url: '/api/worker/harvest',
      type: 'post',
      dataType: 'json',
      data: {
        'fid': fid,
        'captcha': captcha
      },
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        m_loading(false);
        if (status === 'timeout') {
          m_alert('响应超时，请稍候再试！', 'danger');
        }
      },
      success: function(data){
        if (data.errno === 0) {
          m_alert('成功收获 ' + data.body.data.profits + ' ' + data.body.data.iname, 'success');
        }else{
          if (data.errno === 4701) {
            m_alert('获取该区域信息失败，请稍候再试！', 'danger');
          }else if(data.errno === 4702){
            m_alert('发放收益失败，请稍候再试！', 'danger');
          }else if(data.errno === 4703){
            m_alert('验证码错误', 'warning');
          }else{
            m_alert('网络情况不佳，请稍候再试！', 'danger');
          }
        }
      }
    });
  }
</script>
@endsection
