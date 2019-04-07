<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class APIAdminCheckAuth
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
      // 获取管理权限
      $admin = DB::table('admin_level')->where('uid', $uid)->where('status', '<>', -1)->first();
      if (!$admin || $admin->level <= 0) {
        $json =  [
          'errno'     => 2601,
          'error'     => 'Invaild admin auth.',
          'body'      => null
        ];
        return response($json);
      }
      return $next($request);
    }
}
