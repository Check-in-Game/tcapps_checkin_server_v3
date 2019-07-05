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
  @yield('meta')
</head>
  <body>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="page-error">
          <div class="page-inner">
            <h1>404</h1>
            <div class="page-description">
              页面找不到了呢 QAQ
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
