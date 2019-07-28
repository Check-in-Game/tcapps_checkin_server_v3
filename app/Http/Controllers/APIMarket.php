<?php

namespace App\Http\Controllers;

use Cookie;
use Captcha;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class APIMarket extends Controller {
  // 查询可出售物品
  public function query_items() {
    $uid      = request()->cookie('uid');
    // 查询资源
    $resources = DB::table('v3_user_items')
                ->where('uid', $uid)
                ->sharedLock()
                ->value('items');
    $items = [];  // 物品详情
    if ($resources) {
      $resources = json_decode($resources, true);
      // 获取所有物品id
      $user_items_id = array_keys($resources);
      // 查询所需要的物品
      $items = DB::table('v3_items')
                ->whereIn('iid', $user_items_id)
                ->sharedLock()
                ->get();
    }else{
      $json = $this->JSON(5201, 'System is busy.', null);
      return response($json);
    }
    $data = array(
      'user_items'  => $resources,
      'items'       => $items
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
    // 检查是否拥有挂售许可
    $license_iid = 14;  // 挂售许可iid
    $license_exists = DB::table('v3_user_items')
                        ->where('uid', $uid)
                        ->sharedLock()
                        ->value("items->{$license_iid}->count");
    if (!$license_exists) {
      $json = $this->JSON(5302, 'Sale license needed.', null);
      return response($json);
    }
    // 检查挂售物品是否充足
    $item_count = DB::table('v3_user_items')
                    ->where('uid', $uid)
                    ->sharedLock()
                    ->value("items->{$iid}->count");
    if (!$item_count || $item_count < $count) {
      $json = $this->JSON(5303, 'Insufficient items.', null);
      return response($json);
    }
    // 获取背包数据
    $resources = DB::table('v3_user_items')
                ->where('uid', $uid)
                ->sharedLock()
                ->value('items');
    // 复制背包数据
    $_resources = $resources;
    $items = [];  // 物品详情
    if ($resources) {
      $resources = json_decode($resources, true);
    }else{
      $json = $this->JSON(5301, 'System is busy.', null);
      return response($json);
    }
    // 扣除挂售许可
    if (isset($resources[$license_iid]['count']) && $resources[$license_iid]['count'] >= 0) {
      $resources[$license_iid]['count'] --;
      // 数量小于等于0 删除该数据
      if ($resources[$license_iid]['count'] <= 0) {
        unset($resources[$license_iid]);
      }
    }else{
      // 缺少挂售许可
      $json = $this->JSON(5302, 'Sale license needed.', null);
      return response($json);
    }
    // 扣除售出物品
    if (isset($resources[$iid]['count']) && $resources[$iid]['count'] >= $count) {
      $resources[$iid]['count'] -= $count;
      // 数量小于等于0 删除该数据
      if ($resources[$iid]['count'] <= 0) {
        unset($resources[$iid]);
      }
    }else{
      // 挂售物品数量不足
      $json = $this->JSON(5303, 'Insufficient items.', null);
      return response($json);
    }
    // 更新物品数据
    $package = DB::table('v3_user_items')
                ->where('uid', $uid)
                ->lockForUpdate()
                ->update(['items'  => json_encode($resources)]);
    if (!$package) {
      $json = $this->JSON(5304, 'System is busy.', null);
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
      // 恢复数据
      DB::table('v3_user_items')
        ->where('uid', $uid)
        ->sharedLock()
        ->update(['items'  => json_encode($_resources)]);
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
    $user = DB::table('user_accounts')
            ->where('uid', $uid)
            ->sharedLock()
            ->first();
    if ($user->status !== 1) {
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
        ->lockForUpdate()
        ->increment('point', $cost);
    if (!$db) {
      $json = $this->JSON(5408, 'Unknown error.', null);
      return response($json);
    }
    // 查询用户资源
    $user_items = DB::table('v3_user_items')
                  ->where('uid', $uid)
                  ->sharedLock()
                  ->exists();
    if (!$user_items) {
      // 首次注册购买信息
      $items = array(
        $sale->iid  => array(
          'count'   => $count
        )
      );
      $data = array(
        'uid' => $uid,
        'items' => json_encode($items)
      );
      $db = DB::table('v3_user_items')->lockForUpdate()->insert($data);
    }else{
      // 更新购买信息
      // 查询是否有对应IID的记录
      $item_db = DB::table('v3_user_items')
                  ->where('uid', $uid)
                  ->sharedLock()
                  ->value("items->{$sale->iid}->count");
      if ($item_db || $item_db === 0) {
        // 存在对应IID记录
        $db = DB::table('v3_user_items')
              ->where('uid', $uid)
              ->lockForUpdate()
              ->update(["items->{$sale->iid}->count" => $count + $item_db]);
      }else{
        // 创建对应记录
        $db = DB::table('v3_user_items')
              ->where('uid', $uid)
              ->lockForUpdate()
              ->update(['items'=> DB::raw("JSON_MERGE(items, '{\"{$sale->iid}\":{\"count\": {$count}}}')")]);
      }
    }
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
    $user_items = DB::table('v3_user_items')
                  ->where('uid', $uid)
                  ->sharedLock()
                  ->exists();
    if (!$user_items) {
      // 首次注册购买信息
      $items = array(
        $item->iid  => array(
          'count'   => $item->count
        )
      );
      $data = array(
        'uid' => $uid,
        'items' => json_encode($items)
      );
      $db = DB::table('v3_user_items')->lockForUpdate()->insert($data);
    }else{
      // 更新购买信息
      // 查询是否有对应IID的记录
      $item_db = DB::table('v3_user_items')
                  ->where('uid', $uid)
                  ->sharedLock()
                  ->value("items->{$item->iid}->count");
      if ($item_db || $item_db === 0) {
        // 存在对应IID记录
        $db = DB::table('v3_user_items')
              ->where('uid', $uid)
              ->lockForUpdate()
              ->update(["items->{$item->iid}->count" => $item->count + $item_db]);
      }else{
        // 创建对应记录
        $db = DB::table('v3_user_items')
              ->where('uid', $uid)
              ->lockForUpdate()
              ->update(['items'=> DB::raw("JSON_MERGE(items, '{\"{$item->iid}\":{\"count\": {$item->count}}}')")]);
      }
    }
    if (!$db) {
      // 恢复数据
      DB::table('v3_market_sale')
        ->where('sid', $sid)
        ->where('uid', $uid)
        ->where('update_time', $item->update_time)
        ->sharedLock()
        ->update(['count' => $item->count, 'update_time' => date('Y-m-d H:i:s')]);
      $json = $this->JSON(5603, 'Failed to pull it off.', null);
      return response($json);
    }else{
      $json = $this->JSON(0, null, ['msg' => 'Success!']);
      return response($json);
    }
  }
}
