@extends('public.master')
@section('headerExtraContent')
<!-- 幕布 -->
<div class="mb-0 py-4 pb-0 text-center bg-light">
  <div class="container">
    <div class="text-center">
      <img class="lazy" src="{{ asset('img/loading.svg') }}" data-src="{{ $_system['cdn_prefix'] }}/cdn/common/icons/logo_256.png" alt="logo">
    </div>
    <h1 class="display-4">Check-in Game</h1>
    <p class="lead">有参与感的收菜游戏。</p>
    <hr class="my-4">
    <p class="lead">
      @if(isset($_COOKIE['auth']))
      <a class="btn btn-primary mb-1" href="{{ action('UserController@user') }}" target="_self" role="button">用户中心</a>
      @else
      <a class="btn btn-primary mb-1" href="{{ action('PublicController@login') }}" target="_self" role="button">登录</a>
      <a class="btn btn-success mb-1" href="{{ action('PublicController@register') }}" target="_self" role="button">注册账户</a>
      @endif
      <br />
      <a class="btn btn-secondary mb-1" href="https://jq.qq.com/?_wv=1027&k=5ax4j23" target="_blank" role="button">加入交流QQ群：887304185</a>
    </p>
  </div>
</div>
@endsection
@section('container')

<div class="container">
  @foreach($_notices as $notice)
  <div class="alert alert-{{ $notice['color'] }}" role="alert">
    @if (!empty($notice['title']))
    <h4 class="alert-heading">{{ $notice['title'] }}</h4>
    @endif
    {{ $notice['content'] }}
  </div>
  @endforeach
</div>

<!-- Features -->
<section class="mt-0">
  <div class="container-fluid p-0">
    <div class="row no-gutters">
      <!-- 自由交易 -->
      <div class="col-md-6 text-white d-none d-md-block lazy" data-src="{{ $_system['cdn_prefix'] }}/cdn/v3/home/deal.jpg" style="background: no-repeat center center;background-image: url('{{ asset('img/loading.svg') }}');height: 480px;"></div>
      <div class="col-md-6 my-auto py-auto text-center" style="padding: 7rem;">
        <h2>自由交易</h2>
        <p class="lead mb-0">游戏中几乎所有的货币、道具都能进行交易。玩家的所有资源都由您自己掌握。</p>
      </div>

      <!-- 多元货币 -->
      <div class="col-md-6 my-auto py-auto text-center" style="padding: 7rem;">
        <h2>多元货币</h2>
        <p class="lead mb-0">除去官方发行的货币外，玩家可自行使用其他道具作为货币。</p>
      </div>
      <div class="col-md-6 text-white d-none d-md-block lazy" data-src="{{ $_system['cdn_prefix'] }}/cdn/v3/home/currencies.jpg" style="background: no-repeat center center;background-image: url('{{ asset('img/loading.svg') }}');height: 480px;"></div>

      <!-- 国度进化 -->
      <div class="col-md-6 text-white d-none d-md-block lazy" data-src="{{ $_system['cdn_prefix'] }}/cdn/v3/home/co-workers.jpg" style="background: no-repeat center center;background-image: url('{{ asset('img/loading.svg') }}');height: 480px;"></div>
      <div class="col-md-6 my-auto py-auto text-center" style="padding: 7rem;">
        <h2>国度进化</h2>
        <p class="lead mb-0">游戏会预先开发更多功能，玩家需要团结一致，共同推进发展研究，解锁新功能与道具。</p>
      </div>
    </div>
  </div>
</section>

<section class="py-4 text-center bg-dark text-light">
  <div class="container my-4 py-4">
    <h2 class="my-4">准备好了吗？</h2>
    <a type="button" class="btn btn-primary btn-lg" href="{{ action('PublicController@register') }}">立刻注册</a>
  </div>
</section>

<footer class="py-4 px-4 bg-light">
  <div class="container">
    <div class="row">
      <div class="col-sm-12 col-md-4">
        <h4>联系我们</h4>
        <p>
          官方QQ群：887304185
          <br />
          意见或建议提交：jokin@twocola.com
        </p>
      </div>
      <div class="col-sm-12 col-md-4">
        <h4>友情链接</h4>
        <p>
          <a class="text-dark" href="http://tcapps.twocola.com/" target="_blank">可乐趣玩</a>
        </p>
      </div>
      <div class="col-sm-12 col-md-4">
        <h4>鸣谢</h4>
        <p>
          <a class="text-dark" href="https://netlify.com" target="_blank">Netlify</a>
          <br>
          <a class="text-dark" href="https://jsDelivr.com" target="_blank">jsDelivr</a>
        </p>
      </div>
    </div>
    <div class="text-muted">
      <small>&copy; Copyright 2019 Check-in Game Team.</small>
      <br>
      <small class="text-small">
        <a class="text-muted" href="http://www.beian.miit.gov.cn" target="_blank">沪ICP备18039982号-1</a>
      </small>
    </div>
  </div>
</footer>
@endsection
