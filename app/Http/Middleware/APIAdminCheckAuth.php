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
    public function handle($request, Closure $next, string $need_rights = null) {
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
      // 检查权限需要
      if ($need_rights !== null) {
        // 查询是否拥有站长权限
        if ($admin->level !== 255) {
          // 查询是否拥有授权的站长权限
          $right = DB::table('admin_register')
                ->join('admin_rights_list', 'admin_rights_list.rid', '=', 'admin_register.rid')
                ->where('admin_register.uid', $uid)
                ->where('admin_register.status', 1)
                ->where('admin_rights_list.rname', 'site_owner')
                ->exists();
          // 没有站长权限
          if (!$right) {
            // 分别检查需要的权限
            $need_rights = explode('+', $need_rights);
            foreach ($need_rights as $key => $right) {
              $rig = DB::table('admin_register')
              ->join('admin_rights_list', 'admin_rights_list.rid', '=', 'admin_register.rid')
              ->where('admin_register.uid', $uid)
              ->where('admin_register.status', 1)
              ->where('admin_rights_list.rname', $right)
              ->exists();
              // 没有相应权限
              if (!$rig) {
                $json =  [
                  'errno'     => 1001,
                  'error'     => "[{$right}] right needed.",
                  'body'      => null
                ];
                return response($json);
              }
            }
          }
        }
      }
      // 传递参数到控制器
      $data['_admin'] =  $admin;
      $request->attributes->add($data);
      return $next($request);
    }
}
