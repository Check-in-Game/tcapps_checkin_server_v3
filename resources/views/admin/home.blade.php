@extends('admin/master')
@section('container')
<!-- 公告-11 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach
<div class="alert alert-success">
  请选择需要管理的区块！
</div>
@endsection
