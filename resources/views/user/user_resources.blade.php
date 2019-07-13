@extends('user/master')
@section('header')
我的资源
@endsection
@section('body')
<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th scope="col">图标</th>
      <th scope="col">名称</th>
      <th scope="col">描述</th>
      <th scope="col">数量</th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $item)
    <tr>
      <td><img class="lazy" src="{{ asset('img/loading.svg') }}" data-src="{{ $_system['cdn_prefix'] }}{{ $item->image }}" alt="{{ $item->iname }}" height="18x;"></td>
      <td>{{ $item->iname }}</td>
      <td class="text-truncate" title="{{ $item->description }}">{{ $item->description }}</td>
      <td>{{ isset($user_items[$item->iid]['count']) ? $user_items[$item->iid]['count'] : 0 }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
@section('script')
@endsection
