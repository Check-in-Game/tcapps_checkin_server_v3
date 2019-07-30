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

}
