@extends('user/master')
@section('before_nav')
@endsection

@section('container')
<!-- 公告-6 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<div class="alert alert-primary mt-4" role="alert">
  兑换中心欢迎您，{{ $username }} ！您的可用积分为：{{ $balance }}。
</div>
<div class="row">
  @if($goods)
    @foreach($goods as $key => $value)
    <div class="col-md-4 col-sm-12 mb-3 text-center">
      <div class="card @if($value['endtime'] !== '1970-01-01 00:00:00') border-danger @endif mb-3">
        <div class="card-header">
          @if($value['endtime'] !== '1970-01-01 00:00:00')
          <span class="badge badge-danger">限时</span>
          @endif
          @if($value['all_count'] !== 0)
          <span class="badge badge-warning">限量</span>
          @endif
          @if($value['endtime'] === '1970-01-01 00:00:00' && $value['all_count'] === 0)
          <span class="badge badge-primary">日常销售</span>
          @endif
        </div>
        <div class="card-body">
          <h5 class="card-title">{{ $value['gname'] }}</h5>
          @if($value['sid'] === 1)
            @if(empty($value['image']))
            <p class="card-text">此商品暂无预览图</p>
            @else
            <img class="card-img-top" src="{{ $value->image }}" alt="Card image cap">
            @endif
          @endif
          <p class="card-text text-left">价格：{{ $value['cost'] }}积分</p>
          @if($value['all_count'] !== 0)
          <p class="card-text text-left">剩余：{{ $value['all_count']-$value['all_bought'] }} 枚</p>
          @else
          <p class="card-text text-left">此商品不限量</p>
          @endif
          <p class="card-text text-left">{{ $value['description'] }}</p>
          <p class="card-text text-left">
            发售停止时间：
            @if($value['endtime'] !== '1970-01-01 00:00:00')
            {{ $value['endtime'] }}
            @else
            无
            @endif
          </p>
        </div>
        <div class="card-footer text-muted">
          @if($value['all_count'] !== 0 && $value['all_count']-$value['all_bought'] <= 0)
          <button type="button" name="button" class="btn btn-primary" disabled>已售罄</button>
          @elseif($value['rebuy'] !== 0 && $value['rebuy'] <= $value['user_bought'])
          <button type="button" name="button" class="btn btn-primary" disabled>已到达购买上限</button>
          @else
          <button type="button" name="button" class="btn btn-primary" onclick="javascript:purchase({{ $value['gid'] }});" id="btn_g_{{ $value['gid'] }}">购买 / Purchase</button>
          @endif
        </div>
      </div>
    </div>
    @endforeach
  @else
  <div class="col-md-12 col-sm-12 mb-3 text-center">
    <div class="alert alert-secondary" role="alert">
      还没有商品上架呢，等一会再来看吧~
    </div>
  </div>
  @endif
</div>
@endsection
<script type="text/javascript">
  function purchase(gid) {
    $('#btn_g_' + gid).attr('disabled', 'disabled');
    $.getJSON('/api/purchase/' + gid, function(data){
      if (data.errno === 0) {
        alert('购买成功！');
        location.href = '';
      }else{
        let info = '购买失败！请刷新页面后重试！';
        if (data.errno === 2503) {
          info = '余额不足！';
        }
        if (data.errno === 2504) {
          info = '该商品已经停售！';
        }
        if (data.errno === 2505) {
          info = '该商品已售罄！';
        }
        if (data.errno === 2506) {
          info = '该商品的购买次数已经达到上限！';
        }
        alert(info);
        $('#btn_g_' + gid).removeAttr('disabled');
      }
    });
  }
</script>
