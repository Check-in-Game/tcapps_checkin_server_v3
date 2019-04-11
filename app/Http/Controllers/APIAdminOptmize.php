<?php

namespace App\Http\Controllers;

use Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class APIAdminOptmize extends Controller {
    public function optmize(string $project) {
      if (method_exists($this, $project)) {
        return $this->$project();
      }else{
        $json = $this->JSON(2801, 'Can not find this project.', null);
        return response($json);
      }
    }

    // 删除冗余用户与相关数据
    private function users() {
      $uids = [];
      // 查询过期用户
      $tokens_v2 = DB::table('tokens_v2')
                  ->where('token', '')
                  ->orWhere('status', -1)
                  ->get();
      foreach ($tokens_v2 as $key => $value) {
        if (!in_array($value->uid, $uids)) {
          $uids[] = $value->uid;
        }
      }
      foreach ($uids as $key => $uid) {
        DB::table('tokens_v2')->where('uid', $uid)->delete();
        DB::table('admin_level')->where('uid', $uid)->delete();
        DB::table('lists_v2')->where('uid', $uid)->delete();
        DB::table('purchase_records')->where('uid', $uid)->delete();
        DB::table('user_accounts')->where('uid', $uid)->delete();
      }
      $json = $this->JSON(0, null, ['msg'  => 'Success!']);
      return response($json);
    }
}
