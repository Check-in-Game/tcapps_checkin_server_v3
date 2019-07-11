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
      if (!$username || !$b64password || !$captcha) {
        $json = $this->JSON(2307, 'Lost some infomation.', null);
        return response($json);
      }
      // 匹配验证码
      if (!Captcha::check($captcha)) {
        $json = $this->JSON(2305, 'Bad captcha.', null);
        return response($json);
      }
      $password    = base64_decode($b64password);
      if (mb_strlen($username) > 16 || mb_strlen($username) < 5 || mb_strlen($password) > 16 || mb_strlen($password) < 8) {
        $json = $this->JSON(2301, 'Incorrect username or password.', null);
        return response($json);
      }
      // 获取用户名密码
      $user = DB::table('user_accounts')->where('username', $username)->lockForUpdate()->first();
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
    public function purchase(int $cid) {
      $now = date('Y-m-d H:i:s');
      // 购买数量
      $item_count = 1;
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
        $cost = $good->sale_cost;
      }else{
        $cost = $good->cost;
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
      if ($good->all_count !== 0 && $all >= $good->all_count) {
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
      if ($good->rebuy !== 0 && $userR >= $good->rebuy) {
        $json = $this->JSON(2506, 'Purchasing times limited('. $userR .').', null);
        return response($json);
      }
      // 创建购买记录
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
      // 查询用户余额
      $balance = DB::table('v3_user_point')
                ->where('uid', $user->uid)
                ->where('point', '>=', $cost)
                ->sharedLock()
                ->value('point');
      // 余额不足
      if ($balance === false) {
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
      // 查询用户资源
      $user_package = DB::table('v3_user_items')
                    ->where('uid', $user->uid)
                    ->value('items');
      if (!$user_package) {
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
        $items = json_decode($user_package, true);
        if (isset($items[$good->iid]['count'])) {
          $items[$good->iid]['count'] += $item_count;
        }else{
          $items[$good->iid]['count'] = $item_count;
        }
        $data = array(
          'uid' => $user->uid,
          'items' => json_encode($items)
        );
        $db = DB::table('v3_user_items')->where('uid', $user->uid)->sharedLock()->update($data);
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
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
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
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
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
        if ($items[$i]['count'] - $count <= 0) {
          unset($items[$i]);
        }else{
          $items[$i]['count'] -= $count;
        }
      }
      // 增加可莫尔
      if (isset($items[5]['count'])) {
        $items[5]['count'] += $count;
      }else{
        $items[5]['count'] = $count;
      }
      $data = array(
        'items' => json_encode($items)
      );
      $db = DB::table('v3_user_items')
          ->where('uid', $uid)
          ->lockForUpdate()
          ->update($data);
      if (!$db) {
        $json = $this->JSON(4003, 'Failed to blend.', null);
        return response($json);
      }else{
        $json = $this->JSON(0, null, ['msg'  => 'Success!']);
        return response($json);
      }
    }
    // 可莫尔合成
    public function recycle() {
      $uid    = request()->cookie('uid');
      $iid    = request()->post('iid');
      $count  = request()->post('count');
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
      $items[$iid]['count'] -= $count;
      if ($items[$iid]['count'] === 0) {
        unset($items[$iid]['count']);
      }
      $data = array(
        'items' => json_encode($items)
      );
      $db = DB::table('v3_user_items')
          ->where('uid', $uid)
          ->lockForUpdate()
          ->update($data);
      if (!$db) {
        $json = $this->JSON(4104, 'Failed to recycle resources.', null);
        return response($json);
      }
      // 计算积分增量
      $point_add = $recycle_item->recycle_value * $count;
      // 查询用户积分
      $point = DB::table('v3_user_point')->where('uid', $uid)->lockForUpdate()->value('point');
      // 无记录
      if ($point === false || $point === null) {
        $data = array(
          'uid'   => $uid,
          'point' => $point_add
        );
        $db = DB::table('v3_user_point')->insert($data);
      }else{
        $db = DB::table('v3_user_point')->where('uid', $uid)->lockForUpdate()->increment('point', $point_add);
      }
      if ($db) {
        $json = $this->JSON(0, null, ['msg'  => 'Success!']);
        return response($json);
      }else{
        $json = $this->JSON(4105, 'An error occurred.', null);
        return response($json);
      }
    }
}
