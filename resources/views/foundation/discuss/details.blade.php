@extends('user/master')
@section('header')
议事大厅
@endsection
@section('body')
<div class="mb-4 clearfix">
  <a class="btn btn-primary float-left" href="{{ action('FoundationController@discuss') }}">
    <span class="fas fa-fw fa-chevron-left"></span> 返回大厅
  </a>
  <a class="btn btn-warning float-right" href="#comment">
    <span class="fas fa-fw fa-angle-down"></span>
  </a>
</div>
@if(!$discussion)
<div class="alert alert-primary">
  这个话题似乎不存在呢~
</div>
@else
<div class="card">
  <div class="card-header">
    @if ($discussion->tid == 1)
    <img class="mr-3 lazy mb-0"
    data-src="{{ $_system['cdn_prefix'] }}/cdn/v3/foundation/discuss/idea.svg"
    src="{{ asset('img/loading-bar.svg') }}" height="24px">
    @elseif ($discussion->tid == 2)
    <img class="mr-3 lazy mb-0"
    data-src="{{ $_system['cdn_prefix'] }}/cdn/v3/foundation/discuss/question.svg"
    src="{{ asset('img/loading-bar.svg') }}" height="24px">
    @elseif ($discussion->tid == 3)
    <img class="mr-3 lazy mb-0"
    data-src="{{ $_system['cdn_prefix'] }}/cdn/v3/foundation/discuss/bug.svg"
    src="{{ asset('img/loading-bar.svg') }}" height="24px">
    @endif
    <h5 class="pt-2" id="title">
      {{ $discussion->topic }}
      <span class="text-muted">#{{ $discussion->did }}</span>
    </h5>
  </div>
  <div class="card-body">
    <ul class="list-unstyled list-unstyled-border list-unstyled-noborder">
      @foreach($comments as $comment)
      <li class="media my-4 py-4 rounded-right" style="background-color: #f1f1f1;">
        @if($comment->uid === $discussion->uid)
          <div class="media-body border-left border-primary px-4 ">
        @else
          <div class="media-body border-left border-success px-4">
        @endif
          <div class="media-description mb-2 text-dark" style="font-size: 15px; line-height: 26px;">
            <h6 class="text-primary">{{ $comment->nickname }} :</h6>
            @php
              $_comments = explode("\n", $comment->content)
            @endphp
            @foreach($_comments as $_comment)
              {{ $_comment }}
              <br />
            @endforeach
          </div>
          <div class="text-time mb-0">
            <span class="badge badge-success rounded-right p-1" title="回复ID" data-toggle="tooltip">{{ $comment->post_id }}</span>
            |
            {{ $comment->create_at }}
          </div>
          <!-- <div class="media-links">
            <a href="#">编辑</a>
            <div class="bullet"></div>
            <a href="#" class="text-danger">删除</a>
          </div> -->
        </div>
      </li>
      @endforeach
    </ul>
    <hr class="my-4" />
    <input type="hidden" id="did" value="{{ $discussion->did }}">
    @if($discussion->status != 3)
      <!-- 评论 -->
      <div class="form-group row mb-4" id="comment">
        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3">评论</label>
        <div class="col-sm-12 col-md-7">
          <textarea class="form-control" style="height: 120px;" id="content"></textarea>
        </div>
      </div>
      <div class="form-group row mb-4">
        <label class="col-form-label text-md-right col-12 col-md-3 col-lg-3"></label>
        <div class="col-sm-12 col-md-7">
          <span class="text-muted" style="line-height: 36px;">请不要发布任何违反当地与中国法律的内容。</span>
          <button class="btn btn-primary float-right" onclick="javascript:post_comment();">提交</button>
        </div>
      </div>
      <!-- ./评论 -->
      <!-- 设置 -->
      @if($discussion->uid == $_user->uid)
        <hr>
        <div class="my-4 text-center clearfix">
          <a class="btn btn-primary float-left" href="{{ action('FoundationController@discuss') }}">
            <span class="fas fa-fw fa-chevron-left"></span> 返回大厅
          </a>
          <div class="btn-group">
            <button class="btn btn-danger my-1" onclick="close_discuss();">关闭讨论</button>
          </div>
          <a class="btn btn-warning float-right" href="#title">
            <span class="fas fa-fw fa-angle-up"></span>
          </a>
        </div>
      @endif
      <!-- ./设置 -->
    @else
      <div class="text-center">
        <span class="text-muted" style="line-height: 36px;">该议项已经关闭，无法回复。</span>
      </div>
    @endif
    @if($_admin)
      <hr>
      <div class="my-4 text-center">
        <div class="btn-group">
          <button class="btn btn-success my-1" onclick="javascript:change_status(1);">讨论</button>
          <button class="btn btn-warning my-1" onclick="javascript:change_status(2);">跟进</button>
          <button class="btn btn-danger my-1" onclick="javascript:change_status(3);">关闭</button>
          <button class="btn btn-primary my-1" onclick="javascript:change_status(-1);">违规</button>
        </div>
      </div>
    @endif
  </div>
</div>
@endif
@endsection
@section('extraModalContent')

@endsection
@section('script')
<script type="text/javascript">
@if($_admin)
function change_status(status) {
  let did = $('#did').val();
  m_loading();
  $.ajax({
    url: '/api/admin/foundation/discuss/setStatus',
    type: 'post',
    dataType: 'json',
    data: {
      'did': did,
      'status': status
    },
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status === 'timeout') {
        m_alert('响应超时，请稍候再试！');
      }
    },
    success: function(data){
      if (data.errno == 0) {
        location.href='';
      }else{
        info = data.errno + ': ' + $data.error;
        m_alert(info, 'danger');
      }
    }
  });
}

@endif
function post_comment() {
  let did = $('#did').val();
  let content = $('#content').val();
  if (content == '') {
    m_alert('请填写内容');
    return false;
  }
  m_loading();
  $.ajax({
    url: '/api/foundation/discuss/comment',
    type: 'post',
    dataType: 'json',
    data: {
      'did': did,
      'content': content
    },
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status === 'timeout') {
        m_alert('响应超时，请稍候再试！');
      }
    },
    success: function(data){
      if (data.errno == 0) {
        location.href='';
      }else{
        if (data.errno == 6001) {
          info = "您的账户仍然处于实习期（一天）或状态异常，暂时无法回复话题。";
        }else if(data.errno == 6002) {
          info = "距离上次回复不足30秒！";
        }else if(data.errno == 6004) {
          info = "禁止发送重复评论！";
        }else{
          info = "系统繁忙，请稍候再试！";
        }
        m_alert(info, 'danger');
      }
    }
  });
}
function close_discuss() {
  let did = $('#did').val();
  m_loading();
  $.ajax({
    url: '/api/foundation/discuss/close',
    type: 'post',
    dataType: 'json',
    data: {
      'did': did
    },
    timeout: 10000,
    complete: function(XMLHttpRequest, status){
      m_loading(false);
      if (status === 'timeout') {
        m_alert('响应超时，请稍候再试！');
      }
    },
    success: function(data){
      if (data.errno == 0) {
        location.href='';
      }else{
        if (data.errno == 6101) {
          info = "DID不合法！";
        }else if(data.errno == 6102) {
          info = "话题已经关闭或该话题不属于您！";
        }else{
          info = "系统繁忙，请稍候再试！";
        }
        m_alert(info, 'danger');
      }
    }
  });
}
</script>
@endsection
