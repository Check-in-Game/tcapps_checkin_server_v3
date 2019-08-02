@extends('user/master')
@section('header')
邮箱验证
@endsection
@section('body')
<div class="alert alert-{{ $color }}">
  {{ $msg }}
</div>
@endsection
