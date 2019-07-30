<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // 生成密码
    public function generate_password(string $password) {
      $password = md5($password.env('APP_PWD_CONFUSION'));
      return $password;
    }

    // 生成Auth令牌
    public function generate_auth(string $password, string $uid, string $status) {
      // 中间件CheckAuth/APICheckAuth中有重复轮子
      return md5($password.$uid.$status.env('APP_AUTH_CONFUSION'));
    }

    // 生成返回JSON
    public function JSON(string $errno, $error, $body) {
      return [
        'errno'     => $errno,
        'error'     => $error,
        'body'      => $body
      ];
    }

    // 查询系统设置
    public function sysconfig(string $name) {
      $sys = DB::table('system')
              ->where('skey', $name)
              ->value('svalue');
      return $sys;
    }
}
