<!DOCTYPE html>
<html lang="zh-CN" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,height=device-height,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="description" content="一款有参与感的收菜游戏。">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link href="https://cdn.bootcss.com/font-awesome/5.8.1/css/all.min.css" rel="stylesheet">
  <title>Check-in Game</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <link rel="stylesheet" href="{{ asset('css/stisla.css') }}">
  <script src="{{ asset('js/app.js') }}" charset="utf-8"></script>
  <script src="{{ asset('js/stisla.js') }}" charset="utf-8"></script>
  <link rel="stylesheet" href="https://cdn.staticfile.org/izitoast/1.4.0/css/iziToast.min.css">
  <script src="https://cdn.staticfile.org/izitoast/1.4.0/js/iziToast.min.js" charset="utf-8"></script>
  @yield('meta')
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <nav class="navbar navbar-expand-lg main-navbar">
        <form class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="javascript:;" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
          </ul>
        </form>
        <ul class="navbar-nav navbar-right">
          <li class="dropdown"><a href="javascript:;" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            <div class="d-sm-none d-lg-inline-block"><span><i class="fa-fw fas fa-user-circle"></i></span></div></a>
            <div class="dropdown-menu dropdown-menu-right">
              <a href="javascript:logout();" class="dropdown-item has-icon text-danger">
                <i class="fa-fw fas fa-sign-out-alt"></i> 注销
              </a>
            </div>
          </li>
        </ul>
      </nav>
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="{{ action('PublicController@index') }}">Check-in Game</a>
          </div>
          <div class="sidebar-brand sidebar-brand-sm">
            <a href="{{ action('PublicController@index') }}">
              <img src="{{ asset('favicon.ico') }}" alt="logo" height="20px">
            </a>
          </div>
          <ul class="sidebar-menu">
            <li class="menu-header">系统管理</li>
            <li><a class="nav-link" href="{{ action('AdminController@update') }}"><i class="fa-fw fas fa-toolbox"></i> <span>升级管理</span></a></li>
          </ul>

          <div class="mt-4 mb-4 p-3 hide-sidebar-mini">
            <a href="https://checkin-docs.twocola.com" target="_blank" class="btn btn-success btn-lg btn-block btn-icon-split">
              <i class="fas fa-book"></i> 手册 / Manual
            </a>
          </div>
          <div class="mt-4 mb-4 p-3 hide-sidebar-mini">
            <a href="{{ action('UserController@user') }}" target="_self" class="btn btn-primary btn-lg btn-block btn-icon-split">
              <i class="fas fa-plane"></i> 用户中心
            </a>
          </div>
        </aside>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>@yield('header')</h1>
          </div>
          <div class="section-body">
            @if(isset($_user) && $_user->status === 0)
              <div class="alert alert-danger">
                <strong>功能可能受到限制：</strong>您可能暂时无法参与任何公众功能，如签到、参与排行榜等。请您及时在用户中心<a href="{{ action('UserController@username_modify') }}" target="_self">修改用户名（或点此修改）</a>。
              </div>
            @endif
            @foreach($_notices as $notice)
            <div class="alert alert-{{ $notice['color'] }}" role="alert">
              @if (!empty($notice['title']))
              <h4 class="alert-heading">{{ $notice['title'] }}</h4>
              @endif
              {{ $notice['content'] }}
            </div>
            @endforeach
            @yield('body')
          </div>
        </section>
      </div>
      <footer class="main-footer">
        <div class="footer-left">
          <span>&copy; Copyright 2019 Check-in Game Team.</span>
        </div>
        <div class="footer-right">
          @yield('footer_left')
        </div>
      </footer>
    </div>
  </div>

<!-- modal-loading -->
<div class="modal fade" tabindex="-1" role="dialog" id="modal-loading">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="alert alert-primary m-0 text-center">
          <i class="fas fa-spinner fa-spin"></i>
          操作中，请稍候...
        </div>
      </div>
    </div>
  </div>
</div>
<!-- modal-alert -->
<div class="modal fade" tabindex="-1" role="dialog" id="modal-alert">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body p-0">
        <div class="alert m-0 text-center" id='modal-alert-content'></div>
      </div>
    </div>
  </div>
</div>
@yield('extraModalContent')
<script type="text/javascript">
$(function(){
  $("img.lazy").Lazy({
    effect: 'fadeIn',
    effectTime: 500
  });
  $("div.lazy").Lazy({
    effect: 'fadeIn',
    effectTime: 500
  });
});
function m_loading(up = true) {
  if (up === true) {
    $('#modal-loading').modal({
      'backdrop': 'static',
      'keyboard': false
    });
  }else{
    setTimeout("$('#modal-loading').modal('hide')", 500)
  }
}
function m_alert(text, color='primary') {
  $('#modal-alert-content').removeClass('alert-primary');
  $('#modal-alert-content').removeClass('alert-secondary');
  $('#modal-alert-content').removeClass('alert-success');
  $('#modal-alert-content').removeClass('alert-danger');
  $('#modal-alert-content').removeClass('alert-warning');
  $('#modal-alert-content').removeClass('alert-info');
  $('#modal-alert-content').removeClass('alert-light');
  $('#modal-alert-content').removeClass('alert-dark');
  $('#modal-alert-content').addClass('alert-' + color);
  $('#modal-alert-content').text(text);
  $('#modal-alert').modal('show');
}
function logout() {
  $.getJSON('/api/logout', function(){
    location.href = '/home';
  });
}
</script>
@yield('script')
</body>
</html>
