@extends('user/master')
@section('header')
用户面板
@endsection
@section('body')
<div class="row">
  <!-- 基本信息 -->
  <div class="col-md-4 col-sm-12 mb-3 text-center">
    <div class="card border-dark mb-3">
      <div class="card-header">
        基本信息
      </div>
      <div class="card-body text-dark">
        <div class="row">
          <!-- UID -->
          <div class="col-6 text-right mb-1 font-weight-bold">UID：</div>
          <div class="col-6 text-left mb-1">{{ $uid }}</div>
          <!-- 积分 -->
          <div class="col-6 text-right mb-1 font-weight-bold">积分：</div>
          <div class="col-6 text-left mb-1">{{ $point }}</div>
        </div>
      </div>
    </div>
  </div>

  <!-- 获取积分 -->
  <div class="col-md-4 col-sm-12 mb-3 text-center">
    <div class="card border-dark mb-3">
      <div class="card-header">
        获取资源
      </div>
      <div class="card-body text-dark">
        <button class="btn btn-success btn-block" href="javascript:;" data-toggle="modal" data-target="#clean" id='btn_clean'><i class="fa-fw fas fa-broom"></i> 立即擦灰</button>
      </div>
    </div>
  </div>

  <!-- 账户安全 -->
  <div class="col-md-4 col-sm-12 mb-3 text-center">
    <div class="card border-dark mb-3">
      <div class="card-header">
        账户安全
      </div>
      <div class="card-body text-dark">
        <a class="btn btn-danger btn-block" href="{{ action('UserController@security_change_password') }}"><i class="fa-fw fas fa-pen-square"></i> 修改密码</a>
      </div>
    </div>
  </div>

</div>

<!-- Combers -->
<div class="row">
  @foreach($combers as $comber)
  <div class="col-lg-3 col-md-6 col-sm-6 col-12">
    <div class="card card-statistic-1">
      <div class="card-icon bg-white lazy" style="background: url('{{ asset('img/loading.svg') }}') no-repeat center center;" data-src="{{ $_system['cdn_prefix'] }}{{ $comber->image }}">
      </div>
      <div class="card-wrap">
        <div class="card-header">
          <h4>{{ $comber->iname }}</h4>
        </div>
        <div class="card-body">
          {{ isset($items[$comber->iid]['count']) ? $items[$comber->iid]['count'] : 0 }} C
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>

<div class="alert alert-info" role="alert">
  <h4 class="alert-heading">更快的获取积分</h4>
  <p>
    您可以在<a class="alert-link" href="{{ action('PublicController@index') }}" target="_self">首页</a>加入QQ交流群获取一手活动预告信息，您也可以在用户中心的活动中心查询活动一览以更好的把握时机。
  </p>
  <p>
    如果您在使用过程中发现Bug或由其他的意见和建议，加入QQ群向群主反映（Bug请私聊），会有丰厚的礼包奖励。
  </p>
  <p>
    QQ群会不定时发放礼包，礼包内容包含一系列增益道具、直接积分奖励等。
  </p>
  <p>
    游戏是在不断更新的，更多玩法正在紧锣密鼓的布置中，为了更好的游戏体验，欢迎加入QQ群共同讨论、建设、开发。
  </p>
</div>
@endsection
@section('extraModalContent')
<div class="modal fade" id="clean" tabindex="0" role="dialog" aria-labelledby="clean-title" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-body">
        <div class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">验证码</span>
          </div>
          <input type="text" class="form-control" placeholder="Captcha" id="captcha" maxlength="6">
          <div class="input-group-append">
            <img src="{{ captcha_src() }}" alt="captcha" onclick="this.src='{{ captcha_src() }}' + Math.random();" id="captcha_img">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success btn-block m-auto" onclick="javascript:clean();"><i class="fa-fw fas fa-broom"></i> 擦</button>
      </div>
    </div>
  </div>
</div>
@endsection
@section('script')
<script type="text/javascript">
  $(function(){
    let clean_rest_time = {{ $clean }};
    let btn_clean_text = $('#btn_clean').html();
    $('#btn_clean').attr('disabled', 'disabled');
    setTimeout("$('#btn_clean').removeAttr('disabled');", (clean_rest_time + 1) * 1000);
    let interval_btn_clean = setInterval(function(){
      let clean_rest_hour = Math.floor(clean_rest_time / 3600);
      let clean_rest_min = Math.floor(clean_rest_time / 60) - clean_rest_hour * 60;
      let clean_rest_sec = clean_rest_time - clean_rest_min * 60 - clean_rest_hour * 3600;
      if (clean_rest_hour == 0) {
        if(clean_rest_min == 0) {
          $('#btn_clean').text(clean_rest_sec + '秒后可擦灰');
        }else{
          $('#btn_clean').text(clean_rest_min + '分钟后可擦灰');
        }
      }else{
        $('#btn_clean').text(clean_rest_hour + '小时后可擦灰');
      }
      clean_rest_time --;
      if (clean_rest_time <= 0) {
        clearInterval(interval_btn_clean);
        $('#btn_clean').html(btn_clean_text);
      }
    }, 1000);
  });
  function clean() {
    $('#clean').modal('hide');
    let captcha = $('#captcha').val();
    m_loading();
    $.ajax({
      url: '/api/user/checkin/clean',
      type: 'post',
      data: {
        'captcha' : captcha
      },
      dataType: 'json',
      timeout: 10000,
      complete: function(XMLHttpRequest, status){
        m_loading(false);
        if (status == 'timeout') {
          m_alert('连接超时！', 'danger');
        }
      },
      success: function(data){
        $('#captcha_img').click();
        $('#captcha').val('');
        if (data.errno === 0) {
          $('#btn_clean').attr('disabled', 'disabled');
          $('#btn_clean').text('已经签到过啦~');
          msg = '签到成功：获得' + data.body.data.point + '积分';
          if (data.body.data.comber != -1) {
            switch (data.body.data.comber) {
              case 1:
                comber_color = '粉色';
                break;
              case 2:
                comber_color = '蓝色';
                break;
              case 3:
                comber_color = '绿色';
                break;
              case 4:
                comber_color = '黄色';
                break;
            }
            msg += '，与1个' + comber_color + '可莫尔碎片';
          }
          msg += '！';
          m_alert(msg, 'success');
        }else if(data.errno === 3901 || data.errno === 3902){
          m_alert('签权可能过期了哟，重新登录下吧~', 'warning');
        }else if(data.errno === 3903){
          m_alert('您的账户状态异常', 'danger');
        }else if(data.errno === 3904){
          m_alert('不要急哟，一天只能擦一次哟', 'danger');
        }else if(data.errno === 3906){
          m_alert('验证码填写错误辣！', 'danger');
        }else{
          m_alert('未知错误：' + data.errno, 'danger');
        }
      }
    });
  }
</script>
@endsection
