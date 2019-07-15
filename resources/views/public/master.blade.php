@extends('master_panel')
@section('script')
<script type="text/javascript">
  $(function(){
    $("img.lazy").Lazy({
      effect: 'fadeIn',
      effectTime: 500
    });
    $("div.lazy").Lazy({
      effect: 'fadeIn',
      effectTime: 500
    });
  });
</script>
@endsection
