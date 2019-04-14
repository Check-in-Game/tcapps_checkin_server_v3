@extends('admin/master')
@section('container')
<!-- 公告-18 -->
@foreach($_notices as $notice)
<div class="alert alert-{{ $notice['color'] }}" role="alert">
  @if (!empty($notice['title']))
  <h4 class="alert-heading">{{ $notice['title'] }}</h4>
  @endif
  {{ $notice['content'] }}
</div>
@endforeach

<h2>用户一览 / All Users</h2>
<div class="row">

  <table class="table table-striped table-hover text-center">
    <thead>
      <tr>
        <th scope="col">UID</th>
        <th scope="col">Username</th>
        <th scope="col">状态</th>
      </tr>
    </thead>
    <tbody>
        @foreach ($charts as $key => $chart)
          <tr>
            <th scope="row">
              #{{ $chart->uid }}
            </th>
            <th scope="row">
              {{ $chart->username }}
            </th>
            <th scope="row">
              {{ $chart->status }}
            </th>
          </tr>
        @endforeach
      </tr>
    </tbody>
  </table>

</div>

<nav style="text-align: center">
  {{ $charts->links() }}
</nav>
@endsection
