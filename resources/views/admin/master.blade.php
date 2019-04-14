<!DOCTYPE html>
<html lang="zh-CN" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,height=device-height,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <title>Check-in Game</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" charset="utf-8"></script>
  </head>
  <body>

    <!-- 导航条 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="{{ action('UserController@user') }}">
          <img src="{{ asset('favicon.ico') }}" width="30" height="30" class="d-inline-block align-top" alt="">
          User Center
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <a class="nav-link" href="{{ action('PublicController@index') }}">首页</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ action('UserController@user') }}">用户中心</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="{{ action('AdminController@index') }}">管理中心</a>
            </li>
          </ul>
          <button class="btn btn-sm btn-outline-light" type="button" onclick="javascript:logout();">退出 / logout</button>
        </div>
      </div>
    </nav>

    @yield('headerExtraContent')

    <div class="container">

      <div class="alert alert-danger mt-4" role="alert">
        您正处于管理模式，请谨慎操作！
      </div>

      <div class="row">

        <!-- 侧边栏 -->
        <div class="col-sm-12 col-md-2">
          <h3>积分</h3>
          <div class="row text-center">
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-primary btn-block" onclick="javascript:location.href='{{ action('AdminController@compensate') }}';">增加补偿</button>
            </div>
          </div>

          <hr />

          <h3>活动</h3>
          <div class="row text-center">
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-success btn-block" onclick="javascript:location.href='{{ action('AdminController@activity') }}';">活动一览</button>
            </div>
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-primary btn-block" onclick="javascript:location.href='{{ action('AdminController@activity_manage') }}';">管理活动</button>
            </div>
          </div>

          <hr />

          <h3>商店</h3>
          <div class="row text-center">
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-primary btn-block" onclick="javascript:location.href='{{ action('AdminController@goods') }}';">管理商品</button>
            </div>
          </div>

          <hr />

          <h3>用户</h3>
          <div class="row text-center">
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-success btn-block" onclick="javascript:location.href='{{ action('AdminController@users_list') }}';">用户一览</button>
            </div>
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-primary btn-block" onclick="javascript:location.href='{{ action('AdminController@users_manage') }}';">用户管理</button>
            </div>
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-warning btn-block" onclick="javascript:location.href='{{ action('AdminController@admins_manage') }}';">管理提权</button>
            </div>
          </div>

          <hr />

          <h3>系统</h3>

          <div class="row text-center">
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-success btn-block" onclick="javascript:location.href='{{ action('AdminController@notices') }}';">公告一览</button>
            </div>
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-primary btn-block" onclick="javascript:location.href='{{ action('AdminController@notices_manage') }}';">公告管理</button>
            </div>
            <div class="col-sm col-sm-12 mb-3">
              <button type="button" class="btn btn-danger btn-block" onclick="javascript:location.href='{{ action('AdminController@optimize') }}';">系统优化</button>
            </div>
          </div>
        </div>

        <!-- 内容 -->
        <div class="col-sm-12 col-md-10">
          @yield('container')
        </div>

      </div>

    </div>
    <script type="text/javascript">
      function logout() {
        $.getJSON('/api/logout', function(){
          location.href = '/home';
        });
      }
    </script>
    @yield('script')
  </body>
</html>
