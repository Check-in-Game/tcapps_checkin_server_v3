@extends('user/master')
@section('header')
议事大厅
@endsection
@section('body')
<div class="my-4 text-center">
  <div class="btn-group">
    <button class="btn btn-success my-1" onclick="javascript:location.href='?tid=1';">讨论中</button>
    <button class="btn btn-warning my-1" onclick="javascript:location.href='?tid=2';">跟进中</button>
    <button class="btn btn-danger my-1" onclick="javascript:location.href='?tid=3';">已关闭</button>
    <button class="btn btn-primary my-1" onclick="javascript:location.href='{{ action('FoundationController@discuss_new') }}';">创建议项</button>
  </div>
</div>
@if(count($discussions) === 0)
<div class="alert alert-primary">
  这个区域暂时没有讨论内容呢~
</div>
@endif
@foreach($discussions as $discussion)
<div class="card">
  <div class="card-body">
    <ul class="list-unstyled">
      <li class="media pt-4">
        @if ($discussion->tid == 1)
        <img class="mr-3 lazy"
          data-src="{{ $_system['cdn_prefix'] }}/cdn/v3/foundation/discuss/idea.svg"
          src="{{ asset('img/loading-bar.svg') }}" height="50px">
        @elseif ($discussion->tid == 2)
        <img class="mr-3 lazy"
          data-src="{{ $_system['cdn_prefix'] }}/cdn/v3/foundation/discuss/question.svg"
          src="{{ asset('img/loading-bar.svg') }}" height="50px">
        @elseif ($discussion->tid == 3)
        <img class="mr-3 lazy"
          data-src="{{ $_system['cdn_prefix'] }}/cdn/v3/foundation/discuss/bug.svg"
          src="{{ asset('img/loading-bar.svg') }}" height="50px">
        @endif
        <div class="media-body">
          <h5 class="mb-2">
            @if ($discussion->level == 255)
              <span class="badge badge-danger">置顶</span>
            @endif
            <a href="javascript:;" style="color:#6c757d; font-weight: bold; text-decoration: none;">
              {{ $discussion->topic }}
            </a>
            <span class="text-muted">#{{ $discussion->did }}</span>
          </h5>
          <p class="mb-0">
            <span class="text-dark">{{ $discussion->nickname }}</span> |
            {{ $discussion->create_at }} |
            <i class="fa-fw fas fa-comments"></i> {{ $comments_count[$discussion->did] }}
          </p>
        </div>
      </li>
    </ul>
  </div>
</div>
{{ $discussions->links() }}
@endforeach
@endsection
@section('extraModalContent')

@endsection
@section('script')
<script type="text/javascript">
</script>
@endsection
