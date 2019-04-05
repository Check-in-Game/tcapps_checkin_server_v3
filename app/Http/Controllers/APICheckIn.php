<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class APICheckIn extends Controller {

    // 获取Token
    public function get_token($username, $b64password) {
      $password = base64_decode($b64password);
      if (mb_strlen($username) > 16 || mb_strlen($username) < 5 || mb_strlen($password) > 16 || mb_strlen($password) < 8) {
        return $this->JSON(2101, 'Incorrect username or password.', ['token' => null]);
      }
      // 获取用户名密码
      $user = DB::table('user_accounts')->where('username', $username)->first();
      if (!$user) {
        return $this->JSON(2102, 'Incorrect username or password.', ['token' => null]);
      }
      // 匹配密码
      if ($user->password !== $this->generate_password($password)) {
        return $this->JSON(2103, 'Incorrect username or password.', ['token' => null]);
      }
      // 用户状态
      if ($user->status === -1) {
        return $this->JSON(2106, 'Incorrect user status.', ['token' => null]);
      }
      // 查询Token
      $db = DB::table('tokens_v2')->where('uid', $user->uid)->first();
      if (!$db) {
        // 用户信息在Token表中不存在
        return $this->JSON(2104, 'Incorrect user infomation.', ['token' => null]);
      }
      // 查询Token是否需要更新
      if ($db->status !== 1) {
        // 需要更新
        $time = time();
        $token = md5($time . $password).'@'.$username;
        $data = array(
          'token'   => $token,
          'status'  => 1
        );
        $db = DB::table('tokens_v2')->where('uid', $user->uid)->update($data);
        if ($db) {
          return $this->JSON(0, null, ['token' => $token]);
        }else{
          return $this->JSON(2105, 'Failed to generate token.', ['token' => null]);
        }
      }else{
        return $this->JSON(0, null, ['token' => $db->token]);
      }
    }

    // 签到
    public function check_in($username, $token) {
      // 判断基本长度
      if (mb_strlen($username) > 16 || mb_strlen($username) < 5 || mb_strlen($token) > 49 || mb_strlen($token) < 38) {
        return $this->JSON(2201, 'Incorrect username or token.', null);
      }
      // 查询用户信息
      $user = DB::table('user_accounts')->where('username', $username)->first();
      if (!$user) {
        return $this->JSON(2202, 'Incorrect username or token.', null);
      }
      // 判断用户状态
      if ($user->status === -1) {
        return $this->JSON(2203, 'Incorrect user status.', null);
      }
      // 查询上次签到时间
      $db = DB::table('lists_v2')->where('uid', $user->uid)->orderBy('check_time', 'desc')->first();
      if ($db && time() - strtotime($db->check_time) < 60 * 5) {
        return $this->JSON(2205, 'Incorrect check-in time.', null);
      }
      // 查询签到口令
      $db = DB::table('tokens_v2')->where('uid', $user->uid)->where('status', 1)->first();
      if (!$db) {
        return $this->JSON(2204, 'Incorrect user infomation.', null);
      }
      // 对比token
      if ($token !== $db->token) {
        return $this->JSON(2206, 'Incorrect username or token.', null);
      }
      // 查询活动
      $worth = 1;
      $db = DB::table('activity')->where('starttime', '<=', time())->where('endtime', '>=', time())->where('status', 1)->first();
      if ($db) {
        $min = $db->min_worth;
        $max = $db->max_worth;
        $worth = mt_rand($min, $max);
      }
      $data = array(
        'uid'     => $user->uid,
        'tid'     => 0,       // 日常签到
        'worth'   => $worth,
        'check_time'  => date('Y-m-d H:i:s')
      );
      $db = DB::table('lists_v2')->insert($data);
      if (!$db) {
        return $this->JSON(2207, 'Unknown error.', null);
      }else{
        // 更新Token状态
        $data = array(
          'status'  => 0
        );
        DB::table('tokens_v2')->where('uid', $user->uid)->update($data);
        return $this->JSON(0, null, ['msg'  => 'Success!']);
      }
    }
}
