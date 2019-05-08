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
      $count = DB::table('lists_v2')
              ->where('uid', $user->uid)
              ->count();
      $buff = DB::table('system')
              ->where('skey', 'newhand_support_pre_200')
              ->first();
      $buff = $buff ? $buff->svalue : 1;
      $cost = DB::table('purchase_records')
            ->where('uid', $user->uid)
            ->sum('cost');
      // 清灰时间结算
      $clean = DB::table('lists_v2')
            ->where('uid', $user->uid)
            ->where('tid', 7)
            ->where('check_time', '>=', date('Y-m-d 00:00:00'))
            ->where('check_time', '<=', date('Y-m-d 23:59:59'))
            ->first();
      if (!$clean) {
        $clean = 0;
      }else{
        $clean = strtotime(date('Y-m-d 23:59:59')) - time();
      }
      $data = [
        'uid'          => $uid,
        'username'     => $username,
        'all_worth'    => $all_worth,
        'count'        => $count,
        'buff'         => $buff,
        'cost'         => $cost,
        'clean'        => $clean
      ];
      return view('user.home', $data);
    }

    // 兑换中心
    public function shop() {
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      $username = $user->username;
      $db_prefix = env('DB_PREFIX');
      // 读取商品
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
      $all_worth = DB::table('lists_v2')
                ->where('uid', $user->uid)
                ->sum('worth');
      $cost = DB::table('purchase_records')
            ->where('uid', $user->uid)
            ->sum('cost');
      $data = [
        'uid'             => $uid,
        'username'        => $username,
        'goods'           => $shop,
        'balance'         => $all_worth - $cost
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

    // 积分账单
    public function bill() {
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      $charts = DB::table('purchase_records')
              ->where('uid', $user->uid)
              ->orderBy('purchase_time', 'desc')
              ->paginate(25);
      $data = [
        'charts'  => $charts
      ];
      return view('user.bill', $data);
    }

    // 活动一览
    public function activity() {
      $charts = DB::table('activity')
          ->where('endtime', '>=', date('Y-m-d H:i:s', strtotime('-7 day')))
          ->where('starttime', '<=', date('Y-m-d H:i:s', strtotime('+7 day')))
          ->where('status', 1)
          ->orderBy('starttime', 'desc')
          ->paginate(25);
      $data = [
        'charts'  => $charts
      ];
      return view('user.activity', $data);
    }

    // 勋章一览
    public function badges() {
      $uid = request()->cookie('uid');
      $charts = DB::table('shop')
          ->join('purchase_records', 'shop.gid', '=', 'purchase_records.gid')
          ->join('badges', 'shop.gid', '=', 'badges.gid')
          ->join('effects', 'badges.eid', '=', 'effects.eid')
          ->where('shop.tid', 1)
          ->where('purchase_records.uid', $uid)
          ->select('badges.bid as bid',
                  'badges.bname as bname',
                  'badges.image as image',
                  'badges.fgcolor as fgcolor',
                  'badges.bgcolor as bgcolor',
                  'effects.times as times',
                  'purchase_records.purchase_time as purchase_time',
                  'purchase_records.status as status')
          ->paginate(25);
      $wear = DB::table('badges_wear')->where('uid', $uid)->first();
      if (!$wear) {
        $wear = [];
      }else {
        $wear = explode(',', $wear->bid);
      }
      $limit = DB::table('system')->where('skey', 'badges_wear_limit')->first();
      $limit = !$limit ? 1 : $limit->svalue;
      $data = [
        'charts'  => $charts,
        'wear'    => $wear,
        'limit'   => $limit
      ];
      return view('user.badges', $data);
    }

    // 修改用户名
    public function username_modify() {
      return view('user.username_modify');
    }
}
