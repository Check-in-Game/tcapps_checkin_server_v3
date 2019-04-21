@extends('admin/master')
@section('container')
<!-- 公告-23 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>效果一览 / Effects</h2>
<table class="table table-striped table-hover text-center">
  <thead>
    <tr>
      <th scope="col">效果ID</th>
      <th scope="col">倍率</th>
      <th scope="col">描述</th>
      <th scope="col">状态</th>
    </tr>
  </thead>
  <tbody>
      @foreach ($charts as $key => $chart)
        <tr>
          <th scope="row">
            #{{ $chart->eid }}
          </th>
          <th scope="row">
            {{ $chart->times }}
          </th>
          <th scope="row">
            {{ $chart->description }}
          </th>
          <th scope="row">
            @if( $chart->status === 1 )
            <span class="badge badge-success">正常</span>
            @else
            <span class="badge badge-warning">{{ $chart->status }}</span>
            @endif
          </th>
        </tr>
      @endforeach
    </tr>
  </tbody>
</table>
<nav style="text-align: center">
  {{ $charts->links() }}
</nav>
@endsection
