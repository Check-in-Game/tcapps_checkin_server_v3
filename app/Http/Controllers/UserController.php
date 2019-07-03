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
      $point = DB::table('v3_user_point')
              ->where('uid', $user->uid)
              ->value('point');
      $point = $point ? $point : 0;
      // 基础资源信息 comber
      $combers = DB::table('v3_items')
                ->whereIn('iid', [1,2,3,4,5])
                ->get();
      // 查询资源物品
      $items = DB::table('v3_user_items')
              ->where('uid', $user->uid)
              ->value('items');
      // 清灰时间结算
      $clean = DB::table('v3_clean_list')
            ->where('uid', $user->uid)
            ->first();
      if ($clean && $clean->check_time >= date('Y-m-d 00:00:00')) {
        $clean = strtotime(date('Y-m-d 23:59:59')) - time();
      }else{
        $clean = 0;
      }
      $data = [
        'uid'          => $uid,
        'username'     => $username,
        'combers'      => $combers,
        'point'        => $point,
        'items'        => json_decode($items, true),
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
