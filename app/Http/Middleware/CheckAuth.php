<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;
use Illuminate\Support\Facades\DB;

class CheckAuth
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
          return redirect('login');
        }
        // 检查匹配
        // 获取用户名密码
        $user = DB::table('user_accounts')->where('uid', $uid)->first();
        // Controller/APICheckAuth中有重复轮子
        if (!$user || $auth !== md5($user->password.$user->uid.$user->status.'2*4&%1^@HSIW}>./;2')) {
          $data = [
            'error'     => '签权错误',
            'content'   => '您的用户签权已经失效了，请重新登录！'
          ];
          Cookie::queue(cookie()->forget('uid'));
          Cookie::queue(cookie()->forget('auth'));
          return redirect('alert/签权错误/您的用户签权已经失效了，请重新登录！');
        }
        // 获取管理权限
        $admin = DB::table('admin_level')->where('uid', $uid)->where('status', '<>', -1)->first();
        view()->share('_user', $user);
        view()->share('_admin', $admin);
        return $next($request);
    }
}
