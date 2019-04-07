@extends('user/master')
@section('container')
<div class="alert alert-primary mt-4" role="alert">
  欢迎回来，{{ $username }} ！
</div>

<div>
  <div class="row text-center">
    <div class="col-sm mb-3">
      <button type="button" class="btn btn-primary btn-block" disabled>签到记录（建设中）</button>
    </div>
    <div class="col-sm mb-3">
      <button type="button" class="btn btn-primary btn-block" onclick="javascript:location.href='{{ action('UserController@shop') }}';">兑换中心</button>
    </div>
    <div class="col-sm mb-3">
      <button type="button" class="btn btn-primary btn-block" disabled>积分账单（建设中）</button>
    </div>
    <div class="col-sm mb-3">
      <button type="button" class="btn btn-primary btn-block" disabled>活动中心（建设中）</button>
    </div>
  </div>
  <div class="row">
    <div class="col-md-4 col-sm-12 mb-3 text-center">
      <div class="card border-dark mb-3">
        <div class="card-header">
          账户评级
        </div>
        <div class="card-body text-dark">
          <h5 class="card-title">
            @if($uid <= 100)
            骨灰
            @elseif($uid > 100 && $uid < 500)
            元老
            @else
            萌新
            @endif
            级
          </h5>
          <p class="card-text">您的UID为 {{$uid}}，评级仅供娱乐。</p>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
