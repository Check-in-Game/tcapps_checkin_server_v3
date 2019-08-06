@extends('user/master')
@section('header')
回收中心
@endsection
@section('body')
<div class="alert alert-success" role="alert">
  <h4 class="alert-heading">注意事项</h4>
  <p>
    回收中心以积分结算，显示的【单价】是回收单价，点击【回收】后填写回收数量确认回收即可。
    <br />
    回收操作不可撤销。
  </p>
</div>
<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th scope="col">图标</th>
      <th scope="col" class="d-none d-lg-table-cell">名称</th>
      <th scope="col">拥有</th>
      <th scope="col">单价</th>
      <th scope="col">操作</th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $iid => $item)
    <tr>
      <td><img class="lazy" src="{{ asset('img/loading.svg') }}" data-src="{{ $_system['cdn_prefix'] }}{{ $item['image'] }}" alt="{{ $item['iname'] }}" height="18x;" title="{{ $item['iname'] }}" data-toggle="tooltip"></td>
      <td class="d-none d-lg-table-cell">{{ $item['iname'] }}</td>
      <td id="rc_{{ $iid }}">{{ $item['valid'] }}</td>
      <td id="rv_{{ $iid }}">{{ $item['recycle_value'] }}</td>
      <td><a href="javascript:comfirm_recycle({{ $iid }});">回收</a></td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
@section('extraModalContent')
<div class="modal fade" tabindex="-1" role="dialog" id="_comfirm">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">回收确认</h5>
      </div>
      <div class="modal-body">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">回收数量</span>
          </div>
          <input type="hidden" class="form-control" id="recycle_iid" value="1">
          <input type="number" class="form-control" placeholder="回收数量" id="recycle_count" value="1" min="1" onchange="calc();" onkeyup="calc();">
        </div>
        <p>
          本次回收预计到账 <span id="anticipate_point">0</span> 积分
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="javascript:recycle();">确认回收</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
// 计算预计到账积分
function calc() {
  let iid   = $('#recycle_iid').val();
  let value = $('#rv_' + iid).text();
  let count = $('#recycle_count').val();
  $('#anticipate_point').text(value * count);
}
function comfirm_recycle(iid) {
  // 弹出数量选择
  $('#recycle_iid').val(iid);
  // 计算预计到账积分
  calc();
  $('#_comfirm').modal({
    backdrop: 'static'
  });
}
function recycle() {
  $('#_comfirm').modal('hide');
  m_loading();
  let iid = $('#recycle_iid').val();;
  let count = $('#recycle_count').val();;
  $.ajax({
    url: '/api/recycle',
    type: 'post',
    dataType: 'json',
    data: {
      'iid': iid,
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
        m_alert('回收成功！', 'success');
        let value = $('#rc_' + iid).text($('#rc_' + iid).text() - count);
      }else{
        let info = '系统繁忙，请稍候再试';
        if (data.errno == 4102) {
          info = "拥有的资源数量不足！";
        }else if(data.errno == 4103) {
          info = "错误的物品ID！";
        }else if(data.errno == 4104) {
          info = "扣除资源失败！";
        }else if(data.errno == 4105) {
          info = "增加积分失败！";
        }
        m_alert(info, 'danger');
      }
    }
  });
}
</script>
@endsection
