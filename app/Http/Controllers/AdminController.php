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
      $activities = DB::table('activity')->orderBy('aid', 'desc')->paginate(25);
      $data = [
        'charts'  => $activities,
      ];
      return view('admin.activity', $data);
    }

    // 管理活动
    public function activity_manage() {
      return view('admin.activity_manage');
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

    // 用户一览
    public function users_list() {
      $users = DB::table('user_accounts')->paginate(25);
      $data = [
        'charts'  => $users,
      ];
      return view('admin.users', $data);
    }

    // 用户管理
    public function users_manage() {
      return view('admin.users_manage');
    }

    // 管理提权
    public function admins_manage() {
      $rights = DB::table('admin_rights_list')->get();
      $data = [
        'rights'  => $rights,
      ];
      return view('admin.admins_manage', $data);
    }
}
