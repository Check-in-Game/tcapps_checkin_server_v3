<?php

namespace App\Http\Middleware;

use DB;
use Cookie;
use Closure;
use App\Http\Controllers\Common\UserAuth;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string    $level
     * @return mixed
     */
    public function handle($request, Closure $next, string $level = 'wall') {
        // 检查登录状态
        $uid = $request->cookie('uid');
        $auth = $request->cookie('auth');
        if ($level === 'wall') {
          if (!$uid || !$auth) {
            return redirect('login');
          }
        }
        if (!is_null($uid) && !is_null($auth)) {
          // 检查匹配
          // 获取用户名密码
          $user = DB::table('v3_user_accounts')->where('uid', $uid)->first();
          // 无法查询到信息
          if (!$user) {
            Cookie::queue(cookie()->forget('uid'));
            Cookie::queue(cookie()->forget('auth'));
            return redirect("login");
          }
          if ($user->status == -2) {
            $data = [
              'error'     => '账户封禁',
              'content'   => '该账户已经被封禁！'
            ];
            Cookie::queue(cookie()->forget('uid'));
            Cookie::queue(cookie()->forget('auth'));
            return redirect("alert/{$data['error']}/{$data['content']}");
          }
          // Controller/APICheckAuth中有重复轮子
          if (!$user || $auth !== UserAuth::generate_auth($user->password, $user->uid, $user->status)) {
            $data = [
              'error'     => '签权错误',
              'content'   => '您的登录状态失效了，请重新登录！'
            ];
            Cookie::queue(cookie()->forget('uid'));
            Cookie::queue(cookie()->forget('auth'));
            return redirect("alert/{$data['error']}/{$data['content']}");
          }
          // 获取管理权限
          $admin = DB::table('admin_level')->where('uid', $uid)->where('status', '<>', -1)->first();
          view()->share('_user', $user);
          view()->share('_admin', $admin);
        }
        return $next($request);
    }
}
