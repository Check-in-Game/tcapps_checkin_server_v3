<?php

namespace App\Http\Controllers\Api;

use DB;
use Cookie;
use Captcha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\BackpackManager as BM;

class Market extends Controller {
  // 查询可出售物品
  public function query_items() {
    $uid      = request()->cookie('uid');
    // 查询资源
    $resources = BM::uid($uid)->backpack(true, BM::GENERAL);
    $data = array(
      'items'  => $resources,
    );
    $json = $this->JSON(0, null, ['msg'  => 'Success!', 'data' => $data]);
    return response($json);
  }

  // 提交挂售
  public function sale() {
    $uid        = request()->cookie('uid');
    $iid        = request()->post('iid');
    $price      = request()->post('price');
    $count      = request()->post('count');
    if (is_null($iid) || is_null($price) || is_null($count)) {
      $json = $this->JSON(404, 'Not found.', null);
      return response($json, 404);
    }
    // 检查数据是否正确
    $price = (int) $price;
    $count = (int) $count;
    if ($price <= 0 || $count <= 0) {
      $json = $this->JSON(5306, 'Bad request.', null);
      return response($json);
    }
    // 最低价格限制
    $lowest_price = DB::table('v3_items')
                      ->where('iid', $iid)
                      ->value('recycle_value');
    if ($lowest_price && $price < $lowest_price) {
      $json = $this->JSON(5307, 'Too cheap.', null);
      return response($json);
    }
    // 检查是否拥有挂售许可
    $license_iid = 14;  // 挂售许可iid
    $license_exists = BM::uid($uid)->has($license_iid, 1, BM::VALID);
    if (!$license_exists) {
      $json = $this->JSON(5302, 'Sale license needed.', null);
      return response($json);
    }
    // 检查挂售物品是否充足
    $need = $iid == 14 ? $count + 1 : $count;  // 挂售“挂售许可”时增加一个数量检查
    $is_sufficient = BM::uid($uid)->has($iid, $need, BM::GENERAL);
    if (!$is_sufficient) {
      $json = $this->JSON(5303, 'Insufficient items.', null);
      return response($json);
    }
    // 扣除挂售许可
    $license = BM::uid($uid)->reduce($license_iid, 1, BM::LOCKED_FIRST);
    // 缺少挂售许可
    if (!$license) {
      $json = $this->JSON(5302, 'Sale license needed.', null);
      return response($json);
    }
    // 扣除售出物品
    $item = BM::uid($uid)->reduce($iid, $count, BM::GENERAL_ONLY);
    // 挂售物品数量不足
    if (!$item) {
      // 返还挂售许可
      BM::uid($uid)->add($license_iid, 1, BM::GENERAL);
      $json = $this->JSON(5303, 'Insufficient items.', null);
      return response($json);
    }
    // 写入挂售数据
    $data = [
      'uid'     => $uid,
      'iid'     => $iid,
      'count'   => $count,
      'price'   => $price,
      'update_time'   => date('Y-m-d H:i:s'),
      'status'  => 1,
    ];
    $db = DB::table('v3_market_sale')->insert($data);
    if ($db) {
      $json = $this->JSON(0, null, ['msg'  => 'Success!']);
      return response($json);
    }else{
      // 返还挂售许可
      BM::uid($uid)->add($license_iid, 1, BM::GENERAL);
      // 返还物品
      BM::uid($uid)->add($iid, $count, BM::GENERAL);
      $json = $this->JSON(5305, 'Failed to put it on shelves.', null);
      return response($json);
    }
  }

  public function purchase() {
    $uid          = request()->cookie('uid');
    $sid          = request()->post('sid');
    $count        = request()->post('count');
    if (is_null($sid) || is_null($count)) {
      $json = $this->JSON(404, 'Not found.', null);
      return response($json, 404);
    }
    // 检查数量是否正确
    $count = (int) $count;
    if ($count <= 0) {
      $json = $this->JSON(5401, 'Bad request.', null);
      return response($json);
    }
    // 查询商品信息
    $sale = DB::table('v3_market_sale')
          ->where('sid', $sid)
          ->where('count', '>', 0)
          ->where('status', 1)
          ->sharedLock()
          ->first();
    // 商品不存在或已经售罄
    if (!$sale) {
      $json = $this->JSON(5402, 'Invaild sid.', null);
      return response($json);
    }
    // 查询是否是自己的商品
    if ($sale->uid === $uid) {
      $json = $this->JSON(5411, 'Invaild purchasing behavour.', null);
      return response($json);
    }
    // 获取用户信息
    $user = DB::table('v3_user_accounts')
            ->where('uid', $uid)
            ->sharedLock()
            ->first();
    if (!$user || $user->status !== 1) {
      $json = $this->JSON(5403, 'Invaild user status.', null);
      return response($json);
    }
    // 检查物品存量
    if ($count > $sale->count) {
      $json = $this->JSON(5404, 'Insufficient items.', null);
      return response($json);
    }
    // 计算花费
    $cost = $sale->price * $count;
    // 查询用户余额
    $balance = DB::table('v3_user_point')
              ->where('uid', $uid)
              ->where('point', '>=', $cost)
              ->sharedLock()
              ->value('point');
    // 余额不足
    if ($balance === false || $balance === null || $balance < $cost) {
      $json = $this->JSON(5405, 'Insuffcient funds.', null);
      return response($json);
    }
    // 扣除库存
    $decrement = DB::table('v3_market_sale')
          ->where('sid', $sid)
          ->where('update_time', $sale->update_time)
          ->where('status', 1)
          ->lockForUpdate()
          ->decrement('count', $count, ['update_time' => date('Y-m-d H:i:s')]);
    // 购买签权过期
    if (!$decrement) {
      $json = $this->JSON(5409, 'Timeout.', null);
      return response($json);
    }
    // 注册购买信息
    $data = [
      'sale_uid'      => $sale->uid,
      'purc_uid'      => $uid,
      'sid'           => $sale->sid,
      'iid'           => $sale->iid,
      'price'         => $sale->price,
      'count'         => $count,
      'purchase_time' => date('Y-m-d H:i:s'),
      'status'        => 1
    ];
    $pid = DB::table('v3_market_purchase_records')
            ->sharedLock()
            ->insert($data);
    if (!$pid) {
      $json = $this->JSON(5407, 'Unknown error.', null);
      return response($json);
    }
    // 扣除积分
    $db = DB::table('v3_user_point')
        ->where('uid', $uid)
        ->lockForUpdate()
        ->decrement('point', $cost);
    if (!$db) {
      $json = $this->JSON(5406, 'Unknown error.', null);
      return response($json);
    }
    // 增加卖家积分
    $db = DB::table('v3_user_point')
            ->where('uid', $sale->uid)
            ->exists();
    if ($db) {
      $db = DB::table('v3_user_point')
              ->where('uid', $sale->uid)
              ->lockForUpdate()
              ->increment('point', $cost);
    }else{
      $db = DB::table('v3_user_point')
              ->insert(['uid' => $sale->uid, 'point' => $cost]);
    }
    if (!$db) {
      $json = $this->JSON(5408, 'Unknown error.', null);
      return response($json);
    }
    $db = BM::uid($uid)->add($sale->iid, $count);
    if ($db) {
      $json = $this->JSON(0, null, ['msg' => 'Success!']);
      return response($json);
    }else{
      $json = $this->JSON(2507, 'Unknown error.', null);
      return response($json);
    }
  }

  // 修改单价
  public function modify_price() {
    $uid          = request()->cookie('uid');
    $sid          = request()->post('sid');
    $price        = request()->post('price');
    if (is_null($sid) || is_null($price)) {
      $json = $this->JSON(404, 'Not found.', null);
      return response($json, 404);
    }
    // 检查数量是否正确
    $price = (int) $price;
    if ($price <= 0) {
      $json = $this->JSON(5501, 'Bad request.', null);
      return response($json);
    }
    // 最低价格限制
    $lowest_price = DB::table('v3_items')
                      ->where('iid', $iid)
                      ->value('recycle_value');
    if ($lowest_price && $price < $lowest_price) {
      $json = $this->JSON(5503, 'Too cheap.', null);
      return response($json);
    }
    // 修改数据
    $db = DB::table('v3_market_sale')
            ->where('uid', $uid)
            ->where('sid', $sid)
            ->where('price', '<>', $price)
            ->where('count', '>', 0)
            ->where('status', 1)
            ->sharedLock()
            ->update(['price' => $price, 'update_time' => date('Y-m-d H:i:s')]);
    if (!$db) {
      $json = $this->JSON(5502, 'Invaild request.', null);
      return response($json);
    }else{
      $json = $this->JSON(0, null, ['msg' => 'Success!']);
      return response($json);
    }
  }

  // 下架挂售
  public function pulloff() {
    $uid          = request()->cookie('uid');
    $sid          = request()->post('sid');
    if (is_null($sid)) {
      $json = $this->JSON(404, 'Not found.', null);
      return response($json, 404);
    }
    // 获取数据
    $item = DB::table('v3_market_sale')
              ->where('sid', $sid)
              ->where('uid', $uid)
              ->where('status', 1)
              ->sharedLock()
              ->first();
    if (!$item) {
      $json = $this->JSON(5601, 'Invaild sid.', null);
      return response($json);
    }
    // 更新数据
    $db = DB::table('v3_market_sale')
            ->where('sid', $sid)
            ->where('uid', $uid)
            ->where('update_time', $item->update_time)
            ->sharedLock()
            ->update(['update_time' => date('Y-m-d H:i:s'), 'status' => -2]);
    if (!$db) {
      $json = $this->JSON(5602, 'Failed to pull it off.', null);
      return response($json);
    }
    // 退回物品
    // 查询用户资源
    $db = BM::uid($uid)->add($item->iid, $item->count, BM::LOCKED);
    if (!$db) {
      // 恢复数据
      DB::table('v3_market_sale')
        ->where('sid', $sid)
        ->where('uid', $uid)
        ->where('update_time', $item->update_time)
        ->sharedLock()
        ->update(['count' => $item->count, 'update_time' => date('Y-m-d H:i:s'), 'status' => 1]);
      $json = $this->JSON(5603, 'Failed to pull it off.', null);
      return response($json);
    }else{
      $json = $this->JSON(0, null, ['msg' => 'Success!']);
      return response($json);
    }
  }
}
