<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserController extends Controller {
    // 用户中心
    public function user() {
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      if (!$user) {
        return redirect('alert/签权错误/您的用户签权已经失效了，请重新登录！');
      }
      $username = $user->username;
      // 查询积分
      $db_prefix = env('DB_PREFIX');
      $all_worth = DB::table('lists_v2')
              ->where('uid', $user->uid)
              ->sum('worth');
      $cost = DB::table('lists_v2')
              ->where('uid', $user->uid)
              ->sum('cost');
      // 获取管理员权限
      $admin = DB::table('admin_level')
              ->where('uid', $user->uid)
              ->first();
      if ($admin) {
        $admin_level = $admin->level;
      }else{
        $admin_level = 0;
      }
      $data = [
        'uid'          => $uid,
        'username'     => $username,
        'admin_level'  => $admin_level,
        'all_worth'    => $all_worth,
        'cost'         => $cost,
      ];
      return view('user.home', $data);
    }

    // 兑换中心
    public function shop() {
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      // 顺便验证签权状态
      if (!$user) {
        return redirect('alert/签权错误/您的用户签权已经失效了，请重新登录！');
      }
      $username = $user->username;
      $db_prefix = env('DB_PREFIX');
      $shop = DB::table('shop')
              ->where('starttime' ,'<=', date('Y-m-d H:i:s'))
              ->where('endtime' ,'>=', date('Y-m-d H:i:s'))
              ->orWhere('endtime' ,'=', '1970-01-01 00:00:00')
              ->where('starttime' ,'<=', date('Y-m-d H:i:s'))
              ->where('status', 1)
              ->orderBy('gid', 'desc')
              ->get()
              ->map(function ($value) {return (array)$value;})
              ->toArray();
      foreach ($shop as $key => $value) {
        $all = DB::table('purchase_records')
              ->where('gid', $value['gid'])
              ->count();
        $userR = DB::table('purchase_records')
              ->where('gid', $value['gid'])
              ->where('uid', $user->uid)
              ->count();
        $shop[$key]['all_bought'] = $all;
        $shop[$key]['user_bought'] = $userR;
      }
      $balance = DB::table('lists_v2')
                ->where('uid', $user->uid)
                ->select(DB::raw("SUM(worth)-SUM(cost) as balance"))
                ->first();
      $admin = DB::table('admin_level')
              ->where('uid', $user->uid)
              ->first();
      if ($admin) {
        $admin_level = $admin->level;
      }else{
        $admin_level = 0;
      }
      $data = [
        'uid'             => $uid,
        'username'        => $username,
        'goods'           => $shop,
        'balance'         => $balance->balance,
        'admin_level'     => $admin_level
      ];
      return view('user.shop', $data);
    }

    // 修改密码
    public function security_change_password() {
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      // 顺便验证签权状态
      if (!$user) {
        return redirect('alert/签权错误/您的用户签权已经失效了，请重新登录！');
      }
      $admin = DB::table('admin_level')
              ->where('uid', $user->uid)
              ->first();
      if ($admin) {
        $admin_level = $admin->level;
      }else{
        $admin_level = 0;
      }
      $data = [
        'username'        => $user->username,
        'admin_level'     => $admin_level
      ];
      return view('user.security_change_password', $data);
    }
}
