<!DOCTYPE html>
<html lang="zh-CN" dir="ltr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,height=device-height,initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="description" content="一款有参与感的收菜游戏。">
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link href="https://cdn.bootcss.com/font-awesome/5.8.1/css/all.min.css" rel="stylesheet">
  <title>发生错误了QAQ</title>
  <!-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/css/components.min.css">
  <link rel="stylesheet" href="{{ asset('css/stisla.css') }}">
  <script src="{{ asset('js/app.js') }}" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.4.1/dist/jquery.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-lazy@1.7.10/jquery.lazy.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery.nicescroll@3.7.6/jquery.nicescroll.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.bundle.min.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/js/stisla.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/npm/stisla@2.3.0/assets/js/scripts.js" charset="utf-8"></script>
  <link rel="stylesheet" href="https://cdn.staticfile.org/izitoast/1.4.0/css/iziToast.min.css">
  <script src="https://cdn.staticfile.org/izitoast/1.4.0/js/iziToast.min.js" charset="utf-8"></script>
  @yield('meta')
</head>
  <body>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="page-error">
          <div class="page-inner">
            <h1>500</h1>
            <div class="page-description">
              服务君跑着跑着就摔跤了 QAQ
            </div>
            <div class="my-4">
              <button class="btn btn-primary btn-lg" onclick="javascript: history.back(-1);">
                返回
              </button>
              <div class="mt-3">
                <a href="{{ action('PublicController@index') }}">首页</a>
                |
                <a href="{{ action('UserController@user') }}">用户中心</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</body>
</html>
