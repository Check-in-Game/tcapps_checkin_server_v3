@extends('user/master')
@section('header')
交易市场：管理
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
      <td><a href="javascript:;" onclick="javascript:modify_comfirm({{ $item->sid }});" id="price_{{$item->sid}}">{{ $item->price }}</a></td>
      <td id="count_{{$item->sid}}">{{ $item->count }}</td>
      <td><a href="javascript:;" onclick="javascript:pull_off({{ $item->sid }});">下架</a></td>
    </tr>
    @endforeach
  </tbody>
</table>
<nav>
  {{ $items->links() }}
</nav>
@else
<div class="alert alert-primary">
  还没有商品，挂售以后再来看看吧~
</div>
@endif
@endsection
@section('extraModalContent')
<div class="modal fade" tabindex="-1" role="dialog" id="_comfirm">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">单价修改</h5>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">单价</span>
          </div>
          <input type="number" class="form-control" placeholder="单价" id="price" value="10" min="1">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="javascript:;" id="btn_purchase_comfirm">确认修改</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
function modify_comfirm(sid) {
  $('#price').val($('#price_' + sid).text());
  $('#btn_purchase_comfirm').attr('onclick', 'javascript:modify(' + sid + ');');
  $('#_comfirm').modal({
    backdrop: 'static'
  });
}
function modify(sid) {
  $('#_comfirm').modal('hide');
  let price = $('#price').val();
  if (price <= 0) {
    m_alert('价格需要填写大于0的整数！', 'warning');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/market/modify/price',
    type: 'post',
    dataType: 'json',
    data: {
      'sid': sid,
      'price': price
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
        $('#price_' + sid).text(price);
        m_tip('修改成功！', 'success');
      }else{
        if (data.errno == 5502) {
          info = '无需修改、已经售罄或挂售不存在！';
        }else{
          info = '系统繁忙，请稍候再试！';
        }
        m_alert(info, 'danger');
      }
    }
  });
}
function pull_off(sid) {
  m_loading();
  $.ajax({
    url: '/api/market/pulloff',
    type: 'post',
    dataType: 'json',
    data: {
      'sid': sid
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
        $('#tr_' + sid).remove();
        m_tip('下架成功！', 'success');
      }else{
        if (data.errno == 5601) {
          info = '物品不存在或售罄或状态异常！';
        }else if (data.errno == 5603){
          info = '退回物品失败！';
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
