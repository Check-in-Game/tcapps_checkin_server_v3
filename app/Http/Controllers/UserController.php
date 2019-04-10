<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserController extends Controller {
    // 用户中心
    public function user() {
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      $username = $user->username;
      // 查询积分
      $db_prefix = env('DB_PREFIX');
      $all_worth = DB::table('lists_v2')
              ->where('uid', $user->uid)
              ->sum('worth');
      $cost = DB::table('lists_v2')
              ->where('uid', $user->uid)
              ->sum('cost');
      $data = [
        'uid'          => $uid,
        'username'     => $username,
        'all_worth'    => $all_worth,
        'cost'         => $cost,
      ];
      return view('user.home', $data);
    }

    // 兑换中心
    public function shop() {
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
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
      $data = [
        'uid'             => $uid,
        'username'        => $username,
        'goods'           => $shop,
        'balance'         => $balance->balance
      ];
      return view('user.shop', $data);
    }

    // 修改密码
    public function security_change_password() {
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      $data = [
        'username'        => $user->username
      ];
      return view('user.security_change_password', $data);
    }

    // 签到历史查询
    public function history_checkin() {
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      // 读取系统设置
      $limit = DB::table('system')
                ->where('skey', 'checkin_history_limit')
                ->first();
      $unit = DB::table('system')
                ->where('skey', 'checkin_history_limit_unit')
                ->first();
      $earliest = date('Y-m-d 00:00:00', strtotime("-{$limit->svalue} {$unit->svalue}"));
      $charts = DB::table('lists_v2')
              ->where('uid', $user->uid)
              ->where('check_time', '>', $earliest)
              ->orderBy('check_time', 'desc')
              ->paginate(25);
      $data = [
        'limit'   => $limit->svalue,
        'unit'    => $unit->svalue,
        'charts'  => $charts
      ];
      return view('user.history_checkin', $data);
    }
}
