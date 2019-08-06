<?php

namespace App\Http\Controllers\Api;

use DB;
use Cookie;
use Captcha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\UserAuth;
use App\Http\Controllers\Common\BackpackManager as BM;

class User extends Controller {

    // 老用户数据迁移
    public function login_old() {
      $old_username    = request()->post('old_username');
      $old_password    = request()->post('old_password');
      $username        = request()->post('username');
      $password        = request()->post('password');
      $captcha         = request()->post('captcha');
      if (is_null($old_username) || is_null($old_password) ||
          is_null($username) || is_null($password) || is_null($captcha)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 匹配验证码
      if (!Captcha::check($captcha)) {
        $json = $this->JSON(5701, 'Bad captcha.', null);
        return response($json);
      }
      // 判断新账户是用户名否符合注册要求
      $pattern = "/^[a-z][a-zA-Z0-9_]{4,15}$/";
      if(!preg_match($pattern,$username)){
        $json = $this->JSON(5703, 'Invaild username.', null);
        return response($json);
      }
      // 判断新账户密码是否符合注册要求
      $pattern = "/^.{6,16}$/";
      if(!preg_match($pattern,$password)){
        $json = $this->JSON(5704, 'Invaild password.', null);
        return response($json);
      }
      // 密码加密
      $password = UserAuth::generate_password($password);
      // 签权老账户密码
      $old_password = md5($old_password.'tcAppsCheckIn@)!(');
      // 匹配原账户密码
      $old_account = DB::table('user_accounts')
                      ->where('username', $old_username)
                      ->where('password', $old_password)
                      ->where('status', 1)
                      ->first();
      // 账户不存在
      if (!$old_account) {
        $json = $this->JSON(5702, 'Incorrect old account.', null);
        return response($json);
      }
      // 判断新账户是否已经被注册
      $new_account = DB::table('v3_user_accounts')
                      ->where('username', $username)
                      ->exists();
      // 该用户名已经被使用
      if ($new_account) {
        $json = $this->JSON(5705, 'Used username.', null);
        return response($json);
      }
      // 注册用户
      $data = [
        'username'      => $username,
        'nickname'      => $username,
        'password'      => $password,
        'register_at'   => date('Y-m-d H:i:s'),
        'update_at'     => date('Y-m-d H:i:s'),
        'status'        => $status = 0, // 未验证邮箱
      ];
      $new_account = DB::table('v3_user_accounts')->insertGetId($data);
      $uid = $new_account;
      // 写入数据库失败
      if (!$new_account) {
        $json = $this->JSON(5706, 'Unknown error.', null);
        return response($json);
      }
      // 原账户标记已经转移
      DB::table('user_accounts')->where('uid', $old_account->uid)->update(['status'=> -3]);
      // 读取原有资产
      $items = DB::table('v3_user_items')
                ->where('uid', $old_account->uid)
                ->value('items');

      if ($items && count($items = json_decode($items, true)) > 0) {
        // 转移资产
        foreach($items as $iid => $value) {
          $data = [
            'uid'     => $new_account,
            'iid'     => $iid,
            'amount'  => $value['count'],
            'status'  => 1
          ];
          DB::table('v3_user_backpack')->insert($data);
        }
      }
      // 积分转移
      $point = DB::table('v3_point_')
                ->where('uid', $old_account->uid)
                ->value('point');
      if ($point) {
        DB::table('v3_point')->insert(['uid'=>$uid, 'point'=>$point]);
      }
      $auth = UserAuth::generate_auth($password, $uid, $status);
      $json = $this->JSON(0, null, ['msg'  => 'Success!']);
      return response($json)
            ->withCookie(cookie()->forever('uid', $uid))
            ->withCookie(cookie()->forever('auth', $auth));
    }

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
      $user = DB::table('v3_user_accounts')
            ->where('username', $username)
            ->lockForUpdate()
            ->first();
      if (!$user) {
        $json = $this->JSON(2302, 'Incorrect username or password.', null);
        return response($json);
      }
      // 匹配密码
      if ($user->password !==  UserAuth::generate_password($password)) {
        $json = $this->JSON(2303, 'Incorrect username or password.', null);
        return response($json);
      }
      // 用户状态
      if ($user->status === -2) {
        $json = $this->JSON(2306, 'Incorrect user status.', null);
        return response($json);
      }
      // 登录
      $auth = UserAuth::generate_auth($user->password, $user->uid, $user->status);
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
      $uid          = request()->cookie('uid');
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
            ->where('starttime', '<=', date('Y-m-d H:i:s'))
            ->where('status', 1)
            ->sharedLock()
            ->first();
      // 商品不存在
      if (!$good) {
        $json = $this->JSON(2501, 'Invaild iid.', null);
        return response($json);
      }
      // 获取用户信息
      $user = DB::table('v3_user_accounts')
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
        $json = $this->JSON(2505, 'Insufficient goods.', null);
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
      $db = BM::uid($uid)->add($good->iid, $item_count, BM::GENERAL);
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
      if (UserAuth::generate_password($old_password) !== $user->password) {
        $json = $this->JSON(2704, 'Bad auth.', null);
        return response($json);
      }
      // 修改密码
      $password = UserAuth::generate_password($new_password);
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

    // 注册邮箱验证
    public function security_email_verify() {
      $uid            = request()->cookie('uid');
      $email          = request()->post('email');
      $captcha        = request()->post('captcha');
      if (is_null($email) || is_null($captcha)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 检查验证码
      if (!Captcha::check($captcha)) {
        $json = $this->JSON(5801, 'Bad captcha.', null);
        return response($json);
      }
      // 验证邮箱地址
      $pattern = "/^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$/";
      if(!preg_match($pattern,$email)){
        $json = $this->JSON(5802, 'Bad email address.', null);
        return response($json);
      }
      // 检测邮件重复
      $db = DB::table('v3_user_accounts')
              ->where('email', $email)
              ->exists();
      if ($db) {
        $json = $this->JSON(5803, 'Already bound.', null);
        return response($json);
      }
      // 获取用户信息
      $user = DB::table('v3_user_accounts')
                ->where('uid', $uid)
                ->where('status', 0)
                ->sharedLock()
                ->first();
      if (!$user) {
        $json = $this->JSON(5804, 'Invaild user.', null);
        return response($json);
      }
      // 检查注册记录
      $send_time = DB::table('v3_user_email_verification')
              ->where('uid', $uid)
              ->where('send_time', '>', date('Y-m-d H:i:s', strtotime('-60 seconds')))
              ->value('send_time');
      if ($send_time) {
        $rest_sec = 60 - (time() - strtotime($send_time));
        $json = $this->JSON(5805, 'Too many requests.', ['rest_sec' => $rest_sec]);
        return response($json);
      }
      // 注册信息
      $now = date('Y-m-d H:i:s');
      $data = [
        'email' => $email,
        'send_Time' => $now
      ];
      $res = DB::table('v3_user_email_verification')
                ->lockForUpdate()
                ->updateOrInsert(['uid'=>$uid], $data);
      if (!$res) {
        $json = $this->JSON(5806, 'Failed to register the email.', null);
        return response($json);
      }
      // 发送邮件
      $subject = '【'.env('APP_NAME').'】验证电子邮件地址';
      $code = UserAuth::email_code($now, $uid, $email);
      $data = ['active_link' => url("/user/verify_email/{$uid}/{$code}")];
      $this->sendMail('email.activationcode', $email, $user->nickname, $subject, $data);
      $json = $this->JSON(0, null, ['msg' => 'Success!']);
      return response($json);

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
      $comber_1 = BM::uid($uid)->has(1, $count, BM::VALID);
      $comber_2 = BM::uid($uid)->has(2, $count, BM::VALID);
      $comber_3 = BM::uid($uid)->has(3, $count, BM::VALID);
      $comber_4 = BM::uid($uid)->has(4, $count, BM::VALID);
      if (!$comber_1 || !$comber_2 || !$comber_3 || !$comber_4) {
        $json = $this->JSON(4002, 'Insufficient combers.', null);
        return response($json);
      }
      // 扣除碎片
      for ($i=1; $i <= 4; $i++) {
        $db = BM::uid($uid)->reduce($i, $count, BM::LOCKED_FIRST);
        if (!$db) {
          $json = $this->JSON(4003, 'Failed to blend.', null);
          return response($json);
        }
      }
      // 增加可莫尔
      $db = BM::uid($uid)->add($i, $count, BM::GENERAL);
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
      $user_items = BM::uid($uid)->has($iid, $count, BM::VALID);
      if (!$user_items) {
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
      $db = BM::uid($uid)->reduce($iid, $count, BM::LOCKED_FIRST);
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
      DB::table('v3_recycle_records')->sharedLock()->insert($data);
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
      $worker_ticket = BM::uid($uid)->has(13, 1, BM::VALID);
      if (!$worker_ticket) {
        $json = $this->JSON(4201, 'Insufficient Worker ticket.', null);
        return response($json);
      }
      // 兑换Worker
      $db = BM::uid($uid)->reduce(13, 1, BM::LOCKED_FIRST);
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

    // Worker查询
    public function worker_assign_query() {
      $uid    = request()->cookie('uid');
      $fid    = request()->post('fid');
      if (is_null($fid)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      $level = DB::table('v3_user_workers_field')
                ->where('fid', $fid)
                ->value('limi_level');
      if ($level === null || $level === false) {
        $json = $this->JSON(4301, 'Failed to find workers.', null);
        return response($json);
      }
      $db = DB::table('v3_user_workers')
          ->where('uid', $uid)
          ->where('fid', 0)
          ->where('level', '>=', $level)
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
      // 最大计算24小时
      $time_delta = $time_delta >= 24 ? 24 : $time_delta;
      // 计算收益
      $profits = floor($time_delta * $field_info->speed * $field_info->times * $worker_count);
      if ($profits != 0) {
        // 发放收益
        $iid = $field_info->iid;
        $db = BM::uid($uid)->add($iid, $profits, BM::GENERAL);
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

    // Worker 升级查询
    public function worker_upgrade_query() {
      $uid      = request()->cookie('uid');
      $wid      = request()->post('wid');
      if (is_null($wid)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 查询此Worker信息
      $worker = DB::table('v3_user_workers')
                  ->where('uid', $uid)
                  ->where('wid', $wid)
                  ->where('status', 1)
                  ->first();
      if (!$worker) {
        $json = $this->JSON(4901, 'Worker not exists.', null);
        return response($json);
      }
      // 查询最高等级限制
      $max_level = $this->sysconfig('worker_max_level');
      if ($worker->level + 1 > $max_level) {
        $json = $this->JSON(4902, 'Highest level now.', null);
        return response($json);
      }
      // 查询额外需求
      $extra = DB::table('v3_user_workers_upgrade')
                ->where('level', $worker->level)
                ->where('status', 1)
                ->first();
      if (!$extra) {
        $json = $this->JSON(4903, 'Highest level now.', null);
        return response($json);
      }
      // 计算基础需求：（等级+1）*10 的可莫尔
      $demands = [
        '5' => ($worker->level + 1) * 10
      ];
      // 叠加额外需求
      $extra_point = $extra->point;
      $extra_resources = json_decode($extra->resources, true);
      if (count($extra_resources) !== 0) {
        $demands = $demands + $extra_resources;
      }
      $_demands_detail = DB::table('v3_items')
                          ->whereIn('iid', array_keys($demands))
                          ->get();
      if (!$_demands_detail) {
        $json = $this->JSON(4904, 'Failed to get items info.', null);
        return response($json);
      }
      // 整理需求详细
      foreach ($_demands_detail as $key => $value) {
        $demands_detail[$value->iid] = $value;
      }
      $json = $this->JSON(0, null, ['msg'  => 'Success!', 'data' => array(
        'demands'        => $demands,
        'items'          => $demands_detail,
        'point'          => $extra_point,
      )]);
      return response($json);
    }

    // Worker 升级
    public function worker_upgrade() {
      $uid      = request()->cookie('uid');
      $wid      = request()->post('wid');
      if (is_null($wid)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 查询此Worker信息
      $worker = DB::table('v3_user_workers')
                  ->where('uid', $uid)
                  ->where('wid', $wid)
                  ->where('status', 1)
                  ->first();
      if (!$worker) {
        $json = $this->JSON(5001, 'Worker not exists.', null);
        return response($json);
      }
      // 查询最高等级限制
      $max_level = $this->sysconfig('worker_max_level');
      if ($worker->level + 1 > $max_level) {
        $json = $this->JSON(5002, 'Highest level now.', null);
        return response($json);
      }
      // 查询额外需求
      $extra = DB::table('v3_user_workers_upgrade')
                ->where('level', $worker->level)
                ->where('status', 1)
                ->first();
      if (!$extra) {
        $json = $this->JSON(5003, 'Highest level now.', null);
        return response($json);
      }
      // 计算基础需求：（等级+1）*10 的可莫尔
      $demands = [
        '5' => ($worker->level + 1) * 10
      ];
      // 叠加额外需求
      $extra_point = $extra->point;
      $extra_resources = json_decode($extra->resources, true);
      if (count($extra_resources) !== 0) {
        $demands = $demands + $extra_resources;
      }
      // 检查积分是否富裕
      $db_point = DB::table('v3_user_point')
                    ->where('uid', $uid)
                    ->where('point', '>=', $extra_point)
                    ->lockForUpdate()
                    ->value('point');
      if (!$db_point) {
        $json = $this->JSON(5004, 'Point is insufficient.', null);
        return response($json);
      }
      // 检查物品是否充裕
      foreach ($demands as $iid => $demand) {
        $db = BM::uid($uid)->has($iid, $demand, BM::VALID);
        if (!$db) {
          $json = $this->JSON(5005, 'Resource is insufficient.', $iid);
          return response($json);
        }
      }
      // 扣除积分
      if ($extra_point !== 0) {
        $_point = $db_point;  // 原始积分
        $db_point = DB::table('v3_user_point')
                      ->where('uid', $uid)
                      ->where('point', '>=', $extra_point)
                      ->lockForUpdate()
                      ->decrement('point', $extra_point);
        if (!$db_point) {
          // 恢复原始积分
          DB::table('v3_user_point')
            ->where('uid', $uid)
            ->lockForUpdate()
            ->update(['point' => $_point]);
          $json = $this->JSON(5004, 'Point is insufficient.', null);
          return response($json);
        }
      }
      // 扣除物品
      foreach($demands as $iid => $demand) {
        $db = BM::uid($uid)->reduce($iid, $demand, BM::LOCKED_FIRST);
        if (!$db) {
          $json = $this->JSON(5005, 'Failed to update resources amounts.', null);
          return response($json);
        }
      }
      // 升级Worker
      $worker = DB::table('v3_user_workers')
                  ->where('uid', $uid)
                  ->where('wid', $wid)
                  ->where('status', 1)
                  ->increment('level');
      if (!$worker) {
        $json = $this->JSON(5006, 'Failed to upgrade worker.', null);
        return response($json);
      }else{
        $json = $this->JSON(0, null, ['msg'  => 'Success!', 'data' => $db]);
        return response($json);
      }
    }


    // 礼包兑换
    public function gifts_reedem() {
      $uid          = request()->cookie('uid');
      $token        = request()->post('token');
      $captcha      = request()->post('captcha');
      if (is_null($token) || is_null($captcha)) {
        $json = $this->JSON(404, 'Not found.', null);
        return response($json, 404);
      }
      // 匹配验证码
      if (!Captcha::check($captcha)) {
        $json = $this->JSON(5101, 'Bad captcha.', null);
        return response($json);
      }
      $now = date('Y-m-d H:i:s');
      // 查询礼包信息
      $gifts = DB::table('v3_gifts')
                ->where('token', $token)
                ->where('starttime', '<=', date('Y-m-d H:i:s'))
                ->where('status', 1)
                ->sharedLock()
                ->first();
      // 礼物不存在
      if (!$gifts) {
        $json = $this->JSON(5102, 'Invaild iid.', null);
        return response($json);
      }
      // 判断兑换时间是否合法
      if ($gifts->endtime !== '1970-01-01 00:00:00' && $now > $gifts->endtime) {
        $json = $this->JSON(5103, 'Invaild time.', null);
        return response($json);
      }
      // 检查礼包存量
      if ($gifts->all_count !== 0) {
        $all = DB::table('v3_gifts_reedem_records')
                ->where('pid', $gifts->pid)
                ->sharedLock()
                ->count('rid');
        if ($all === false) {
          $json = $this->JSON(5104, 'System is busy.', null);
          return response($json);
        }
        if ($gifts->all_count - $all <= 0) {
          $json = $this->JSON(5105, 'Insuffcient gifts.', null);
          return response($json);
        }
      }
      // 检查兑换限制
      $specific_users = json_decode($gifts->specific_users, true);
      if (count($specific_users) !== 0 && !in_array($uid, $specific_users)) {
        $json = $this->JSON(5106, 'Failed to reedem.', null);
        return response($json);
      }
      // 检查是否已兑换
      $is_reedemed = DB::table('v3_gifts_reedem_records')
                      ->where('uid', $uid)
                      ->where('pid', $gifts->pid)
                      ->exists();
      if ($is_reedemed) {
        $json = $this->JSON(5109, 'Reedemed.', null);
        return response($json);
      }
      // 解析礼物信息
      $gifts_items = json_decode($gifts->items, true);
      // 更新物品信息
      foreach($gifts_items as $iid => $value) {
        if (!isset($value['type']) || $value['type'] === 'locked') {
          $type = BM::LOCKED;
        }else if($value['type'] === 'general') {
          $type = BM::GENERAL;
        }
        // 添加物品
        $db = BM::uid($uid)->add($iid, $value['count'], $type);
        // 恢复用户原始物品信息
        if (!$db) {
          $json = $this->JSON(5108, 'Failed to reedem.', null);
          return response($json);
        }
      }
      // // 查询用户资源
      // $user_items = DB::table('v3_user_items')
      //               ->where('uid', $uid)
      //               ->sharedLock()
      //               ->exists();
      // if (!$user_items) {
      //   // 首次注册购买信息
      //   $data = array(
      //     'uid' => $uid,
      //     'items' => $gifts->items
      //   );
      //   $db = DB::table('v3_user_items')->lockForUpdate()->insert($data);
      // }else{
      //   // 记录用户原物品信息
      //   $user_items = DB::table('v3_user_items')
      //                 ->where('uid', $uid)
      //                 ->sharedLock()
      //                 ->value('items');
      //   if (!$user_items) {
      //     $json = $this->JSON(5107, 'Failed to reedem.', null);
      //     return response($json);
      //   }
      //   // 更新物品信息
      //   foreach($gifts_items as $iid => $value) {
      //     // 查询是否有对应IID的记录
      //     $item_db = DB::table('v3_user_items')
      //                   ->where('uid', $uid)
      //                   ->sharedLock()
      //                   ->value("items->{$iid}->count");
      //     if ($item_db || $item_db === 0) {
      //       // 存在对应IID记录
      //       $db = DB::table('v3_user_items')
      //               ->where('uid', $uid)
      //               ->lockForUpdate()
      //               ->update(["items->{$iid}->count" => $item_db + $value['count']]);
      //     }else{
      //       // 创建对应记录
      //       $db = DB::table('v3_user_items')
      //               ->where('uid', $uid)
      //               ->lockForUpdate()
      //               ->update(['items'=> DB::raw("JSON_MERGE(items, '{\"{$iid}\":{\"count\": {$value['count']}}}')")]);
      //     }
      //     // 恢复用户原始物品信息
      //     if (!$db) {
      //       $user_items = DB::table('v3_user_items')
      //                     ->where('uid', $uid)
      //                     ->lockForUpdate()
      //                     ->update(['items' => $user_items]);
      //       $json = $this->JSON(5108, 'Failed to reedem.', null);
      //       return response($json);
      //     }
      //   }
      // }
      // 写入兑换记录
      $data = [
        'uid'     => $uid,
        'pid'     => $gifts->pid,
        'reedem_time' => date('Y-m-d H:i:s'),
        'status'  => 1
      ];
      DB::table('v3_gifts_reedem_records')->sharedLock()->insert($data);
      // 查询物品信息
      $gifts_items_keys = array_keys($gifts_items);
      $_items       = DB::table('v3_items')
                      ->whereIn('iid', $gifts_items_keys)
                      ->get();
      $items = [];
      foreach($_items as $value) {
        $items[$value->iid] = $value;
      }
      $data = [
        'items' => $items,
        'gifts' => $gifts_items,
        'description' => $gifts->description
      ];
      $json = $this->JSON(0, null, $data);
      return response($json);
    }
}
