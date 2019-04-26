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

    // 清理结算一个月前正常的签到数据
    private function checkin_list_settle() {
      $date = date('Y-m-d 00:00:00', strtotime('-1 week'));
      $db_prefix = env('DB_PREFIX');
      $charts = DB::table('lists_v2')
              ->join('user_accounts', 'lists_v2.uid', '=', 'user_accounts.uid')
              ->select(DB::raw("{$db_prefix}user_accounts.uid,{$db_prefix}user_accounts.username,sum({$db_prefix}lists_v2.worth) as allWorth"))
              ->where('check_time', '<', $date)
              ->where('tid', 0)     // 签到数据
              ->groupBy('user_accounts.username')
              ->get();
      foreach($charts as $chart) {
        $uid = $chart->uid;
        $worth = $chart->allWorth;
        $db = DB::table('lists_v2')
            ->where('uid', $uid)
            ->where('check_time', '<', $date)
            ->where('tid', 0)
            ->delete();
        if ($db) {
          $data = [
            'uid'         => $uid,
            'tid'         => 5,   // 结算
            'worth'       => $worth,
            'check_time'  => $date,
            'status'      => 1,
          ];
          $insert = DB::table('lists_v2')->insert($data);
          // 写入失败
          if (!$insert) {
            $insert = DB::table('lists_v2_settle_failure')->insert($data);
          }
        } else {
          continue;
        }
      }
      $json = $this->JSON(0, null, ['msg'  => 'Success!']);
      return response($json);
    }
}
