@extends('user/master')
@section('header')
回收中心
@endsection
@section('body')
<div class="alert alert-success" role="alert">
  <h4 class="alert-heading">注意事项</h4>
  <p>
    回收中心以积分结算，显示的【单价】是回收单价，点击【回收】后填写回收数量确认回收即可。
    <br />
    回收操作不可撤销。
  </p>
</div>
<table class="table table-striped table-hover">
  <thead>
    <tr>
      <th scope="col">图标</th>
      <th scope="col">名称</th>
      <th scope="col">拥有</th>
      <th scope="col">单价</th>
      <th scope="col">操作</th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $item)
    <tr>
      <td><img class="lazy" src="{{ asset('img/loading.svg') }}" data-src="{{ $_system['cdn_prefix'] }}{{ $item->image }}" alt="{{ $item->iname }}" height="18x;"></td>
      <td>{{ $item->iname }}</td>
      <td>{{ $user_items[$item->iid]['count'] }}</td>
      <td>{{ $item->recycle_value }}</td>
      <td><a href="javascript:m_alert('暂未开放回收功能');">回收</a></td>
    </tr>
    @endforeach
  </tbody>
</table>
@endsection
@section('script')
@endsection
