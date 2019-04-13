@extends('user/master')
@section('before_nav')
@endsection

@section('container')
<!-- 公告-5 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<div class="row">

  <!-- 账户评级 -->
  <div class="col-md-4 col-sm-12 mb-3 text-center">
    <div class="card border-dark mb-3">
      <div class="card-header">
        基本信息
      </div>
      <div class="card-body text-dark">
        <p class="card-text">UID： <span class="badge badge-primary">{{ $uid }}</span></p>
        <p class="card-text">签到积分： <span class="badge badge-primary">{{ $all_worth }}</span></p>
        <p class="card-text">可用积分： <span class="badge badge-primary">{{ $all_worth - $cost }}</span></p>
      </div>
    </div>
  </div>

  <div class="col-md-4 col-sm-12 mb-3 text-center">
    <!-- 账户评级 -->
    <div class="card border-dark mb-3">
      <div class="card-header">
        账户安全
      </div>
      <div class="card-body text-dark">
        <a class="btn btn-danger btn-block" href="{{ action('UserController@security_change_password') }}">修改密码</a>
      </div>
    </div>
  </div>

</div>
@endsection
