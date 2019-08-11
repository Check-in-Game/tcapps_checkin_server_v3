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
  <!-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/checkin-static/assets/stisla/2.2.0-modified/css/selectric.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/checkin-static/assets/stisla/2.2.0-modified/css/style.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/checkin-static/assets/stisla/2.2.0-modified/css/components.min.css">
  <script src="{{ asset('js/app.js') }}" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-lazy@1.7.10/jquery.lazy.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/checkin-static/assets/stisla/2.2.0-modified/js/jquery.selectric.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/checkin-static/assets/stisla/2.2.0-modified/js/moment.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/checkin-static/assets/stisla/2.2.0-modified/js/stisla.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/checkin-static/assets/stisla/2.2.0-modified/js/scripts.min.js" charset="utf-8"></script>
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
            <li class="menu-header">个人中心</li>
            <li><a class="nav-link" href="{{ action('UserController@user') }}"><i class="fa-fw fas fa-user-circle"></i> <span>用户面板</span></a></li>
            <li><a class="nav-link" href="{{ action('UserController@user_resources') }}"><i class="fa-fw fas fa-box-open"></i> <span>我的资源</span></a></li>
            <li class="menu-header">资源管理</li>
            <li><a class="nav-link" href="{{ action('UserController@worker') }}"><i class="fa-fw fas fa-tools"></i> <span>Worker</span></a></li>
            <li><a class="nav-link" href="{{ action('UserController@blend') }}"><i class="fa-fw fas fa-mortar-pestle"></i> <span>合成中心</span></a></li>
            <li><a class="nav-link" href="{{ action('UserController@recycle') }}"><i class="fa-fw fas fa-recycle"></i> <span>回收中心</span></a></li>
            <li><a class="nav-link" href="{{ action('UserController@market') }}"><i class="fa-fw fas fa-money-check"></i> <span>交易市场</span></a></li>
            <li><a class="nav-link" href="{{ action('UserController@shop') }}"><i class="fa-fw fas fa-store-alt"></i> <span>资源商城</span></a></li>
            <li><a class="nav-link" href="{{ action('UserController@gifts_reedem') }}"><i class="fa-fw fas fa-gift"></i> <span>礼包兑换</span></a></li>
            <li class="menu-header">基金会</li>
            <li class="dropdown">
              <a href="javascript:;" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa-fw fas fa-angle-double-up"></i> <span>科技升级</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ action('UserController@worker_upgrade') }}"><i class="fa-fw fas fa-tools"></i> Worker升级</a></li>
              </ul>
            </li>
            <li><a class="nav-link" href="{{ action('FoundationController@discuss') }}"><i class="fa-fw fas fa-comments"></i> <span>议事大厅</span></a></li>
            <!-- <li class="dropdown">
              <a href="javascript:;" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fa-fw fas fa-keyboard"></i> <span>办公中心</span></a>
              <ul class="dropdown-menu">
                <li><a class="nav-link" href="{{ action('FoundationController@recruit') }}"><i class="fa-fw fas fa-file-invoice"></i> <span>招募计划</span></a></li>
              </ul>
            </li> -->
          </ul>

          <div class="mt-4 mb-4 p-3 hide-sidebar-mini">
            <a href="https://checkin-docs.twocola.com" target="_blank" class="btn btn-success btn-lg btn-block btn-icon-split">
              <i class="fas fa-book"></i> 手册 / Manual
            </a>
          </div>
          @if(isset($_admin) && $_admin && $_admin->level > 0)
          <div class="mt-4 mb-4 p-3 hide-sidebar-mini">
            <a href="{{ action('AdminController@index') }}" target="_self" class="btn btn-danger btn-lg btn-block btn-icon-split">
              <i class="fas fa-plane"></i> 管理中心
            </a>
          </div>
          @endif
        </aside>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>@yield('header')</h1>
            @yield('breadcrumb')
          </div>
          <div class="section-body">
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
function m_tip(text, color='info', id='_tips', position='bottomRight', timeout=5000) {
  switch (color) {
    case 'info':
      iziToast.info({
        id: id,
        message: text,
        position: position,
        timeout: timeout
      });
      break;
    case 'success':
      iziToast.success({
        id: id,
        message: text,
        position: position,
        timeout: timeout
      });
      break;
    case 'warning':
      iziToast.warning({
        id: id,
        message: text,
        position: position,
        timeout: timeout
      });
      break;
    case 'danger':
      iziToast.error({
        id: id,
        message: text,
        position: position,
        timeout: timeout
      });
      break;
    case 'error':
      iziToast.error({
        id: id,
        message: text,
        position: position,
        timeout: timeout
      });
      break;
    default:
      iziToast.info({
        id: id,
        message: text,
        position: position,
        timeout: timeout
      });
      break;
  }
  return id;
}
function m_tip_close(id) {
  iziToast.hide({}, document.querySelector('#' + id));
}
function logout() {
  $.getJSON('/api/logout', function(){
    location.href = '';
  });
}
</script>
@yield('script')
</body>
</html>
