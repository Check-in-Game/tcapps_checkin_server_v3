@extends('user/master')
@section('header')
交易市场
@endsection
@section('breadcrumb')
<div class="section-header-breadcrumb">
  <div class="breadcrumb-item"><a href="{{ action('UserController@market') }}">购买</a></div>
  <div class="breadcrumb-item"><a href="{{ action('UserController@market_sale') }}">挂售</a></div>
  <div class="breadcrumb-item"><a href="{{ action('UserController@market_manage') }}">管理</a></div>
</div>
@endsection
@section('body')
@if(count($items) !== 0)
<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th scope="col">SID</th>
      <th scope="col">物品</th>
      <th scope="col">单价</th>
      <th scope="col">库存</th>
      <th scope="col">操作</th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $item)
    <tr id="tr_{{$item->sid}}">
      <td>{{ $item->sid }}</td>
      <td><img class="lazy" src="{{ asset('img/loading.svg') }}"
         data-src="{{ $_system['cdn_prefix'] }}{{ $item->image }}"
         alt="{{ $item->iname }}" title="{{ $item->iname }}" data-toggle="tooltip" height="18x;"></td>
      <td>{{ $item->price }}</td>
      <td id="count_{{$item->sid}}">{{ $item->count }}</td>
      <td><a href="javascript:;" onclick="javascript:purchase_comfirm({{ $item->sid }});">购买</a></td>
    </tr>
    @endforeach
  </tbody>
</table>
<nav>
  {{ $items->links() }}
</nav>
@else
<div class="alert alert-primary">
  还没有可以购买的商品，稍候再来看看吧~
</div>
@endif
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
function purchase_comfirm(sid) {
  $('#pruchase_count').val(1);
  $('#btn_purchase_comfirm').attr('onclick', 'javascript:purchase(' + sid + ');');
  $('#_comfirm').modal({
    backdrop: 'static'
  });
}
function purchase(sid) {
  $('#_comfirm').modal('hide');
  let count = $('#pruchase_count').val();
  if (count <= 0) {
    m_alert('数量需要填写大于0的整数！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/market/purchase',
    type: 'post',
    dataType: 'json',
    data: {
      'sid': sid,
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
      if (data.errno == 0) {
        if ($('#count_' + sid).text() - count <= 0) {
          $('#tr_' + sid).remove();
        }
        $('#count_' + sid).text($('#count_' + sid).text() - count);
        m_tip('购买成功！', 'success');
      }else{
        if (data.errno == 5402) {
          info = '商品不存在或已经售罄！';
        }else if (data.errno == 5403) {
          info = '用户状态异常！';
        }else if (data.errno == 5404) {
          info = '该商品库存不足！';
        }else if (data.errno == 5405) {
          info = '积分不足！';
        }else if (data.errno == 5409) {
          info = '购买超时！';
        }else{
          info = '系统繁忙，请稍候再试！';
        }
        m_alert(info, 'danger');
      }
    }
  });
}
</script>
@endsection
