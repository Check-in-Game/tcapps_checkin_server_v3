@extends('public.master')
@section('container')
  <div class="alert alert-success mt-4" role="alert">
    中文名称不能直接注册，可以使用签到累计的积分向管理员提交改名申请。
  </div>

  @if(isset($reg_status) && $reg_status===false)
  <div class="alert alert-danger mt-4" role="alert">
    {{ $reg_error }}
  </div>
  @endif

  <h1>注册用户</h1>

  <form class="" action="{{ action('PublicController@register') }}" method="post">
    @csrf
    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">用户名</span>
      </div>
      <input type="text" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1" name="username">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">密码</span>
      </div>
      <input type="password" class="form-control" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1" name="password">
    </div>

    <div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text">确认密码</span>
      </div>
      <input type="password" class="form-control" placeholder="Password" aria-label="Password" aria-describedby="basic-addon1" name="comfirm">
    </div>

    <p>
      <input class="btn btn-primary float-right" type="submit" value="提交">
    </p>
  </form>
@endsection
