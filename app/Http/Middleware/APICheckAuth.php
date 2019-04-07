<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class APICheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
      // 检查登录状态
      $uid = $request->cookie('uid');
      $auth = $request->cookie('auth');
      if (!$uid || !$auth) {
        $json =  [
          'errno'     => 2401,
          'error'     => 'Invaild auth.',
          'body'      => null
        ];
        return response($json);
      }
      // 检查匹配
      // 获取用户名密码
      $user = DB::table('user_accounts')->where('uid', $uid)->first();
      // Controller/CheckAuth中有重复轮子
      if (!$user || $auth !== md5($user->password.$user->uid.$user->status.'2*4&%1^@HSIW}>./;2')) {
        $json =  [
          'errno'     => 2402,
          'error'     => 'Invaild auth.',
          'body'      => null
        ];
        return response($json)
              ->withCookie(cookie()->forget('uid'))
              ->withCookie(cookie()->forget('auth'));
      }
      return $next($request);
    }
}
