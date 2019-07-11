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
      $cols = [
        'v3_shop.cid',
        'v3_shop.iid',
        'v3_shop.cost',
        'v3_shop.starttime',
        'v3_shop.endtime',
        'v3_shop.sid',
        'v3_shop.all_count',
        'v3_shop.rebuy',
        'v3_shop.onsale',
        'v3_shop.sale_starttime',
        'v3_shop.sale_endtime',
        'v3_shop.sale_cost',
        'v3_shop.description',
        'v3_shop.status',
        'v3_items.iname',
        'v3_items.tid',
        'v3_items.image',
        'v3_items.description as item_description',
      ];
      $shop = DB::table('v3_shop')
              ->join('v3_items', 'v3_shop.iid', '=', 'v3_items.iid')
              ->where('starttime' ,'<=', date('Y-m-d H:i:s'))
              ->where('endtime' ,'>=', date('Y-m-d H:i:s'))
              ->orWhere('endtime' ,'=', '1970-01-01 00:00:00')
              ->where('starttime' ,'<=', date('Y-m-d H:i:s'))
              ->where('v3_shop.status', 1)
              ->orderBy('v3_shop.cid', 'asc')
              ->select($cols)
              ->get()
              ->map(function ($value) {return (array)$value;})
              ->toArray();
      foreach ($shop as $key => $value) {
        // 总销售量
        $all = DB::table('v3_purchase_records')
              ->where('iid', $value['iid'])
              ->sum('item_count');
        // 当前用户购买量
        $userR = DB::table('v3_purchase_records')
              ->where('iid', $value['iid'])
              ->where('uid', $user->uid)
              ->sum('item_count');
        $shop[$key]['all_bought'] = $all;
        $shop[$key]['user_bought'] = $userR;
      }
      $data = [
        'uid'             => $uid,
        'username'        => $username,
        'goods'           => $shop,
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

    // 我的资源
    public function user_resources() {
      $uid = request()->cookie('uid');
      // 查询资源
      $resources = DB::table('v3_user_items')
                  ->where('uid', $uid)
                  ->sharedLock()
                  ->value('items');
      $items = [];
      if ($resources) {
        $resources = json_decode($resources, true);
        // 获取所有物品id
        $user_items_id = array_keys($resources);
        // 查询所需要的物品
        $items = DB::table('v3_items')
        ->whereIn('iid', $user_items_id)
        ->sharedLock()
        ->get();
      }
      $data = array(
        'user_items'  => $resources,
        'items'       => $items
      );
      return view('user.user_resources', $data);
    }

    // 回收中心
    public function recycle() {
      $uid = request()->cookie('uid');
      // 查询资源
      $resources = DB::table('v3_user_items')
                  ->where('uid', $uid)
                  ->sharedLock()
                  ->value('items');
      $items = [];
      if ($resources) {
        $resources = json_decode($resources, true);
        // 获取所有物品id
        $user_items_id = array_keys($resources);
        // 查询所需要的物品
        $items = DB::table('v3_items')
                  ->whereIn('iid', $user_items_id)
                  ->sharedLock()
                  ->get();
      }
      $data = array(
        'user_items'  => $resources,
        'items'       => $items
      );
      return view('user.recycle_center', $data);
    }

    // 合成中心
    public function blend() {
      $uid = request()->cookie('uid');
      // 基础资源信息 comber
      $combers = DB::table('v3_items')
                ->whereIn('iid', [1,2,3,4])
                ->get();
      // 查询资源物品
      $items = DB::table('v3_user_items')
              ->where('uid', $uid)
              ->value('items');
      $data = array(
        'combers'  => $combers,
        'items'    => json_decode($items, true)
      );
      return view('user.blend_center', $data);
    }
}
