@extends('user/master')
@section('header')
招募计划
@endsection
@section('body')

<section class="section">
  <div class="section-body">
    <h2 class="section-title">“招募计划”进度</h2>
    <div class="row">
      <div class="col-12">
        <div class="activities">
          <div class="activity">
            <div class="activity-icon bg-primary text-white shadow-primary">
              <i class="fas fa-file-invoice"></i>
            </div>
            <div class="activity-detail">
              <div class="mb-2">
                <span class="text-job text-primary">招募报名</span>
                <span class="bullet"></span>
                <a class="text-job" href="javascript:;">前往</a>
              </div>
              <p>填写报名表，正式参与基金会“招募计划”。</p>
            </div>
          </div>
          <div class="activity">
            <div class="activity-icon bg-primary text-white shadow-primary">
              <i class="fas fa-user"></i>
            </div>
            <div class="activity-detail">
              <div class="mb-2">
                <span class="text-job">票选基金会·会长</span>
                <!-- <span class="bullet"></span> -->
                <!-- <a class="text-job" href="javascript:;">前往</a> -->
              </div>
              <p>从基金会·会长候选人中投票选出基金会·会长。</p>
            </div>
          </div>
          <div class="activity">
            <div class="activity-icon bg-primary text-white shadow-primary">
              <i class="fas fa-users"></i>
            </div>
            <div class="activity-detail">
              <div class="mb-2">
                <span class="text-job">票选基金会·理事</span>
                <!-- <span class="bullet"></span> -->
                <!-- <a class="text-job" href="javascript:;">前往</a> -->
              </div>
              <p>从基金会·理事候选人中投票选出基金会·理事。</p>
            </div>
          </div>
          <div class="activity">
            <div class="activity-icon bg-primary text-white shadow-primary">
              <i class="fas fa-certificate"></i>
            </div>
            <div class="activity-detail">
              <div class="mb-2">
                <span class="text-job">颁发就职勋章</span>
                <!-- <span class="bullet"></span> -->
                <!-- <a class="text-job" href="javascript:;">前往</a> -->
              </div>
              <p>系统颁发基金会·会长勋章、基金会·理事勋章并激活相关职位权限、正式开放基金会主要功能。</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
@section('extraModalContent')

@endsection
@section('script')
<script type="text/javascript">

</script>
@endsection
