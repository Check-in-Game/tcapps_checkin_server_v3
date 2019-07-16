<?php

namespace App\Http\Controllers;

use Cookie;
use Captcha;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class APIUser extends Controller {

    // 登录
    public function login() {
      $username    = request()->post('username');
      $b64password = request()->post('password');
      $captcha     = request()->post('captcha');
      if (is_null($username) || is_null($b64password) || is_null($captcha)) {
        $json = $this->JSON(2307, 'Lost some infomation.', null);
        return response($json);
      }
      // 匹配验证码
      if (!Captcha::check($captcha)) {
        $json = $this->JSON(2305, 'Bad captcha.', null);
        return response($json);
      }
      $password    = base64_decode($b64password);
      if (mb_strlen($username) > 16 || mb_strlen($username) < 5
        || mb_strlen($password) > 16 || mb_strlen($password) < 8) {
        $json = $this->JSON(2301, 'Incorrect username or password.', null);
        return response($json);
      }
      // 获取用户名密码
      $user = DB::table('user_accounts')
            ->where('username', $username)
            ->lockForUpdate()
            ->first();
      if (!$user) {
        $json = $this->JSON(2302, 'Incorrect username or password.', null);
        return response($json);
      }
      // 匹配密码
      if ($user->password !== $this->generate_password($password)) {
        $json = $this->JSON(2303, 'Incorrect username or password.', null);
        return response($json);
      }
      // 用户状态
      if ($user->status === -1) {
        $json = $this->JSON(2306, 'Incorrect user status.', null);
        return response($json);
      }
      // 登录
      $auth = $this->generate_auth($user->password, $user->uid, $user->status);
      $json = $this->JSON(0, null, ['msg'  => 'Success!']);
      return response($json)
            ->withCookie(cookie()->forever('uid', $user->uid))
            ->withCookie(cookie()->forever('auth', $auth));
    }

    // 登出
    public function logout() {
      $json = $this->JSON(0, null, ['msg'  => 'Success!']);
      return response($json)
            ->withCookie(cookie()->forget('uid'))
            ->withCookie(cookie()->forget('auth'));
    }

    // purchase
    public function purchase() {
      $cid          = request()->post('cid');
      $item_count   = request()->post('count');
      if (is_null($cid) || is_null($item_count)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 检查数量是否正确
      if (!preg_match('/^[1-9][0-9]*$/', $item_count)) {
        $json = $this->JSON(2509, 'Bad request.', null);
        return response($json);
      }
      $now = date('Y-m-d H:i:s');
      // 查询商品信息
      $good = DB::table('v3_shop')
            ->where('cid', $cid)
            ->where('status', 1)
            ->sharedLock()
            ->first();
      // 商品不存在
      if (!$good) {
        $json = $this->JSON(2501, 'Invaild iid.', null);
        return response($json);
      }
      $uid = Cookie::get('uid');
      // 获取用户信息
      $user = DB::table('user_accounts')
              ->where('uid', $uid)
              ->sharedLock()
              ->first();
      if ($user->status !== 1) {
        $json = $this->JSON(2502, 'Invaild user status.', null);
        return response($json);
      }
      // 计算花费
      // 查询促销状态
      if ($good->onsale === 1 && $now >= $good->sale_starttime && $now <= $good->sale_endtime) {
        $cost = $good->sale_cost * $item_count;
      }else{
        $cost = $good->cost * $item_count;
      }
      // 判断购买时间是否合法
      if ($good->endtime !== '1970-01-01 00:00:00' && $now > $good->endtime) {
        $json = $this->JSON(2504, 'Invaild time.', null);
        return response($json);
      }
      // 检查物品存量
      $all = DB::table('v3_purchase_records')
            ->where('cid', $good->cid)
            ->sharedLock()
            ->sum('item_count');
      if ($all === false) {
        $json = $this->JSON(2508, 'System is busy.', null);
        return response($json);
      }
      if ($good->all_count !== 0 && $good->all_count - $all < $item_count) {
        $json = $this->JSON(2505, 'Insuffcient goods.', null);
        return response($json);
      }
      // 检查购买限制
      $userR = DB::table('v3_purchase_records')
            ->where('cid', $good->cid)
            ->where('uid', $user->uid)
            ->sharedLock()
            ->sum('item_count');
      if ($userR === false || $userR === null) {
        $json = $this->JSON(2508, 'System is busy.', null);
        return response($json);
      }
      if ($good->rebuy !== 0 && $userR + $item_count > $good->rebuy) {
        $json = $this->JSON(2506, 'Purchasing times limited('. $userR .').', ['rest'  => $good->rebuy - $userR]);
        return response($json);
      }
      // 查询用户余额
      $balance = DB::table('v3_user_point')
                ->where('uid', $user->uid)
                ->where('point', '>=', $cost)
                ->sharedLock()
                ->value('point');
      // 余额不足
      if ($balance === false || $balance === null) {
        $json = $this->JSON(2503, 'Insuffcient funds.', null);
        return response($json);
      }
      // 扣除积分
      $db = DB::table('v3_user_point')
          ->where('uid', $user->uid)
          ->lockForUpdate()
          ->decrement('point', $cost);
      if (!$db) {
        $json = $this->JSON(2507, 'Unknown error.', null);
        return response($json);
      }
      // 注册购买信息
      $data = [
        'uid'           => $user->uid,
        'cid'           => $good->cid,
        'iid'           => $good->iid,
        'item_count'    => $item_count,
        'cost'          => $cost,
        'purchase_time' => date('Y-m-d H:i:s'),
        'status'        => 1
      ];
      $pid = DB::table('v3_purchase_records')->sharedLock()->insert($data);
      if (!$pid) {
        $json = $this->JSON(2507, 'Unknown error.', null);
        return response($json);
      }
      // 查询用户资源
      $user_items = DB::table('v3_user_items')
                    ->where('uid', $user->uid)
                    ->sharedLock()
                    ->exists();
      if (!$user_items) {
        // 首次注册购买信息
        $items = array(
          $good->iid  => array(
            'count'   => $item_count
          )
        );
        $data = array(
          'uid' => $user->uid,
          'items' => json_encode($items)
        );
        $db = DB::table('v3_user_items')->lockForUpdate()->insert($data);
      }else{
        // 更新购买信息
        // 查询是否有对应IID的记录
        $item_db = DB::table('v3_user_items')
                    ->where('uid', $user->uid)
                    ->sharedLock()
                    ->value("items->{$good->iid}->count");
        if ($item_db || $item_db === 0) {
          // 存在对应IID记录
          $db = DB::table('v3_user_items')
                ->where('uid', $user->uid)
                ->lockForUpdate()
                ->update(["items->{$good->iid}->count" => $item_count + $item_db]);
        }else{
          // 创建对应记录
          $db = DB::table('v3_user_items')
                ->where('uid', $user->uid)
                ->lockForUpdate()
                ->update(['items'=> DB::raw("JSON_MERGE(items, '{\"{$good->iid}\":{\"count\": {$item_count}}}')")]);
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

    // 修改密码
    public function security_change_password() {
      // 判断提交方式是否安全
      if(!Request()->isMethod('post')){
        $json = $this->JSON(2701, 'Invaild method.', null);
        return response($json);
      }
      // 获取用户uid
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->sharedLock()->first();
      // 顺便验证签权状态
      if (!$user) {
        $json = $this->JSON(2702, 'Invaild user.', null);
        return response($json);
      }
      $old_password = Request()->post('old_password');
      $new_password = Request()->post('new_password');
      $comfirm_password = Request()->post('comfirm_password');
      $captcha = Request()->post('captcha');
      // 检查验证码
      if (!Captcha::check($captcha)) {
        $json = $this->JSON(2706, 'Bad captcha.', null);
        return response($json);
      }
      // 解析密码
      $old_password = base64_decode($old_password);
      $new_password = base64_decode($new_password);
      $comfirm_password = base64_decode($comfirm_password);
      // 判断密码长度与一致性
      if (mb_strlen($old_password) < 8 || mb_strlen($old_password) > 16
        || mb_strlen($new_password) < 8 || mb_strlen($new_password) > 16
        || mb_strlen($comfirm_password) < 8 || mb_strlen($comfirm_password) > 16
        || $new_password !== $comfirm_password
      ){
        $json = $this->JSON(2703, 'Invaild password.', null);
        return response($json);
      }
      // 判断原密码是否正确
      if ($this->generate_password($old_password) !== $user->password) {
        $json = $this->JSON(2704, 'Bad auth.', null);
        return response($json);
      }
      // 修改密码
      $password = $this->generate_password($new_password);
      $data = [
        'password'  => $password
      ];
      $res = DB::table('user_accounts')
                ->where('uid', $user->uid)
                ->lockForUpdate()
                ->update($data);
      if ($res) {
        $json = $this->JSON(0, null, ['msg'  => 'Success!']);
        return response($json)
        ->withCookie(cookie()->forget('uid'))
        ->withCookie(cookie()->forget('auth'));
      }else{
        $json = $this->JSON(2705, 'Failed to change password.', null);
        return response($json);
      }
    }

    // 修改用户名
    public function security_change_username() {
      // 获取用户uid
      $uid = request()->cookie('uid');
      $user = DB::table('user_accounts')->where('uid', $uid)->sharedLock->first();
      if (!$user) {
        $json = $this->JSON(3502, 'Invaild user.', null);
        return response($json);
      }else if($user->status === 1) {
        $json = $this->JSON(3503, 'Cannot change username.', null);
        return response($json);
      }
      $username = Request()->post('username');
      // 判断用户名是否合法
      $pattern = "/^[a-zA-Z0-9_]+$/";
      $preg = preg_match($pattern, $username);
      if (mb_strlen($username) < 5 || mb_strlen($username) > 16 || !$preg){
        $json = $this->JSON(3501, 'Invaild username.', null);
        return response($json);
      }
      // 用户名不合法状态
      if ($user->status === 0) {
        $data = [
          'username'  => $username,
          'status'    => 1,         // 修正合法状态
        ];
      }else{
        // 其他情况不修改状态
        $json = $this->JSON(3503, 'Failed to change username.', null);
        return response($json);
      }
      // 修改用户名
      $res = DB::table('user_accounts')
                ->where('uid', $user->uid)
                ->update($data);
      if ($res) {
        $json = $this->JSON(0, null, ['msg'  => 'Success!']);
        return response($json)
        ->withCookie(cookie()->forget('uid'))
        ->withCookie(cookie()->forget('auth'));
      }else{
        $json = $this->JSON(3503, 'Failed to change username.', null);
        return response($json);
      }
    }

    // 可莫尔合成
    public function blend() {
      $uid    = request()->cookie('uid');
      $count  = request()->post('count');
      if (is_null($count)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 检查数量是否正确
      if (!preg_match('/^[1-9][0-9]*$/', $count)) {
        $json = $this->JSON(4001, 'Bad request.', null);
        return response($json);
      }
      $count = (int) $count;
      if (!is_numeric($count) || !is_int($count)) {
        $json = $this->JSON(4001, 'Bad request.', null);
        return response($json);
      }
      // 查询该用户可莫尔数量
      $user_items = DB::table('v3_user_items')
              ->where('uid', $uid)
              ->sharedLock()
              ->value('items');
      if (!$user_items) {
        $json = $this->JSON(4002, 'Insufficient combers.', null);
        return response($json);
      }
      $items = json_decode($user_items, true);
      // 检查碎片数量需求
      if (!isset($items[1]['count'], $items[2]['count'], $items[3]['count'], $items[4]['count'])) {
        $json = $this->JSON(4002, 'Insufficient combers.', null);
        return response($json);
      }
      $combers = [$items[1]['count'], $items[2]['count'], $items[3]['count'], $items[4]['count']];
      if (min($combers) < $count) {
        $json = $this->JSON(4002, 'Insufficient combers.', null);
        return response($json);
      }
      // 扣除碎片
      for ($i=1; $i <= 4; $i++) {
        $c = $items[$i]['count'] - $count;
        if ($c <= 0) {
          $db = DB::table('v3_user_items')
              ->where('uid', $uid)
              ->lockForUpdate()
              ->update(['items'=> DB::raw("JSON_REMOVE(items, '$.\"{$i}\"')")]);
        }else{
          $db = DB::table('v3_user_items')
              ->where('uid', $uid)
              ->lockForUpdate()
              ->update(["items->{$i}->count"  => $c]);
        }
        if (!$db) {
          $json = $this->JSON(4003, 'Failed to blend.', null);
          return response($json);
        }
      }
      // 增加可莫尔
      if (isset($items[5]['count'])) {
        $db = DB::table('v3_user_items')
            ->where('uid', $uid)
            ->lockForUpdate()
            ->update(["items->5->count"  => $items[5]['count'] + $count]);
      }else{
        $db = DB::table('v3_user_items')
            ->where('uid', $uid)
            ->lockForUpdate()
            ->update(['items'=> DB::raw("JSON_MERGE(items, '{\"5\":{\"count\": {$count}}}')")]);
      }
      if (!$db) {
        $json = $this->JSON(4003, 'Failed to blend.', null);
        return response($json);
      }else{
        $json = $this->JSON(0, null, ['msg'  => 'Success!']);
        return response($json);
      }
    }

    // 资源回收
    public function recycle() {
      $uid    = request()->cookie('uid');
      $iid    = request()->post('iid');
      $count  = request()->post('count');
      if (is_null($iid) || is_null($count)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 检查数量是否正确
      if (!preg_match('/^[1-9][0-9]*$/', $count)) {
        $json = $this->JSON(4101, 'Bad request.', null);
        return response($json);
      }
      $count = (int) $count;
      if (!is_numeric($count) || !is_int($count)) {
        $json = $this->JSON(4101, 'Bad request.', null);
        return response($json);
      }
      // 查询该用户指定资源数量
      $user_items = DB::table('v3_user_items')
              ->where('uid', $uid)
              ->sharedLock()
              ->value('items');
      if (!$user_items) {
        $json = $this->JSON(4102, 'Insufficient resources.', null);
        return response($json);
      }
      $items = json_decode($user_items, true);
      // 检查资源数量需求
      if (!isset($items[$iid]['count'])) {
        $json = $this->JSON(4102, 'Insufficient resources.', null);
        return response($json);
      }
      if ($items[$iid]['count'] < $count) {
        $json = $this->JSON(4102, 'Insufficient resources.', null);
        return response($json);
      }
      // 查询回收资源信息
      $recycle_item = DB::table('v3_items')->where('iid', $iid)->first();
      if (!$recycle_item) {
        $json = $this->JSON(4103, 'Incorrect iid.', null);
        return response($json);
      }
      // 扣除资源
      $c = $items[$iid]['count'] - $count;
      if ($c <= 0) {
        $db = DB::table('v3_user_items')
            ->where('uid', $uid)
            ->lockForUpdate()
            ->update(['items'=> DB::raw("JSON_REMOVE(items, '$.\"{$iid}\"')")]);
      }else{
        $db = DB::table('v3_user_items')
            ->where('uid', $uid)
            ->lockForUpdate()
            ->update(["items->{$iid}->count"  => $c]);
      }
      if (!$db) {
        $json = $this->JSON(4104, 'Failed to recycle resources.', null);
        return response($json);
      }
      // 计算积分增量
      $point_add = $recycle_item->recycle_value * $count;
      // 写入回收记录
      $data = array(
        'uid' => $uid,
        'iid' => $iid,
        'item_count'  => $count,
        'value' => $point_add,
        'recycle_time'  => date('Y-m-d H:i:s'),
        'status'  => 1,
      );
      $db = DB::table('v3_recycle_records')->sharedLock()->insert($data);
      if ($point_add !== 0) {
        // 查询用户积分
        $point = DB::table('v3_user_point')
                  ->where('uid', $uid)
                  ->lockForUpdate()
                  ->exists();
        // 无记录
        if (!$point) {
          $data = array(
            'uid'   => $uid,
            'point' => $point_add
          );
          $db = DB::table('v3_user_point')
                  ->sharedLock()
                  ->insert($data);
        }else{
          $db = DB::table('v3_user_point')
                  ->where('uid', $uid)
                  ->lockForUpdate()
                  ->increment('point', $point_add);
        }
      }else{
        $db = true;
      }
      if ($db) {
        $json = $this->JSON(0, null, ['msg'  => 'Success!']);
        return response($json);
      }else{
        $json = $this->JSON(4105, 'An error occurred.', null);
        return response($json);
      }
    }

    // Worker兑换
    public function worker_redeem() {
      $uid    = request()->cookie('uid');
      // 查询兑换券数量 IID 13
      $worker_ticket = DB::table('v3_user_items')
                  ->where('uid', $uid)
                  ->sharedLock()
                  ->value('items->13->count');
      if (!$worker_ticket) {
        $json = $this->JSON(4201, 'Insufficient Worker ticket.', null);
        return response($json);
      }
      // 兑换Worker
      $worker_ticket -= 1;
      if ($worker_ticket === 0) {
        $db = DB::table('v3_user_items')
            ->where('uid', $uid)
            ->lockForUpdate()
            ->update(['items'=> DB::raw('JSON_REMOVE(items, "$.\"13\"")')]);
      }else{
        $db = DB::table('v3_user_items')
            ->where('uid', $uid)
            ->lockForUpdate()
            ->update(['items->13->count' => $worker_ticket]);
      }
      if (!$db) {
        $json = $this->JSON(4202, 'Failed to redeem worker.', null);
        return response($json);
      }
      // 注册Worker
      $data = array(
        'uid'   => $uid,
        'fid'   => 0,
        'level' => 1,
        'update_time' => date('Y-m-d H:i:s'),
        'status'  => 1,
      );
      $db = DB::table('v3_user_workers')->insert($data);
      if ($db) {
        $json = $this->JSON(0, null, ['msg'  => 'Success!']);
        return response($json);
      }else{
        $json = $this->JSON(4203, 'An error occurred.', null);
        return response($json);
      }
    }

    // Worker查询
    public function worker() {
      $uid    = request()->cookie('uid');
      $fid    = request()->post('fid');
      if (is_null($fid)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      $db = DB::table('v3_user_workers')
          ->where('uid', $uid)
          ->where('fid', $fid)
          ->where('status', 1)
          ->orderBy('level', 'desc')
          ->lockForUpdate()
          ->get();
      if ($db) {
        $json = $this->JSON(0, null, ['msg'  => 'Success!', 'data' => $db]);
        return response($json);
      }else{
        $json = $this->JSON(4301, 'Failed to find workers.', null);
        return response($json);
      }
    }

    // Worker投放
    public function worker_assign() {
      $uid    = request()->cookie('uid');
      $wid    = request()->post('wid');
      $fid    = request()->post('fid');
      if (is_null($fid) || is_null($wid)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 获取Worker信息
      $worker = DB::table('v3_user_workers')
          ->where('uid', $uid)
          ->where('wid', $wid)
          ->where('status', 1)
          ->lockForUpdate()
          ->first();
      if (!$worker) {
        $json = $this->JSON(4401, 'Inoperable worker.', null);
        return response($json);
      }
      // 获取指定产区信息
      $field = DB::table('v3_user_workers_field')
          ->where('fid', $fid)
          ->where('status', 1)
          ->first();
      if (!$field) {
        $json = $this->JSON(4402, 'Invaild worker field.', null);
        return response($json);
      }
      // 检查等级限制
      if ($worker->level < $field->limi_level) {
        $json = $this->JSON(4403, 'Higher level needed.', null);
        return response($json);
      }
      // 检查总数量限制
      $count = DB::table('v3_user_workers')
            ->where('fid', $fid)
            ->where('status', 1)
            ->count();
      if ($count && $field->limi_count != 0 && $count >= $field->limi_count) {
        $json = $this->JSON(4404, 'No free slots.', null);
        return response($json);
      }
      $data = array(
        'fid' => $fid,
        'update_time' => date('Y-m-d H:i:s')
      );
      $db = DB::table('v3_user_workers')
          ->where('uid', $uid)
          ->where('wid', $wid)
          ->where('status', 1)
          ->lockForUpdate()
          ->update($data);
      if ($db) {
        $json = $this->JSON(0, null, ['msg'  => 'Success!', 'data' => $db]);
        return response($json);
      }else{
        $json = $this->JSON(4401, 'Inoperable worker.', null);
        return response($json);
      }
    }

    // Worker投放
    public function worker_withdraw() {
      $uid    = request()->cookie('uid');
      $wid    = request()->post('wid');
      if (is_null($wid)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      $data = array(
        'fid' => 0,
        'update_time' => date('Y-m-d H:i:s')
      );
      $db = DB::table('v3_user_workers')
          ->where('uid', $uid)
          ->where('wid', $wid)
          ->where('status', 1)
          ->lockForUpdate()
          ->update($data);
      if ($db) {
        $json = $this->JSON(0, null, ['msg'  => 'Success!', 'data' => $db]);
        return response($json);
      }else{
        $json = $this->JSON(4501, 'Inoperable worker.', null);
        return response($json);
      }
    }

    // Worker 查询收获预计收益
    public function worker_harvest_query() {
      $uid    = request()->cookie('uid');
      $fid    = request()->post('fid');
      if (is_null($fid)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 查询Worker数量
      $worker_count = DB::table('v3_user_workers')
                    ->where('uid', $uid)
                    ->where('fid', $fid)
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->count();
      if ($worker_count === 0) {
        $json = $this->JSON(4602, 'No worker here.', null);
        return response($json);
      }
      // 查询最新日期
      $update_time = DB::table('v3_user_workers')
                    ->where('uid', $uid)
                    ->where('fid', $fid)
                    ->where('status', 1)
                    ->orderBy('update_time', 'desc')
                    ->lockForUpdate()
                    ->value('update_time');
      // 查询区域信息
      $field_info = DB::table('v3_user_workers_field')
                ->join('v3_items', 'v3_user_workers_field.iid', '=', 'v3_items.iid')
                ->where('v3_user_workers_field.fid', $fid)
                ->where('v3_user_workers_field.status', 1)
                ->select([
                  'v3_user_workers_field.fid',
                  'v3_user_workers_field.fname',
                  'v3_user_workers_field.iid',
                  'v3_user_workers_field.speed',
                  'v3_user_workers_field.times',
                  'v3_items.iname',
                ])
                ->first();
      if (!$worker_count || !$update_time || !$field_info) {
        $json = $this->JSON(4601, 'Incorrect field.', null);
        return response($json);
      }else{
        $data = array(
          'worker_count'       => $worker_count,
          'update_time'        => $update_time,
          'update_time_unix'   => strtotime($update_time),
          'field_info'         => $field_info
        );
        $json = $this->JSON(0, null, ['msg'  => 'Success!', 'data' => $data]);
        return response($json);
      }
    }

    // Worker 收获
    public function worker_harvest() {
      $uid      = request()->cookie('uid');
      $fid      = request()->post('fid');
      $captcha  = request()->post('captcha');
      if (is_null($fid) || is_null($captcha)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 匹配验证码
      if (!Captcha::check($captcha)) {
        $json = $this->JSON(4703, 'Bad captcha.', null);
        return response($json);
      }
      // 查询Worker数量
      $worker_count = DB::table('v3_user_workers')
                    ->where('uid', $uid)
                    ->where('fid', $fid)
                    ->where('status', 1)
                    ->lockForUpdate()
                    ->count();
      // 查询最新日期
      $update_time = DB::table('v3_user_workers')
                    ->where('uid', $uid)
                    ->where('fid', $fid)
                    ->where('status', 1)
                    ->orderBy('update_time', 'desc')
                    ->lockForUpdate()
                    ->value('update_time');
      // 查询区域信息
      $field_info = DB::table('v3_user_workers_field')
                ->join('v3_items', 'v3_user_workers_field.iid', '=', 'v3_items.iid')
                ->where('v3_user_workers_field.fid', $fid)
                ->where('v3_user_workers_field.status', 1)
                ->select([
                  'v3_user_workers_field.fid',
                  'v3_user_workers_field.fname',
                  'v3_user_workers_field.iid',
                  'v3_user_workers_field.speed',
                  'v3_user_workers_field.times',
                  'v3_items.iname',
                ])
                ->first();
      if (!$worker_count || !$update_time || !$field_info) {
        $json = $this->JSON(4701, 'Incorrect field.', null);
        return response($json);
      }
      // 计算投放时间
      $time_delta = (time() - strtotime($update_time)) / 60 / 60;
      // 计算收益
      $profits = floor($time_delta * $field_info->speed * $field_info->times * $worker_count);
      $profits = $profits > 24 ? 24 : $profits;
      if ($profits != 0) {
        // 发放收益
        $iid = $field_info->iid;
        // 查询记录是否存在
        $exists = DB::table('v3_user_items')
            ->where('uid', $uid)
            ->sharedLock()
            ->exists();
        if (!$exists) {
          // 插入记录
          $data = array(
            'uid' => $uid,
            'items' => json_encode(array($iid=>array('count'=>$profits)))
          );
          $db = DB::table('v3_user_items')->sharedLock()->insert($data);
        }else{
          // 更新记录
          $db = DB::table('v3_user_items')
                  ->where('uid', $uid)
                  ->sharedLock()
                  ->value("items->{$iid}->count");
          if ($db) {
            // 存在对应IID记录
            $db = DB::table('v3_user_items')
                    ->where('uid', $uid)
                    ->lockForUpdate()
                    ->update(["items->{$iid}->count" => $profits + $db]);
          }else{
            // 创建对应记录
            $db = DB::table('v3_user_items')
                    ->where('uid', $uid)
                    ->lockForUpdate()
                    ->update(['items'=> DB::raw("JSON_MERGE(items, '{\"{$iid}\":{\"count\": {$profits}}}')")]);
          }
        }
      }else{
        $db = true;
      }
      if (!$db) {
        $json = $this->JSON(4702, 'Failed to pay bounds.', null);
        return response($json);
      }else{
        // 更新时间
        $data = array(
          'update_time' => date('Y-m-d H:i:s')
        );
        DB::table('v3_user_workers')
          ->where('uid', $uid)
          ->where('fid', $fid)
          ->where('status', 1)
          ->lockForUpdate()
          ->update($data);
        $json = $this->JSON(0, null, ['msg'  => 'Success!', 'data' => array(
          'profits' => $profits,
          'iname'   => $field_info->iname
        )]);
        return response($json);
      }
    }
}
