@extends('user/master')
@section('header')
交易市场：挂售
@endsection
@section('breadcrumb')
<div class="section-header-breadcrumb">
  <div class="breadcrumb-item"><a href="{{ action('UserController@market') }}">购买</a></div>
  <div class="breadcrumb-item"><a href="{{ action('UserController@market_sale') }}">挂售</a></div>
  <div class="breadcrumb-item"><a href="{{ action('UserController@market_manage') }}">管理</a></div>
</div>
@endsection
@section('body')
<!-- 挂售须知 -->
<div class="alert alert-primary">
  <h4 class="alert-heading">挂售须知</h4>
  <p>
    1、申请挂售商品需要“挂售许可”，可以在商城购买，一旦提交挂售，消耗的“挂售许可”将不退还。<br />
    2、挂售商品价格需符合市场规律（资源商城中不售卖的商品除外），与市场价值差异过大的商品将被强制下架并不退还“挂售许可”，多次严重扰乱市场或恶意批量挂单的用户将被<strong>封禁账户</strong>并不给予解封。<br />
    3、提交挂售后，系统将暂时扣除对应物品，取消挂售后返还。<br />
    4、挂售提交后，允许挂售人多次修改挂售价格和暂停挂售。<br />
    5、提供的参考单价为系统回收单价，非市场平均价。<br />
    6、仅【通用】类型的物品可进行寄售。<br />
    <strong>7、提交挂售视为已经阅读、充分理解并同意“挂售须知”。</strong>
  </p>
</div>

<button class="btn btn-info mb-4" onclick="items_query();">选择物品</button>

<p id="item_preview">暂未选择物品</p>

<input type="hidden" value="0" id="sale_iid">

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">售卖价格</span>
  </div>
  <input type="number" class="form-control" min="1" value="1" id="sale_price">
</div>

<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">售卖数量</span>
  </div>
  <input type="number" class="form-control" min="1" value="1" id="sale_count">
</div>
<p class="clearfix">
  <button class="btn btn-danger mb-4 float-right" onclick="sale();" id="btn_sale">提交挂售</button>
</p>

@endsection
@section('extraModalContent')
<div class="modal fade" tabindex="-1" role="dialog" id="_sale">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">选择挂售物品</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th scope="col">物品</th>
              <th scope="col">库存</th>
              <th scope="col">操作</th>
            </tr>
          </thead>
          <tbody id="_sale_body_table">
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
@section('script')
<script type="text/template" id="tpl_sale_table">
<tr>
  <td scope="row">
    <img class="lazy" src="{{ asset('img/loading.svg') }}"
      data-src="{{ $_system['cdn_prefix'] }}==3=="
      data-toggle="tooltip" title="==1==" height="20px;" />
  </td>
  <td>==2==</td>
  <td><a href="javascript:;" onclick="select(==4==);">选择</a></td>
</tr>
</script>

<script type="text/template" id="tpl_item_preview">
  <strong>出售物品：</strong>
  <img class="lazy" src="{{ asset('img/loading.svg') }}"
    data-src="{{ $_system['cdn_prefix'] }}==3=="
    data-toggle="tooltip" title="==1==" height="20px;" />
  <br />
  <strong>参考单价：</strong>
  ==4== 积分
  <br />
  <strong>剩余库存：</strong>
  ==5==
</script>

<script type="text/javascript">
  var _items = [];
  function items_query() {
    let tip = m_tip('加载中，请稍候...', 'info');
    $('#_sale_body_table').text('');
    $.ajax({
      url: '/api/market/sale/query_items',
      type: 'get',
      dataType: 'json',
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        m_tip_close(tip);
        if (status === 'timeout') {
          m_alert('响应超时，请稍候再试！');
        }
      },
      success: function(data){
        if (data.errno == 0) {
          let items = data.body.data.items;
          // let user_items = data.body.data.user_items;
          let table = '';
          $.each(items, function(iid, item){
            // 注册物品信息
            _items[item['iid']] = [];
            _items[item['iid']]['iname'] = item['iname'];
            _items[item['iid']]['image'] = item['image'];
            _items[item['iid']]['recycle_value'] = item['recycle_value'];
            _items[item['iid']]['max'] = item['amount'];
            let _d = $('#tpl_sale_table').text();
            _d = _d.replace('==1==', item['iname']);
            _d = _d.replace('==2==', item['amount']);
            _d = _d.replace('==3==', item['image']);
            _d = _d.replace('==4==', item['iid']);
            table += _d;
          });
          $('#_sale_body_table').append(table);
          $('#_sale').modal({
            backdrop: 'static'
          });
          $("img.lazy").Lazy({
            effect: 'fadeIn',
            effectTime: 500
          });
          $('img').tooltip();
        }else{
          m_alert('系统繁忙，请稍候再试！', 'danger');
        }
      }
    });
  }
  // 选择物品
  function select(iid) {
    $('#_sale').modal('hide');
    $('#item_preview').text('');
    let tpl = $('#tpl_item_preview').text();
    tpl = tpl.replace('==1==', _items[iid]['iname'])
    tpl = tpl.replace('==3==', _items[iid]['image'])
    tpl = tpl.replace('==4==', _items[iid]['recycle_value'])
    tpl = tpl.replace('==5==', _items[iid]['max'])
    $('#item_preview').append(tpl);
    $('#sale_iid').val(iid);
    $('#sale_price').val(_items[iid]['recycle_value']);
    $('#sale_count').attr('max', _items[iid]['max']);
    $("img.lazy").Lazy({
      effect: 'fadeIn',
      effectTime: 500
    });
    $('img').tooltip();
  }
  // 提交挂售
  function sale() {
    let iid = $('#sale_iid').val();
    let price = $('#sale_price').val();
    let count = $('#sale_count').val();
    if (iid == 0) {
      items_query();
      return false;
    }
    if (price <= 0 || count <= 0) {
      m_alert('价格与单价需要填写大于0的整数！', 'warning');
      return false;
    }
    m_loading();
    $.ajax({
      url: '/api/market/sale',
      type: 'post',
      dataType: 'json',
      data: {
        'iid': iid,
        'price': price,
        'count': count
      },
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        m_loading(false);
        if (status === 'timeout') {
          m_alert('响应超时，请稍候再试！', 'danger');
        }
      },
      success: function(data){
        if (data.errno == 0) {
          m_tip('挂售成功！', 'success');
        }else{
          if (data.errno == 5302) {
            m_alert('请购买挂售许可后再挂售物品！', 'warning');
          }else if(data.errno == 5303) {
            m_alert('挂售的物品剩余数量不足！', 'warning');
          }else if(data.errno == 5306) {
            m_alert('挂售数量与挂售单价不可小于0', 'warning');
          }else if(data.errno == 5307) {
            m_alert('挂售价格过低', 'warning');
          }else{
            m_alert('网络状态不佳，请稍候再试！', 'danger');
          }
        }
      }
    });
  }
</script>

@endsection
