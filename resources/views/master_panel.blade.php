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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js" charset="utf-8"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-lazy@1.7.10/jquery.lazy.min.js" charset="utf-8"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js" charset="utf-8"></script>
    @yield('meta')
  </head>
  <body>

    <!-- 导航条 -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
      <a class="navbar-brand" href="{{ action('PublicController@index') }}">
        <img src="{{ asset('favicon.ico') }}" width="30" height="30" class="d-inline-block align-top" alt="">
        Check-in Center <span class="badge badge-primary">v3</span>
      </a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNavDropdown">
        <ul class="navbar-nav mr-md-0 ml-sm-auto ml-md-auto d-md-flex text-center">
          @if(isset($_COOKIE['auth']))
          <li class="nav-item">
            <a class="nav-link" href="{{ action('UserController@user') }}"><i class="fa-fw fas fa-user-circle"></i> 用户</a>
          </li>
          @else
          <li class="nav-item">
            <a class="nav-link" href="{{ action('PublicController@login') }}"><i class="fa-fw fas fa-user"></i> 登录</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ action('PublicController@register') }}"><i class="fa-fw fas fa-user-edit"></i> 注册</a>
          </li>
          @endif
          @if(isset($_admin) && $_admin && $_admin->level > 0)
          <li class="nav-item">
            <a class="nav-link" href="{{ action('AdminController@index') }}"><i class="fa-fw fas fa-magic"></i> 管理</a>
          </li>
          @endif
          <li class="nav-item">
            <a class="nav-link" href="https://checkin-docs.twocola.com" target="_blank"><i class="fa-fw fas fa-book"></i> 手册</a>
          </li>
          @if(isset($_COOKIE['auth']))
          <li class="nav-item">
            <a class="nav-link" href="javascript:logout();" target="_blank"><i class="fa-fw fas fa-sign-out-alt"></i> 注销</a>
          </li>
          @endif
        </ul>
      </div>
    </nav>

    @yield('headerExtraContent')

    @yield('container')

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
    // 清理modal-alert-content中的class
      $(function(){
        $('#modal-alert').on('hidden.bs.modal', function (e) {
          $('#modal-alert-content').removeClass();
          $('#modal-alert-content').addClass('alert m-0 text-center');
        })
      });
      function logout() {
        $.getJSON('/api/logout', function(){
          location.href = '';
        });
      }
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
        $('#modal-alert-content').addClass('alert-' + color);
        $('#modal-alert-content').text(text);
        $('#modal-alert').modal('show');
      }
    </script>
    @yield('script')
  </body>
</html>
