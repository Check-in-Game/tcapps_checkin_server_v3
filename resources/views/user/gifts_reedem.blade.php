@extends('user/master')
@section('header')
礼包兑换
@endsection
@section('body')
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">礼包兑换码</span>
  </div>
  <input type="text" class="form-control" id="reedem_token" placeholder="礼包兑换码">
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
  <button type="button" class="btn btn-success float-right" onclick="javascript:reedem();" id="btn">立即兑换</button>
</p>

@endsection
@section('extraModalContent')
<div class="modal fade" tabindex="-1" role="dialog" id="_items">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">礼包物品一览</h5>
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
          <tbody id="_items_body_table">
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-dismiss="modal">我知道啦</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script type="text/template" id="tpl_items_table">
<tr>
  <td scope="row"><img class="lazy" src="{{ asset('img/loading-bar.svg') }}" data-src="{{ $_system['cdn_prefix'] }}==3==" data-toggle="tooltip" title="==1==" height="20px;" /></td>
  <td>==2==</td>
</tr>
</script>

<script type="text/javascript">
  function reedem(){
    let token = $('#reedem_token').val();
    let captcha  = $('#captcha').val();
    // 检查密码长度
    if (token == '') {
      m_alert('请输入兑换码！');
      return false;
    }
    if (captcha == '') {
      m_alert('请输入验证码！');
      return false;
    }
    $('#_items_body_table').text('');
    m_tip('兑换中，请稍候...', 'info', 'reedeming');
    $('#btn').attr('disabled', 'disabled');
    $.ajax({
      url: '/api/gifts/reedem',
      type: 'post',
      dataType: 'json',
      data: {
        'token': token,
        'captcha': captcha
      },
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        m_tip_close('reedeming');
        $('#captcha_img').click();
        $('#captcha').val('');
        $('#btn').removeAttr('disabled');
        if (status === 'timeout') {
          m_alert('响应超时，请稍候再试！', 'warning');
        }
      },
      success: function(data){
        if (data.errno == 0) {
          m_tip('兑换成功！', 'success');
          let items = data.body.items;
          let gifts = data.body.gifts;
          let table = '';
          $.each(gifts, function(iid, value){
            let _d = $('#tpl_items_table').text();
            _d = _d.replace('==1==', items[iid]['iname']);
            _d = _d.replace('==2==', value['count']);
            _d = _d.replace('==3==', items[iid]['image']);
            table += _d;
          });
          console.log(table);
          $('#_items_body_table').append(table);
          $('#_items').modal({
            backdrop: 'static'
          });
          $("img.lazy").Lazy({
            effect: 'fadeIn',
            effectTime: 500
          });
          $('img').tooltip();
        }else{
          if (data.errno == 5101) {
            m_alert('验证码错误！', 'danger');
          }else if(data.errno == 5102) {
            m_alert('礼包不存在或已经过期！', 'danger');
          }else if(data.errno == 5103) {
            m_alert('礼包已经过期！', 'danger');
          }else if(data.errno == 5105) {
            m_alert('该礼包已经兑换完毕', 'danger');
          }else if(data.errno == 5106) {
            m_alert('您无法兑换此礼包', 'danger');
          }else if(data.errno == 5109) {
            m_alert('您已经兑换过这个礼包啦', 'success');
          }else{
            m_alert('网络状态不佳，请稍候再试！', 'danger');
          }
        }
      }
    });
  }
</script>
@endsection
