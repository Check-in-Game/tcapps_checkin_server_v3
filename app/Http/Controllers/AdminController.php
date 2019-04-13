<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AdminController extends Controller {
    // 管理中心
    public function index() {
      return view('admin.home');
    }

    // 增加补偿
    public function compensate() {
      return view('admin.compensate');
    }

    // 管理活动
    public function activity() {
      return view('admin.activity');
    }

    // 管理商店
    public function goods() {
      return view('admin.goods');
    }

    // 优化页
    public function optimize() {
      return view('admin.optimize');
    }

    // 公告一览页
    public function notices() {
      $notices = DB::table('notices')->paginate();
      $data = [
        'charts'  => $notices,
      ];
      return view('admin.notices', $data);
    }

    // 公告管理页
    public function notices_manage() {
      return view('admin.notices_manage');
    }
}
