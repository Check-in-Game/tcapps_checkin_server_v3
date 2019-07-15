@extends('user/master')
@section('header')
资源商城
@endsection
@section('body')
<div class="row">
  @if($goods)
  @foreach($goods as $key => $value)
  <div class="col-12 col-sm-6 col-md-6 col-lg-3">
    <article class="article article-style-b">
      <div class="article-header">
        <div class="article-image lazy" data-background="{{ asset('img/loading-bar.svg') }}" data-src="{{ $_system['cdn_prefix'] }}{{ $value['image'] }}" style="background-size: contain;">
        </div>
        <div class="article-badge">
          @if($value['endtime'] !== '1970-01-01 00:00:00')
          <div class="article-badge-item bg-danger" data-toggle="tooltip" title="现在 - {{ $value['endtime'] }}"><i class="fa-fw fas fa-clock"></i> 限时</div>
          @endif
          @if($value['all_count'] !== 0 || $value['rebuy'] !== 0)
          <div class="article-badge-item bg-warning" data-toggle="tooltip" title="@if($value['all_count'] !== 0)总限量 {{ $value['all_count'] }} 份@endif @if($value['rebuy'] !== 0)个人限购 {{ $value['rebuy'] }} 份@endif"><i class="fa-fw fas fa-fire"></i> 限量</div>
          @endif
          @if($value['onsale'] === 1 && date('Y-m-d H:i:s') >= $value['sale_starttime'] && date('Y-m-d H:i:s') <= $value['sale_endtime'])
          <div class="article-badge-item bg-info" data-toggle="tooltip" title="{{ $value['sale_starttime'] }} - {{ $value['sale_endtime'] }}"><i class="fa-fw fas fa-coins"></i> 促销</div>
          @endif
          @if($value['endtime'] === '1970-01-01 00:00:00' && $value['all_count'] === 0)
          <!-- 日常销售 -->
          @endif
        </div>
      </div>
      <div class="article-details" style="height:100%;">
        <div class="article-title">
          <h2><a href="javascript:m_alert('描述：{{ $value['description'] }}', 'success');">{{ $value['iname'] }}</a></h2>
        </div>
        <p>
          <strong>货号：</strong>
          {{ $value['cid'] }}
          <br>
          <strong>单价：</strong>
          @if($value['onsale'] === 1 && date('Y-m-d H:i:s') >= $value['sale_starttime'] && date('Y-m-d H:i:s') <= $value['sale_endtime'])
            <s class="text-muted">{{ $value['cost'] }}</s> <strong>{{ $value['sale_cost'] }}</strong>
          @else
            {{ $value['cost'] }}
          @endif
          积分
          <br>
          <strong>库存：</strong>
          @if($value['all_count'] !== 0)
            <span id="rest_{{ $value['cid'] }}">{{ $value['all_count']-$value['all_bought'] }}</span>
          @else
            <span id="rest_{{ $value['cid'] }}">不限量</span>
          @endif
        </p>
        <div class="article-cta">
          @if($value['all_count'] !== 0 && $value['all_count']-$value['all_bought'] <= 0)
          <button type="button" name="button" class="btn btn-primary" disabled>已售罄</button>
          @elseif($value['rebuy'] !== 0 && $value['rebuy'] <= $value['user_bought'])
          <button type="button" name="button" class="btn btn-primary" disabled>已到达购买上限</button>
          @else
          <button type="button" name="button" class="btn btn-primary" onclick="javascript:purchase_comfirm({{ $value['cid'] }});" id="btn_g_{{ $value['cid'] }}">购买 / Purchase</button>
          @endif
        </div>
      </div>
    </article>
  </div>
  @endforeach
  @else
  <div class="col-md-12 col-sm-12 mb-3">
    <div class="alert alert-light alert-has-icon">
      <div class="alert-icon"><i class="far fa-lightbulb"></i></div>
      <div class="alert-body">
        <div class="alert-title">老板别急~小店一会就开张！</div>
        我们正在努力备货中，请稍等...
      </div>
    </div>
  </div>
  @endif
</div>
@endsection
@section('extraModalContent')
<div class="modal fade" tabindex="-1" role="dialog" id="_comfirm">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">购买</h5>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">购买数量</span>
          </div>
          <input type="number" class="form-control" placeholder="购买数量" id="pruchase_count" value="1" min="1">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="javascript:;" id="btn_purchase_comfirm">确认购买</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>

@endsection
@section('script')
<script type="text/javascript">
  function purchase_comfirm(cid) {
    $('#pruchase_count').val(1);
    let rest = $('#rest_' + cid).text();
    if (rest != '不限量') {
      $('#pruchase_count').attr('max', rest);
    }else{
      $('#pruchase_count').removeAttr('max');
    }
    $('#btn_purchase_comfirm').attr('onclick', 'javascript:purchase(' + cid + ');');
    $('#_comfirm').modal();
  }
  function purchase(cid) {
    $('#_comfirm').modal('hide');
    let count = $('#pruchase_count').val();
    $('#btn_g_' + cid).attr('disabled', 'disabled');
    m_loading();
    $.ajax({
      url: '/api/purchase',
      type: 'post',
      dataType: 'json',
      data: {
        'cid': cid,
        'count': count
      },
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        m_loading(false);
        if (status === 'timeout') {
          m_alert('响应超时，请稍候再试！');
        }
      },
      success: function(data){
        if (data.errno === 0) {
          m_tip('购买成功！', 'success');
          $('#btn_g_' + cid).removeAttr('disabled');
        }else{
          $('#btn_g_' + cid).removeAttr('disabled');
          let info = '购买失败！请刷新页面后重试！';
          if (data.errno === 2503) {
            info = '余额不足！';
          }else if (data.errno === 2504) {
            info = '该商品已经停售！';
            $('#btn_g_' + cid).text('已停售');
            $('#btn_g_' + cid).attr('disabled', 'disabled');
          }else if (data.errno === 2505) {
            info = '该商品库存不足！';
          }else if (data.errno === 2506) {
            info = '该商品剩余购买次数不足！剩余购买次数：' + data.body.rest;
          }else if (data.errno === 2509) {
            info = '错误的购买数量';
          }else{
            info = '系统繁忙，请稍候再试';
          }
          m_alert(info, 'danger');
        }
      }
    });
  }
</script>
@endsection
