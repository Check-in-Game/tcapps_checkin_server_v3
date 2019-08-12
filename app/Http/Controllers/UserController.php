<?php

namespace App\Http\Controllers;

use Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Common\UserAuth;
use App\Http\Controllers\Common\BackpackManager as BM;

class UserController extends Controller {
    // 用户中心
    public function user() {
      $uid = request()->cookie('uid');
      // 查询积分
      $db_prefix = env('DB_PREFIX');
      $point = DB::table('v3_user_point')
                ->where('uid', $uid)
                ->value('point');
      $point = $point ? $point : 0;
      // 查询可莫尔信息
      $items = BM::uid($uid)
                ->items([1,2,3,4,5], true)
                ->backpack(true);
      // 清灰时间结算
      $clean = DB::table('v3_clean_list')
                ->where('uid', $uid)
                ->first();
      if ($clean && $clean->check_time >= date('Y-m-d 00:00:00')) {
        $clean = strtotime(date('Y-m-d 23:59:59')) - time();
      }else{
        $clean = 0;
      }
      $data = [
        'point'        => $point,
        'items'        => $items,
        'clean'        => $clean
      ];
      return view('user.home', $data);
    }

    // 兑换中心
    public function shop() {
      $uid = request()->cookie('uid');
      $user = DB::table('v3_user_accounts')->where('uid', $uid)->first();
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
              ->where('cid', $value['cid'])
              ->sum('item_count');
        // 当前用户购买量
        $userR = DB::table('v3_purchase_records')
              ->where('cid', $value['cid'])
              ->where('uid', $user->uid)
              ->sum('item_count');
        $shop[$key]['all_bought'] = $all;
        $shop[$key]['user_bought'] = $userR;
      }
      $point = DB::table('v3_user_point')
              ->where('uid', $user->uid)
              ->value('point');
      $data = [
        'uid'             => $uid,
        'goods'           => $shop,
        'point'           => $point ? $point : 0,
      ];
      return view('user.shop', $data);
    }

    // 修改密码
    public function security_change_password() {
      $uid = request()->cookie('uid');
      $user = DB::table('v3_user_accounts')->where('uid', $uid)->first();
      $data = [
        'username'        => $user->username
      ];
      return view('user.security_change_password', $data);
    }

    // 修改邮箱
    public function security_email() {
      $uid = request()->cookie('uid');
      $user = DB::table('v3_user_accounts')->where('uid', $uid)->first();
      $data = [
        'username'        => $user->username
      ];
      return view('user.security_email', $data);
    }

    // 验证邮箱
    public function verify_email($uid, $code) {
      // 寻找用户
      $user = DB::table('v3_user_accounts')
                ->where('uid', $uid)
                ->where('status', 0)
                ->first();
      if (!$user) {
        $data = [
          'color'       => 'warning',
          'msg'         => '用户不存在或状态异常，请稍候重试。'
        ];
        return view('user.verify_email', $data);
      }
      // 获取邮件注册信息
      $email_db = DB::table('v3_user_email_verification')
                    ->where('uid', $uid)
                    ->where('send_time', '>', date('Y-m-d H:i:s', strtotime('-30 minutes')))
                    ->first();
      if (!$email_db) {
        $data = [
          'color'       => 'warning',
          'msg'         => '验证链接已经失效，请重试。'
        ];
        return view('user.verify_email', $data);
      }
      // 对比code
      $res = UserAuth::email_code($email_db->send_time, $email_db->uid, $email_db->email, $code);
      if ($res) {
        $data = [
          'email'   => $email_db->email,
          'status'  => 1,
        ];
        DB::table('v3_user_accounts')->where('uid', $uid)->where('status', 0)->update($data);
        DB::table('v3_user_email_verification')->where('uid', $uid)->delete();
        $data = [
          'color'       => 'success',
          'msg'         => '验证成功！'
        ];
        $auth = UserAuth::generate_auth($user->password, $user->uid, 1);
        Cookie::queue('auth', $auth);
        // 新手礼包
        BM::uid($user->uid)->add(5, 100, BM::LOCKED);  // 20个可莫尔
        BM::uid($user->uid)->add(13, 10, BM::LOCKED);  // 10个WK兑换券
        BM::uid($user->uid)->add(14, 10, BM::LOCKED);  // 10个挂售许可
        return view('user.verify_email', $data);
      }else{
        $data = [
          'color'       => 'danger',
          'msg'         => '验证链接错误，请重试。'
        ];
        return view('user.verify_email', $data);
      }
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
      $items = BM::uid($uid)
                              ->backpack(true);
      $data = array(
        'items'       => $items
      );
      return view('user.user_resources', $data);
    }

    // 回收中心
    public function recycle() {
      $uid = request()->cookie('uid');
      // 查询资源
      $items = BM::uid($uid)
                              ->backpack(true);
      $data = array(
        'items'       => $items
      );
      return view('user.recycle_center', $data);
    }

    // 合成中心
    public function blend() {
      $uid = request()->cookie('uid');
      $items = BM::uid($uid)
                ->items([1,2,3,4], true)
                ->backpack(true);
      $data = array(
        'items'    => $items
      );
      return view('user.blend_center', $data);
    }

    // worker
    public function worker() {
      $uid = request()->cookie('uid');
      $worker_ticket = BM::uid($uid)
                        ->items(13, true)
                        ->backpack();
      $worker_ticket = $worker_ticket[13]['valid'];
      // 查询Worker数量
      $worker_count = DB::table('v3_user_workers')
                    ->where('uid', $uid)
                    ->sharedLock()
                    ->count();
      // 查询产区情况
      $_field = DB::table('v3_user_workers_field')
              ->join('v3_items', 'v3_user_workers_field.iid', '=', 'v3_items.iid')
              ->where('v3_user_workers_field.status', 1)
              ->get();
      $fields = [];
      foreach ($_field as $key => $value) {
        $fields[$value->limi_level][] = $value;
      }
      ksort($fields);
      // 统计各产区Worker数量
      $field_workers_data = DB::table('v3_user_workers')
                          ->where('fid', '<>', 0)
                          ->where('status', 1)
                          ->groupBy('fid')
                          ->select(DB::raw('fid, count(wid) as c'))
                          ->get();
      $field_workers = [];
      foreach ($field_workers_data as $key => $value) {
        $field_workers[$value->fid] = $value->c;
      }
      // 统计各产区用户Worker数量
      $field_workers_mine_data = DB::table('v3_user_workers')
                          ->where('fid', '<>', 0)
                          ->where('uid', $uid)
                          ->where('status', 1)
                          ->groupBy('fid')
                          ->select(DB::raw('fid, count(wid) as c'))
                          ->get();
      $field_workers_mine = [];
      foreach ($field_workers_mine_data as $key => $value) {
        $field_workers_mine[$value->fid] = $value->c;
      }
      $data = array(
        'worker_ticket'       => $worker_ticket,
        'worker_count'        => $worker_count,
        'fields'              => $fields,
        'field_workers'       => $field_workers,
        'field_workers_mine'  => $field_workers_mine,
        'harvest'             => json_encode(array_keys($field_workers_mine)),
      );
      return view('user.worker', $data);
    }

    // Worker升级
    public function worker_upgrade() {
      $uid = request()->cookie('uid');
      $workers = DB::table('v3_user_workers')
                  ->where('uid', $uid)
                  ->where('status', 1)
                  ->orderBy('level', 'desc')
                  ->lockForUpdate()
                  ->paginate(25);
      $data = array(
        'workers'        => $workers,
      );
      return view('user.worker_upgrade', $data);
    }

    // 礼物兑换
    public function gifts_reedem() {
      $uid = request()->cookie('uid');
      $workers = DB::table('v3_user_workers')
                  ->where('uid', $uid)
                  ->where('status', 1)
                  ->orderBy('level', 'desc')
                  ->lockForUpdate()
                  ->paginate(25);
      $data = array(
        'workers'        => $workers,
      );
      return view('user.gifts_reedem', $data);
    }

    // 交易市场
    public function market() {
      $uid = request()->cookie('uid');
      $select = [
        'v3_market_sale.sid',
        'v3_market_sale.uid',
        'v3_market_sale.iid',
        'v3_market_sale.count',
        'v3_market_sale.price',
        'v3_market_sale.update_time',
        'v3_market_sale.status',
        'v3_user_accounts.username',
        'v3_items.iname',
        'v3_items.image',
      ];
      $items = DB::table('v3_market_sale')
                  ->join('v3_user_accounts', 'v3_market_sale.uid', '=', 'v3_user_accounts.uid')
                  ->join('v3_items', 'v3_market_sale.iid', '=', 'v3_items.iid')
                  ->where('v3_market_sale.uid', '<>', $uid)
                  ->where('v3_market_sale.status', 1)
                  ->where('v3_market_sale.count', '>', 0)
                  ->orderBy('v3_market_sale.sid')
                  ->sharedLock()
                  ->select($select)
                  ->paginate(25);
      $data = array(
        'items'        => $items,
      );
      return view('user.market', $data);
    }

    // 交易市场挂售
    public function market_sale() {
      return view('user.market_sale');
    }

    // 挂售管理
    public function market_manage() {
      $uid = request()->cookie('uid');
      $select = [
        'v3_market_sale.sid',
        'v3_market_sale.uid',
        'v3_market_sale.iid',
        'v3_market_sale.count',
        'v3_market_sale.price',
        'v3_market_sale.update_time',
        'v3_market_sale.status',
        'v3_user_accounts.username',
        'v3_items.iname',
        'v3_items.image',
      ];
      $items = DB::table('v3_market_sale')
                  ->join('v3_user_accounts', 'v3_market_sale.uid', '=', 'v3_user_accounts.uid')
                  ->join('v3_items', 'v3_market_sale.iid', '=', 'v3_items.iid')
                  ->where('v3_market_sale.uid', $uid)
                  ->where('v3_market_sale.count', '>', 0)
                  ->where('v3_market_sale.status', '<>', -2)
                  ->orderBy('v3_market_sale.sid')
                  ->sharedLock()
                  ->select($select)
                  ->paginate(25);
      $data = array(
        'items'        => $items,
      );
      return view('user.market_manage', $data);
    }
}
