@extends('user/master')
@section('header')
事务大厅
@endsection
@section('body')
<!-- 新人福利 -->
@if (date('Y-m-d 00:00:00', strtotime($_user->register_at)) >= date('Y-m-d 00:00:00', strtotime('-6 days')))
<div class="card">
  <div class="card-header">
    <h4>新人福利</h4>
  </div>
  <div class="card-body">
    新注册用户自注册日7天内每日领取一份新人福利。
    <ul>
      <li>第一天：体验积分券 * 1 + 粉色可莫尔 * 50</li>
      <li>第二天：小积分券 * 1 + 蓝色可莫尔 * 50</li>
      <li>第三天：积分券 * 1 + 绿色可莫尔 * 50</li>
      <li>第四天：挂售许可 * 10 + 黄色可莫尔 * 50</li>
      <li>第五天：Worker兑换券 * 5</li>
      <li>第六天：Worker升级卡 * 2</li>
      <li>第七天：Worker升级卡 * 5</li>
    </ul>
    <small>此项目产生的资源支出由系统结算。</small>
  </div>
  <div class="card-footer text-right">
    <button class="btn btn-success" onclick="javascript:gift();">领取今日份礼包</button>
  </div>
</div>
@endif

<!-- 捐赠 -->
<div class="row">

  <div class="col-sm-12 col-md-6">
    <div class="card">
      <div class="card-header">
        <h4>基金捐赠</h4>
      </div>
      <div class="card-body">
        <div class="row">
          <!-- 积分 -->
          <div class="col-6 text-right mb-1 font-weight-bold">基金存量：</div>
          <div class="col-6 text-left mb-1">{{ $foundation_point }}</div>
          <!-- 贡献 -->
          <div class="col-6 text-right mb-1 font-weight-bold">我的贡献：</div>
          <div class="col-6 text-left mb-1">{{ $my_credit }} <a href="javascript:donate_comfirm();">捐赠</a></div>
        </div>
      </div>
      <div class="card-footer">
        <small>捐赠的积分将由基金会进行管理，捐赠者将获得相应的贡献值。</small>
      </div>
    </div>
  </div>

  <div class="col-sm-12 col-md-6">
    <div class="card">
      <div class="card-body">
        <div class="hero align-items-center bg-secondary text-muted">
          <div class="hero-inner text-center">
            <h2>Coming Soon</h2>
            <p class="lead">更多玩法、功能即将开放，敬请期待！</p>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>


<!-- 排行榜 -->
<div class="row">
  <div class="col-sm-12">
    <div class="card">
      <div class="card-header">
        <h4>贡献 Top10</h4>
      </div>
      <div class="card-body">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th scope="col">排名</th>
              <th scope="col">昵称</th>
              <th scope="col">贡献值</th>
            </tr>
          </thead>
          <tbody>
              @foreach ($charts as $key => $chart)
                <tr style="line-height:26px;">
                  <th scope="row">
                    # {{ $key + 1 }}
                  </th>
                  <th scope="row">
                    {{ $chart->nickname }}
                  </th>
                  <th scope="row">
                    {{ $chart->credit }}
                  </th>
                </tr>
              @endforeach
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

@endsection
@section('extraModalContent')
<div class="modal fade" tabindex="-1" role="dialog" id="_donation">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">基金会捐赠</h5>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">捐赠积分</span>
          </div>
          <input type="number" class="form-control" placeholder="捐赠积分" id="point" value="1" min="1">
        </div>
        <p>
          <small>目前只接受积分捐赠，一旦捐赠无法撤销，捐赠后会获得相应的贡献奖励。</small>
          <br>
          <small>每日只可捐赠一次。</small>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="javascript:donate_point();">确认捐赠</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
function donate_comfirm() {
  $('#_donation').modal('show');
}
function donate_point() {
  $('#_donation').modal('hide');
  let point = $('#point').val();
  if (point == 0 || point < 0 || point == '') {
    m_alert('请输入有效的捐赠数值', 'danger');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/foundation/business/donate_point',
    type: 'post',
    dataType: 'json',
    data: {
      'point': point
    },
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status === 'timeout') {
        m_alert('响应超时，请稍候再试！', 'warning');
      }
    },
    success: function(data){
      if (data.errno == 0) {
        m_tip('捐赠成功！', 'success');
      }else{
        if (data.errno == 6502) {
          m_alert('今天已经捐赠过啦~', 'danger');
        }else if(data.errno == 6503) {
          m_alert('您的积分不足！', 'danger');
        }else{
          m_alert('网络状态不佳，请稍候再试！', 'danger');
        }
      }
    }
  });

}
function gift() {
  m_loading();
  $.ajax({
    url: '/api/foundation/business/fresher_gift',
    type: 'get',
    dataType: 'json',
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status === 'timeout') {
        m_alert('响应超时，请稍候再试！', 'warning');
      }
    },
    success: function(data){
      if (data.errno == 0) {
        m_tip('领取成功！', 'success');
      }else{
        if (data.errno == 6401) {
          m_alert('您没有可以领取的礼包', 'danger');
        }else if(data.errno == 6402) {
          m_alert('您今日的礼包已经领取过啦~', 'danger');
        }else{
          m_alert('网络状态不佳，请稍候再试！', 'danger');
        }
      }
    }
  });
}
</script>
@endsection
