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
      $user = DB::table('user_accounts')->where('username', $username)->first();
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
    public function purchase(int $gid) {
      $now = date('Y-m-d H:i:s');
      // 查询商品信息
      $good = DB::table('shop')
            ->where('gid', $gid)
            ->where('status', 1)
            ->first();
      // 商品不存在
      if (!$good) {
        $json = $this->JSON(2501, 'Invaild gid.', null);
        return response($json);
      }
      $uid = Cookie::get('uid');
      // 获取用户信息
      $user = DB::table('user_accounts')
              ->where('uid', $uid)
              ->first();
      if ($user->status !== 1) {
        $json = $this->JSON(2502, 'Invaild user status.', null);
        return response($json);
      }
      // 查询用户余额
      $all_worth = DB::table('lists_v2')
                ->where('uid', $user->uid)
                ->sum('worth');
      $cost = DB::table('purchase_records')
            ->where('uid', $user->uid)
            ->sum('cost');
      $balance = $all_worth - $cost;
      // 余额不足
      if ($good->cost > $balance) {
        $json = $this->JSON(2503, 'Insuffcient funds.', null);
        return response($json);
      }
      // 判断购买时间是否合法
      if ($good->endtime !== '1970-01-01 00:00:00' && strtotime($good->endtime) < time()) {
        $json = $this->JSON(2504, 'Invaild time.', null);
        return response($json);
      }
      // 检查用户购买状态
      $pur_status = DB::table('purchase_records')
                  ->where('uid', $user->uid)
                  ->where('status', 0)
                  ->first();
      // 该用户有其他商品处于购买状态
      if ($pur_status) {
        $json = $this->JSON(2508, 'Other goods are checking out.', null);
        return response($json);
      }
      // 查询商品购买信息
      $all = DB::table('purchase_records')
            ->where('gid', $gid)
            ->count();
      $userR = DB::table('purchase_records')
            ->where('gid', $gid)
            ->where('uid', $user->uid)
            ->count();
      if ($all >= $good->all_count && $good->all_count !== 0) {
        $json = $this->JSON(2505, 'Insuffcient goods.', null);
        return response($json);
      }
      if ($userR >= $good->rebuy && $good->rebuy !== 0) {
        $json = $this->JSON(2506, 'Purchasing times limited('. $userR .').', null);
        return response($json);
      }
      // 创建购买记录
      $data = [
        'uid'           => $user->uid,
        'gid'           => $good->gid,
        'cost'          => $good->cost,
        'purchase_time' => date('Y-m-d H:i:s'),
        'status'        => 1
      ];
      $pid = DB::table('purchase_records')->insertGetId($data);
      if (!$pid) {
        $json = $this->JSON(2507, 'Unknown error.', null);
        return response($json);
      }else{
        $json = $this->JSON(0, null, ['msg' => 'Success!']);
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
        $data = [
          'username'  => $username,
        ];
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

    // 佩戴勋章
    public function badge_wear() {
      $uid    = request()->cookie('uid');
      $bid    = request()->post('bid');
      // 查询佩戴数量限制
      $limit  = DB::table('system')->where('skey', 'badges_wear_limit')->first();
      if (!$limit) {
        $limit = 1;
      }else{
        $limit = $limit->svalue;
      }
      // 查询用户是否拥有该勋章
      $ownership = DB::table('badges')
                ->join('purchase_records', 'badges.gid', '=', 'purchase_records.gid')
                ->where('purchase_records.uid', $uid)
                ->where('purchase_records.status', 1)
                ->first();
      if (!$ownership) {
        $json = $this->JSON(3401, 'The ownership of this badge is abnormal.', null);
        return response($json);
      }
      // 查询用户佩戴信息
      $wore = DB::table('badges_wear')->where('uid', $uid)->first();
      // 信息不存在直接写入
      if(!$wore) {
        $data = [
          'uid'           => $uid,
          'bid'           => $bid,
          'update_time'   => date('Y-m-d H:i:s'),
        ];
        $insert = DB::table('badges_wear')->insert($data);
        if (!$insert) {
          $json = $this->JSON(3402, 'Failed to wear badge.', null);
          return response($json);
        }else{
          $json = $this->JSON(0, null, ['msg'  => 'Success!']);
          return response($json);
        }
      }
      // 检查佩戴数量
      $wore = explode(',', $wore->bid);
      $count = count($wore);
      // 限制1直接更新
      if ($limit == 1) {
        $data = [
          'uid'           => $uid,
          'bid'           => $bid,
          'update_time'   => date('Y-m-d H:i:s'),
        ];
      }else{
        // 限制不为1，查询是否超额佩戴
        if ($count >= $limit) {
          $json = $this->JSON(3404, 'The count of wore badges is limited.', null);
          return response($json);
        }else{
          $tmp = $wore;
          $tmp[] = $bid;
          $bid = join(',', $tmp);
          $data = [
            'uid'           => $uid,
            'bid'           => $bid,
            'update_time'   => date('Y-m-d H:i:s'),
          ];
        }
      }
      $update = DB::table('badges_wear')->where('uid', $uid)->update($data);
      if (!$update) {
        $json = $this->JSON(3403, 'Failed to wear badge.', null);
        return response($json);
      }else{
        $json = $this->JSON(0, null, ['msg'  => 'Success!', 'wearing' => $bid]);
        return response($json);
      }
    }

    // 取消佩戴勋章
    public function badge_takeoff() {
      $uid    = request()->cookie('uid');
      $bid    = request()->post('bid');
      // 查询用户佩戴信息
      $wore = DB::table('badges_wear')->where('uid', $uid)->first();
      if (!$wore) {
        $json = $this->JSON(3405, 'Failed to query badges.', null);
        return response($json);
      }
      $badges = explode(',', $wore->bid);
      if (!in_array($bid, $badges)) {
        $json = $this->JSON(3406, 'This badge is not been wore.', null);
        return response($json);
      }
      // 待删除bid
      $_bid = array_search($bid, $badges);
      unset($badges[$_bid]);
      $badges = join(',', $badges);
      $data = [
        'uid'           => $uid,
        'bid'           => $badges,
        'update_time'   => date('Y-m-d H:i:s'),
      ];
      $update = DB::table('badges_wear')->where('uid', $uid)->update($data);
      if (!$update) {
        $json = $this->JSON(3407, 'Failed to take off badge.', null);
        return response($json);
      }else{
        $json = $this->JSON(0, null, ['msg'  => 'Success!']);
        return response($json);
      }
    }
}
