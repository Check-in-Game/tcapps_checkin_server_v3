@extends('user/master')
@section('header')
合成中心
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
<div class="row">
  @foreach($combers as $comber)
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-white lazy" style="background: url('{{ asset('img/loading.svg') }}') no-repeat center center;" data-src="{{ $_system['cdn_prefix'] }}{{ $comber->image }}">
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>{{ $comber->iname }}</h4>
        </div>
        <div class="card-body">
          <span id="comber_{{ $comber->iid }}">{{ isset($items[$comber->iid]['count']) ? $items[$comber->iid]['count'] : 0 }}</span>
          C
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
<div class="input-group mb-3">
  <div class="input-group-prepend">
    <span class="input-group-text">合成数量</span>
  </div>
  <input type="number" class="form-control" placeholder="合成数量" id="blend_count" value="1" min="1">
  <div class="input-group-append">
    <button class="btn btn-success" type="button" onclick="javascript:comfirm_blend();">合成</button>
  </div>
</div>
@endsection
@section('extraModalContent')
<div class="modal fade" tabindex="-1" role="dialog" id="_comfirm">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">合成确认</h5>
      </div>
      <div class="modal-body">
        <p>
          即将合成<strong id="comber_count">0</strong>个可莫尔，合成数量正确请点击下方【确认合成】按钮。
          <br>
          <span class="text-muted">本次合成手续费由合成中心统筹支出。</span>
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" onclick="javascript:blend();">确认合成</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">取消</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
  function comfirm_blend() {
    // 弹出确认提示
    let comber_count = $('#blend_count').val();
    $('#comber_count').text(comber_count);
    $('#_comfirm').modal({
      backdrop: 'static'
    });
  }
  function blend() {
    $('#_comfirm').modal('hide');
    m_loading();
    let comber_count = $('#blend_count').val();
    $.ajax({
      url: '/api/blend',
      type: 'post',
      dataType: 'json',
      data: {
        'count': comber_count,
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
          m_alert('合成成功！', 'success');
          $('#comber_1').text($('#comber_1').text() - comber_count);
          $('#comber_2').text($('#comber_2').text() - comber_count);
          $('#comber_3').text($('#comber_3').text() - comber_count);
          $('#comber_4').text($('#comber_4').text() - comber_count);
          if ($('#comber_1').text() < 0 || $('#comber_2').text() < 0
            || $('#comber_3').text() < 0 || $('#comber_4').text() < 0) {
            setTimeout("location.href=''", 2000);
          }
        }else{
          let info = '系统繁忙，请稍候再试';
          if (data.errno === 4002) {
            info = "可莫尔碎片数量不足！";
          }
          m_alert(info, 'danger');
        }
      }
    });
  }
</script>
@endsection
