<?php

namespace App\Http\Controllers;

use Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class APIAdmin extends Controller {
    // Conpensate
    public function conpensate(string $uid, int $count) {
      if ($uid == 0) {
        // 全体补偿
        $uids = DB::table('user_accounts')->select('uid')->get();
        foreach ($uids as $value) {
          $uid = $value->uid;
          $data = array(
            'uid'     => $uid,
            'tid'     => 3,       // 系统补偿
            'worth'   => $count,
            'check_time'  => date('Y-m-d H:i:s')
          );
          $db = DB::table('lists_v2')->insert($data);
          if (!$db) {
            $json = $this->JSON(2602, 'Failed to insert into database.', null);
            return response($json);
          }
        }
      }else{
        // 个别补偿
        $uids = explode(',', $uid);
        foreach ($uids as $uid) {
          $data = array(
            'uid'     => $uid,
            'tid'     => 3,       // 系统补偿
            'worth'   => $count,
            'check_time'  => date('Y-m-d H:i:s')
          );
          $db = DB::table('lists_v2')->insert($data);
          if (!$db) {
            $json = $this->JSON(2602, 'Failed to insert into database.', null);
            return response($json);
          }
        }
      }
      $json = $this->JSON(0, null, ['msg' => 'Success.']);
      return response($json);
    }

    // 增加活动
    function activity_add(string $starttime, string $endtime, int $min, int $max) {
        // 判断日期格式
        if (!strtotime($starttime) || !strtotime($endtime) ) {
          $json = $this->JSON(2603, 'Invaild datetime.', null);
          return response($json);
        }
        // 格式化日期
        $starttime = date('Y-m-d H:i:s', strtotime($starttime));
        $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
        if ($min <= 0) {
          $json = $this->JSON(2604, "Invaild maximun({$min}).", null);
          return response($json);
        }
        if ($max <= $min) {
          $json = $this->JSON(2605, "Invaild maximun({$max}).", null);
          return response($json);
        }
        $data = [
          'starttime' => $starttime,
          'endtime'   => $endtime,
          'min_worth' => $min,
          'max_worth' => $max,
          'status'    => 1
        ];
        $db = DB::table('activity')->insert($data);
        if ($db) {
          $json = $this->JSON(0, null, ['msg' => 'Success.']);
          return response($json);
        }else{
          $json = $this->JSON(2606, 'Failed to insert into database.', null);
          return response($json);
        }
    }

    // 增加商品
    function goods_add(string $name, int $cost, string $starttime, string $endtime, int $tid, int $sid, int $rebuy, int $all_count, string $description, string $image) {
        $image = $image === 'null' ? '' : $image;
        // 判断日期格式
        if (!strtotime($starttime) || !strtotime($endtime) ) {
          $json = $this->JSON(2607, 'Invaild datetime.', null);
          return response($json);
        }
        // 格式化日期
        $starttime = date('Y-m-d H:i:s', strtotime($starttime));
        $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
        if ($cost < 0 || $tid < 0 || $sid < 0 || $rebuy < 0 || $all_count < 0) {
          $json = $this->JSON(2608, "Invaild params.", null);
          return response($json);
        }
        $data = [
          'gname'         => $name,
          'cost'          => $cost,
          'starttime'     => $starttime,
          'endtime'       => $endtime,
          'tid'           => $tid,
          'sid'           => $sid,
          'all_count'     => $all_count,
          'image'         => $image,
          'rebuy'         => $rebuy,
          'description'   => $description,
          'status'        => 1
        ];
        $db = DB::table('shop')->insert($data);
        if ($db) {
          $json = $this->JSON(0, null, ['msg' => 'Success.']);
          return response($json);
        }else{
          $json = $this->JSON(2609, 'Failed to insert into database.', null);
          return response($json);
        }
    }
}
