<?php

namespace App\Http\Controllers;

use Captcha;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class APICheckIn extends Controller {

    // 获取Token
    public function get_token($username, $b64password) {
      $password = base64_decode($b64password);
      if (mb_strlen($username) > 16 || mb_strlen($username) < 5 || mb_strlen($password) > 16 || mb_strlen($password) < 8) {
        $json = $this->JSON(2101, 'Incorrect username or password.', ['token' => null]);
        return response($json);
      }
      // 获取用户名密码
      $user = DB::table('user_accounts')->where('username', $username)->first();
      if (!$user) {
        $json = $this->JSON(2102, 'Incorrect username or password.', ['token' => null]);
        return response($json);
      }
      // 匹配密码
      if ($user->password !== $this->generate_password($password)) {
        $json = $this->JSON(2103, 'Incorrect username or password.', ['token' => null]);
        return response($json);
      }
      // 用户状态
      if ($user->status === -1) {
        $json = $this->JSON(2106, 'Incorrect user status.', ['token' => null]);
        return response($json);
      }
      // 查询Token
      $db = DB::table('tokens_v2')->where('uid', $user->uid)->first();
      if (!$db) {
        $time = time();
        $token = md5($time . $password).'@'.$username;
        $data = array(
          'uid'     => $user->uid,
          'token'   => $token,
          'status'  => 1
        );
        $db = DB::table('tokens_v2')->insert($data);
        if (!$db) {
          $json = $this->JSON(2105, 'Failed to generate token.', ['token' => null]);
          return response($json);
        }else{
          $json = $this->JSON(0, null, ['token' => $token]);
          return response($json);
        }
      }else if ($db->status !== 1) {
        // 需要更新
        $time = time();
        $token = md5($time . $password).'@'.$username;
        $data = array(
          'token'   => $token,
          'status'  => 1
        );
        $db = DB::table('tokens_v2')->where('uid', $user->uid)->update($data);
        if ($db) {
          $json = $this->JSON(0, null, ['token' => $token]);
          return response($json);
        }else{
          $json = $this->JSON(2105, 'Failed to generate token.', ['token' => null]);
          return response($json);
        }
      }else{
        $json = $this->JSON(0, null, ['token' => $db->token]);
        return response($json);
      }
    }

    // 签到
    public function check_in($username, $token) {
      // 判断基本长度
      if (mb_strlen($username) > 16 || mb_strlen($username) < 5 || mb_strlen($token) > 49 || mb_strlen($token) < 38) {
        $json = $this->JSON(2201, 'Incorrect username or token.', null);
        return response($json);
      }
      // 查询用户信息
      $user = DB::table('user_accounts')->where('username', $username)->first();
      if (!$user) {
        $json = $this->JSON(2202, 'Incorrect username or token.', null);
        return response($json);
      }
      // 判断用户状态
      $banedStatus = [-1, 0];
      if (in_array($user->status, $banedStatus)) {
        $json = $this->JSON(2203, 'Incorrect user status.', null);
        return response($json);
      }
      // 查询上次签到时间
      $db = DB::table('lists_v2')->where('uid', $user->uid)->orderBy('check_time', 'desc')->where('tid', 0)->first();
      if ($db && time() - strtotime($db->check_time) < 60 * 10) {
        $json = $this->JSON(2205, 'Incorrect check-in time.', null);
        return response($json);
      }
      // 查询签到口令
      $db = DB::table('tokens_v2')->where('uid', $user->uid)->where('status', 1)->first();
      if (!$db) {
        $json = $this->JSON(2204, 'Incorrect user infomation.', null);
        return response($json);
      }
      // 对比token
      if ($token !== $db->token) {
        $json = $this->JSON(2206, 'Incorrect username or token.', null);
        return response($json);
      }
      $worth = 1;
      // 默认最低分
      $min = 1;
      // 默认最高分
      $max = 10;
      // 查询活动
      $db = DB::table('activity')
          ->where('starttime', '<=', date('Y-m-d H:i:s'))
          ->where('endtime', '>=', date('Y-m-d H:i:s'))
          ->where('status', 1)
          ->first();
      if ($db) {
        $min = $db->min_worth;
        $max = $db->max_worth;
      }
      $worth = mt_rand($min, $max);
      // 查询签到次数
      $count = DB::table('lists_v2')->where('uid', $user->uid)->count();
      // 查询新用户加成
      $buff = DB::table('system')->where('skey', 'newhand_support_pre_200')->first();
      // 积分基数小于等于100有新手加成
      if ($buff && $count <= 200) {
        $worth = ($worth > 200) ? $worth : $worth * $buff->svalue;
      }
      // 查询用户佩戴勋章
      $badges = DB::table('badges_wear')->where('uid', $user->uid)->first();
      if( $badges && !empty($badges->bid) ) {
        $badges = explode(',', $badges->bid);
        foreach ($badges as $key => $bid) {
          $times = DB::table('badges')
                ->join('effects', 'badges.eid', '=', 'effects.eid')
                ->where('badges.bid', $bid)
                ->value('times');
          if ($times) {
            $worth = $worth * $times;
          }
        }
        $worth = round($worth);
      }
      $check_time = date('Y-m-d H:i:s');
      $data = array(
        'uid'     => $user->uid,
        'tid'     => 0,       // 日常签到
        'worth'   => $worth,
        'check_time'  => $check_time
      );
      $db = DB::table('lists_v2')->insert($data);
      if (!$db) {
        $json = $this->JSON(2207, 'Unknown error.', null);
        return response($json);
      }else{
        // 更新Token状态
        $data = array(
          'status'  => 0
        );
        $return = [
          'msg'         => 'Success!',
          'worth'       => $worth,
          'check_time'  => $check_time
        ];
        DB::table('tokens_v2')->where('uid', $user->uid)->update($data);
        $json =  $this->JSON(0, null, $return);
        return response($json);
      }
    }

    // 清灰
    public function clean() {
      $uid      = request()->cookie('uid');
      $captcha  = request()->post('captcha');
      // 查询登录状态是否正常
      if (is_null($uid) || is_null($captcha)) {
        $json = $this->JSON(3901, 'Bad auth.', null);
        return response($json);
      }
      // 匹配验证码
      if (!Captcha::check($captcha)) {
        $json = $this->JSON(3906, 'Bad captcha.', null);
        return response($json);
      }
      // 查询用户信息
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      if (!$user) {
        $json = $this->JSON(3902, 'Bad user id.', null);
        return response($json);
      }
      // 判断用户状态
      $banedStatus = [-1, 0];
      if (in_array($user->status, $banedStatus)) {
        $json = $this->JSON(3903, 'Incorrect user status.', null);
        return response($json);
      }
      // 查询上次擦灰时间
      $db = DB::table('lists_v2')->where('uid', $user->uid)->orderBy('check_time', 'desc')->where('tid', 7)->first();
      if ($db && time() - strtotime($db->check_time) < 60 * 60 * 18) {
        $json = $this->JSON(3904, 'Incorrect clean time.', null);
        return response($json);
      }
      // 固定擦灰500分
      $worth = 500;
      // 查询签到次数
      $count = DB::table('lists_v2')->where('uid', $user->uid)->count();
      // 查询用户佩戴勋章
      // $badges = DB::table('badges_wear')->where('uid', $user->uid)->first();
      // if( $badges && !empty($badges->bid) ) {
      //   $badges = explode(',', $badges->bid);
      //   foreach ($badges as $key => $bid) {
      //     $times = DB::table('badges')
      //           ->join('effects', 'badges.eid', '=', 'effects.eid')
      //           ->where('badges.bid', $bid)
      //           ->value('times');
      //     if ($times) {
      //       $worth = $worth * $times;
      //     }
      //   }
      //   $worth = round($worth);
      // }
      $check_time = date('Y-m-d H:i:s');
      $data = array(
        'uid'         => $user->uid,
        'tid'         => 7,       // 擦灰加值
        'worth'       => $worth,
        'check_time'  => $check_time
      );
      $db = DB::table('lists_v2')->insert($data);
      if (!$db) {
        $json = $this->JSON(3905, 'Unknown error.', null);
        return response($json);
      }else{
        $json =  $this->JSON(0, null, ['msg'  => 'Success!']);
        return response($json);
      }
    }
}
