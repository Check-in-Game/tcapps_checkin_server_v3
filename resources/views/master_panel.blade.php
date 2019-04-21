<!DOCTYPE html>
<html lang="zh-CN" dir="ltr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,height=device-height,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <title>Check-in Game</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://cdn.bootcss.com/font-awesome/5.8.1/css/all.min.css" rel="stylesheet">
    <script src="{{ asset('js/app.js') }}" charset="utf-8"></script>
  </head>
  <body>

    <!-- 导航条 -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
      <div class="container">
        <a class="navbar-brand" href="{{ action('UserController@user') }}">
          <img src="{{ asset('favicon.ico') }}" width="30" height="30" class="d-inline-block align-top" alt="">
          Check-in Center
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <a class="nav-link" href="{{ action('PublicController@index') }}">首页</a>
            </li>
            <!-- 用户中心 -->
            <li class="nav-item dropdown">
             <a class="nav-link dropdown-toggle" href="javascript:;" id="user" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <i class="fa-fw fas fa-user-circle"></i> 用户
             </a>
             <div class="dropdown-menu" aria-labelledby="user">
               <a class="dropdown-item" href="{{ action('UserController@user') }}"><i class="fa-fw fas fa-user-tie"></i> 用户中心</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">签到与积分</h6>
               <a class="dropdown-item" href="{{ action('UserController@shop') }}"><i class="fa-fw fas fa-gifts"></i> 兑换中心</a>
               <a class="dropdown-item" href="{{ action('UserController@bill') }}"><i class="fa-fw fas fa-coins"></i> 积分账单</a>
               <a class="dropdown-item" href="{{ action('UserController@history_checkin') }}"><i class="fa-fw fas fa-clipboard-list"></i> 签到记录</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">活动</h6>
               <a class="dropdown-item" href="{{ action('UserController@activity') }}"><i class="fa-fw fab fa-slack"></i> 活动中心</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">勋章</h6>
             </div>
           </li>
           @if($_admin)
           <!-- 管理中心 -->
           <li class="nav-item dropdown">
             <a class="nav-link dropdown-toggle" href="javascript:;" id="admin" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               <i class="fa-fw fas fa-magic"></i> 管理
             </a>
             <div class="dropdown-menu" aria-labelledby="user">
               <a class="dropdown-item" href="{{ action('AdminController@index') }}"><i class="fa-fw fas fa-hat-wizard"></i> 管理中心</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">积分</h6>
               <a class="dropdown-item" href="{{ action('AdminController@compensate') }}"><i class="fa-fw fas fa-coins"></i> 增加积分</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">活动</h6>
               <a class="dropdown-item" href="{{ action('AdminController@activity') }}"><i class="fa-fw fab fa-slack"></i> 活动一览</a>
               <a class="dropdown-item" href="{{ action('AdminController@activity_manage') }}"><i class="fa-fw fab fa-slack-hash"></i> 管理活动</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">商店</h6>
               <a class="dropdown-item" href="{{ action('AdminController@goods') }}"><i class="fa-fw fas fa-gifts"></i>  管理商品</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">用户</h6>
               <a class="dropdown-item" href="{{ action('AdminController@users_list') }}"><i class="fa-fw fas fa-users"></i> 用户一览</a>
               <a class="dropdown-item" href="{{ action('AdminController@users_manage') }}"><i class="fa-fw fas fa-user-edit"></i> 用户管理</a>
               <a class="dropdown-item" href="{{ action('AdminController@admins_manage') }}"><i class="fa-fw fas fa-user-cog"></i> 管理提权</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">勋章</h6>
               <a class="dropdown-item" href="{{ action('AdminController@badges') }}"><i class="fa-fw fas fa-certificate"></i> 勋章一览</a>
               <a class="dropdown-item" href="{{ action('AdminController@badges_manage') }}"><i class="fa-fw fas fa-spray-can"></i> 管理勋章</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">效果</h6>
               <a class="dropdown-item" href="{{ action('AdminController@effects') }}"><i class="fa-fw fas fa-magic"></i> 效果一览</a>
               <a class="dropdown-item" href="{{ action('AdminController@effects_manage') }}"><i class="fa-fw fas fa-magic"></i> 管理效果</a>
               <div class="dropdown-divider"></div>
               <h6 class="dropdown-header">系统</h6>
               <a class="dropdown-item" href="{{ action('AdminController@notices') }}"><i class="fa-fw fas fa-th-list"></i> 公告一览</a>
               <a class="dropdown-item" href="{{ action('AdminController@notices_manage') }}"><i class="fa-fw fas fa-edit"></i> 公告管理</a>
               <a class="dropdown-item" href="{{ action('AdminController@optimize') }}"><i class="fa-fw fas fa-toolbox"></i> 系统优化</a>
             </div>
           </li>
           @endif
          </ul>
          <button class="btn btn-sm btn-outline-light" type="button" onclick="javascript:logout();">退出 / logout</button>
        </div>
      </div>
    </nav>

    @yield('headerExtraContent')

    <div class="container">

      @yield('container')

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