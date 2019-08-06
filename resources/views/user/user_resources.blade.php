@extends('user/master')
@section('header')
我的资源
@endsection
@section('body')
<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th scope="col">图标</th>
      <th scope="col" class="d-none d-lg-table-cell">名称</th>
      <th scope="col" class="d-none d-lg-table-cell">描述</th>
      <th scope="col">通用/绑定/冻结</th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $item)
    <tr>
      <td>
        <img class="lazy" src="{{ asset('img/loading.svg') }}"
            data-src="{{ $_system['cdn_prefix'] }}{{ $item['image'] }}"
            alt="{{ $item['iname'] }}" height="18x;"
            title="{{ $item['iname'] }}" data-toggle="tooltip">
      </td>
      <td class="d-none d-lg-table-cell">{{ $item['iname'] }}</td>
      <td class="d-none d-lg-table-cell">{{ $item['description'] }}</td>
      <td>{{ $item['amount'] }} / {{ $item['locked_amount'] }} / {{ $item['frozen'] }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
@section('script')
@endsection
