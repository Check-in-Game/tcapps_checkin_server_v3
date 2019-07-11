@extends('user/master')
@section('header')
Worker管理
@endsection
@section('body')
<!-- 基本信息 -->
<div class="row">
  <div class="col-md-4 col-sm-12 mb-3 text-center">
    <div class="card border-dark mb-3">
      <div class="card-header">
        基本信息
      </div>
      <div class="card-body text-dark">
        <div class="row">
          <!-- UID -->
          <div class="col-6 text-right mb-1 font-weight-bold">兑换券：</div>
          <div class="col-6 text-left mb-1">{{ $worker_ticket }} @if($worker_ticket > 0) <a href="javascript:redeem();">兑换</a> @endif</div>
          <!-- 积分 -->
          <div class="col-6 text-right mb-1 font-weight-bold">Worker：</div>
          <div class="col-6 text-left mb-1">{{ $worker_count }}</div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- 产区 -->
<div class="row">
  @foreach($field as $f)
  <div class="col-12 col-md-6 col-lg-3">
    <div class="card card-dark">
      <div class="card-header">
        <h4>{{$f->fname}}</h4>
        <div class="card-header-action">
          <a href="javascript:assign({{ $f->fid }});" class="btn btn-primary">投入</a>
          <a href="javascript:resign({{ $f->fid }});" class="btn btn-danger">撤出</a>
          <a href="javascript:harvest({{ $f->fid }});" class="btn btn-success">收获</a>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6 text-right mb-1 font-weight-bold">
            <strong>产出资源：</strong>
          </div>
          <div class="col-6 text-left mb-1">
            {{ $f->iname }}
          </div>
          <div class="col-6 text-right mb-1 font-weight-boldcol-6 text-right mb-1 font-weight-bold">
            <strong>产出速度：</strong>
          </div>
          <div class="col-6 text-left mb-1">
            {{ $f->speed }}/h
          </div>
          <div class="col-6 text-right mb-1 font-weight-boldcol-6 text-right mb-1 font-weight-bold">
            <strong>产出倍率：</strong>
          </div>
          <div class="col-6 text-left mb-1">
            {{ $f->times }} 倍
          </div>
          <div class="col-6 text-right mb-1 font-weight-boldcol-6 text-right mb-1 font-weight-bold">
            <strong>数量限制：</strong>
          </div>
          <div class="col-6 text-left mb-1">
            @if ($f->limi_count === 0)
              无限制
            @else
              {{ $f->limi_count }}
            @endif
          </div>
          <div class="col-6 text-right mb-1 font-weight-boldcol-6 text-right mb-1 font-weight-bold">
            <strong>最低等级：</strong>
          </div>
          <div class="col-6 text-left mb-1">
            {{ $f->limi_level }} 级
          </div>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endsection
@section('script')
<script type="text/javascript">
  function redeem() {
    m_loading();
    $.getJSON('/api/worker/redeem', function(data){
      m_loading(false);
      if (data.errno === 0) {
        m_alert('兑换成功！', 'success');
      }else{
        let info = '兑换失败！请刷新页面后重试！';
        if (data.errno === 4201) {
          info = '兑换券数量不足！';
        }else if (data.errno === 4202) {
          info = '扣除兑换券失败，请联系管理员追回兑换券！';
        }else if (data.errno === 4203) {
          info = '注册Worker失败，请联系管理员追回兑换券！';
        }
        m_alert(info, 'danger');
      }
    });
  }
</script>
@endsection
