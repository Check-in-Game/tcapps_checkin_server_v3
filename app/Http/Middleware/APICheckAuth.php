<?php

namespace App\Http\Middleware;

use DB;
use Closure;
use App\Http\Controllers\Common\UserAuth;

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
      $user = DB::table('v3_user_accounts')->where('uid', $uid)->first();
      // Controller/CheckAuth中有重复轮子
      if (!$user || $auth !== UserAuth::generate_auth($user->password, $user->uid, $user->status)) {
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
