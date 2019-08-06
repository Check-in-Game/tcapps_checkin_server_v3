<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

class UserAuth extends Controller {

    // 生成密码
    static public function generate_password(string $password) : string {
      return md5($password.env('APP_PWD_CONFUSION'));
    }

    // 生成Auth令牌
    static public function generate_auth(string $password, string $uid, string $status) : string {
      // 中间件CheckAuth/APICheckAuth中有重复轮子
      return md5($password.$uid.$status.env('APP_AUTH_CONFUSION'));
    }

    // 邮箱验证代码
    static public function email_code(string $now, int $uid, string $email, $code = false) {
      // 生成代码
      $real = md5(env('APP_AUTH_CONFUSION').$email.$now.env('APP_PWD_CONFUSION').$uid);
      if ($code === false) {
        // 生成验证代码
        return $real;
      }else{
        // 验证
        return $code === $real ? true : false;
      }
    }

}
