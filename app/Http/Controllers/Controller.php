<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // 生成密码
    public function generate_password(string $password) {
      $password = md5($password.'tcAppsCheckIn@)!(');
      return $password;
    }

    // 生成Auth令牌
    public function generate_auth(string $password, string $uid, string $status) {
      // 中间件CheckAuth/APICheckAuth中有重复轮子
      return md5($password.$uid.$status.'2*4&%1^@HSIW}>./;2');
    }

    // 生成返回JSON
    public function JSON($errno, $error, $body) {
      return [
        'errno'     => $errno,
        'error'     => $error,
        'body'      => $body
      ];
    }
}
