<?php

namespace App\Http\Controllers;

use Cookie;
use Captcha;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class APIAdmin extends Controller {
    // 积分数据迁移
    public function migrate_points() {
      // 获取总积分
      $users_point = DB::table('lists_v2')
                        ->where('status', 1)
                        ->groupBy('uid')
                        ->select(DB::raw('uid, sum(worth) as worth'))
                        ->get();
      // 获取消耗积分
      $users_comsumed_point = DB::table('purchase_records')
                                ->where('status', 1)
                                ->groupBy('uid')
                                ->select(DB::raw('uid, sum(cost) as cost'))
                                ->get();
      // 建立数据表
      $point = [];
      // 清算获得积分
      foreach ($users_point as $key => $value) {
        $point[$value->uid] = (int) $value->worth;
      }
      // 清算消耗积分
      foreach ($users_comsumed_point as $key => $value) {
        if (isset($point[$value->uid])) {
          $point[$value->uid] -= $value->cost;
        }else{
          continue;
        }
      }
      // 写入数据库
      foreach ($point as $uid => $value) {
        $data = array(
          'uid'   => $uid,
          // 比例 1000:1 补偿2积分
          'point' => floor($value / 1000) + 2
        );
        $db = DB::table('v3_user_point')->sharedLock()->insert($data);
        if (!$db) {
          $json = $this->JSON(4801, 'Failed to add data to database.', null);
          return response($json);
        }
      }
      $json = $this->JSON(0, null, ['msg'  => 'Success!', null]);
      return response($json);
    }

    // 积分数据迁移
    public function migrate_badges() {
      // 勋章ID映射表
      $map = [
        1 => 6,
        2 => 7,
        3 => 8,
        4 => 9,
        5 => 10,
        6 => 11,
        7 => 12,
      ];
      // 获取勋章列表
      $users_point = DB::table('purchase_records')
                        ->join('user_accounts', 'user_accounts.uid', '=', 'purchase_records.uid')
                        ->whereIn('purchase_records.gid', range(1, 7))
                        ->where('purchase_records.status', 1)
                        ->select(['purchase_records.uid', 'purchase_records.gid'])
                        ->get();
      // 整理数据
      $user_items = [];
      foreach ($users_point as $key => $value) {
        if (isset($user_items[$value->uid])) {
          $user_items[$value->uid][$map[$value->gid]] = ['count' => 1];
        }else{
          $user_items[$value->uid] = [$map[$value->gid] => ['count' => 1]];
        }
      }
      // 写入数据库
      foreach ($user_items as $uid => $items) {
        $data = array(
          'uid'   => $uid,
          'items' => json_encode($items)
        );
        $db = DB::table('v3_user_items')->sharedLock()->insert($data);
        if (!$db) {
          $json = $this->JSON(4802, 'Failed to add data to database.', null);
          return response($json);
        }
      }
      $json = $this->JSON(0, null, ['msg'  => 'Success!', null]);
      return response($json);
    }
    // // Conpensate
    // public function conpensate() {
    //   $uid = request()->post('uid');
    //   $count = request()->post('count');
    //   $tid = request()->post('tid');
    //   $captcha = request()->post('captcha');
    //   if (is_null($uid) || is_null($count) || is_null($tid) || is_null($captcha)) {
    //     $json = $this->JSON(2611, 'Lost some infomation.', null);
    //     return response($json);
    //   }
    //   if (!Captcha::check($captcha)) {
    //     $json = $this->JSON(2613, 'Bad captcha.', null);
    //     return response($json);
    //   }
    //   if ($uid == 0) {
    //     // 全体补偿
    //     $uids = DB::table('user_accounts')->select('uid')->get();
    //     foreach ($uids as $value) {
    //       $uid = $value->uid;
    //       $data = array(
    //         'uid'     => $uid,
    //         'tid'     => $tid,       // 系统补偿
    //         'worth'   => $count,
    //         'check_time'  => date('Y-m-d H:i:s')
    //       );
    //       $db = DB::table('lists_v2')->insert($data);
    //       if (!$db) {
    //         $json = $this->JSON(2602, 'Failed to insert into database.', null);
    //         return response($json);
    //       }
    //     }
    //   }else{
    //     // 个别补偿
    //     $uids = explode(',', $uid);
    //     foreach ($uids as $uid) {
    //       // 查询用户是否存在
    //       $user = DB::table('user_accounts')->where('uid', $uid)->first();
    //       if (!$user) {
    //         $json = $this->JSON(2612, "UID({$uid}) cannot be found.", null);
    //         return response($json);
    //       }
    //     }
    //     foreach ($uids as $uid) {
    //       // 查询用户是否存在
    //       $data = array(
    //         'uid'     => $uid,
    //         'tid'     => $tid,       // 系统补偿
    //         'worth'   => $count,
    //         'check_time'  => date('Y-m-d H:i:s')
    //       );
    //       $db = DB::table('lists_v2')->insert($data);
    //       if (!$db) {
    //         $json = $this->JSON(2602, 'Failed to insert into database.', null);
    //         return response($json);
    //       }
    //     }
    //   }
    //   $json = $this->JSON(0, null, ['msg' => 'Success.']);
    //   return response($json);
    // }
    //
    // // 搜索活动
    // function activity_search(int $aid) {
    //     $db = DB::table('activity')->where('aid', $aid)->first();
    //     if ($db) {
    //       $json = $this->JSON(0, null, ['msg' => 'Success.', 'data' => $db]);
    //       return response($json);
    //     }else{
    //       $json = $this->JSON(2617, 'Failed to find this AID.', null);
    //       return response($json);
    //     }
    // }
    //
    // // 增加活动
    // function activity_add() {
    //   if (!isset($_POST['starttime']) || empty($_POST['starttime'])
    //     || !isset($_POST['endtime']) || empty($_POST['endtime'])
    //     || !isset($_POST['min_worth']) || empty($_POST['min_worth'])
    //     || !isset($_POST['max_worth']) || empty($_POST['max_worth'])
    //     || !isset($_POST['status']) || empty($_POST['status'])
    //   ){
    //     $json = $this->JSON(2618, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $starttime  = $_POST['starttime'];
    //   $endtime    = $_POST['endtime'];
    //   $min_worth  = $_POST['min_worth'];
    //   $max_worth  = $_POST['max_worth'];
    //   $status     = $_POST['status'];
    //   // 判断日期格式
    //   if (!strtotime($starttime) || !strtotime($endtime) ) {
    //     $json = $this->JSON(2603, 'Invaild datetime.', null);
    //     return response($json);
    //   }
    //   // 格式化日期
    //   $starttime = date('Y-m-d H:i:s', strtotime($starttime));
    //   $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
    //   if ($min_worth < 0) {
    //     $json = $this->JSON(2604, "Invaild minimun({$min_worth}).", null);
    //     return response($json);
    //   }
    //   if ($max_worth < $min_worth) {
    //     $json = $this->JSON(2605, "Invaild maximun({$max_worth}).", null);
    //     return response($json);
    //   }
    //   $data = [
    //     'starttime' => $starttime,
    //     'endtime'   => $endtime,
    //     'min_worth' => $min_worth,
    //     'max_worth' => $max_worth,
    //     'status'    => $status
    //   ];
    //   $db = DB::table('activity')->insert($data);
    //   if ($db) {
    //     $json = $this->JSON(0, null, ['msg' => 'Success.']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2606, 'Failed to insert into database.', null);
    //     return response($json);
    //   }
    // }
    //
    // // 修改活动
    // function activity_update() {
    //   if (!isset($_POST['aid']) || empty($_POST['aid'])
    //     || !isset($_POST['starttime']) || empty($_POST['starttime'])
    //     || !isset($_POST['endtime']) || empty($_POST['endtime'])
    //     || !isset($_POST['min_worth']) || empty($_POST['min_worth'])
    //     || !isset($_POST['max_worth']) || empty($_POST['max_worth'])
    //     || !isset($_POST['status']) || empty($_POST['status'])
    //   ){
    //     $json = $this->JSON(2619, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $aid        = $_POST['aid'];
    //   $starttime  = $_POST['starttime'];
    //   $endtime    = $_POST['endtime'];
    //   $min_worth  = $_POST['min_worth'];
    //   $max_worth  = $_POST['max_worth'];
    //   $status     = $_POST['status'];
    //   // 判断日期格式
    //   if (!strtotime($starttime) || !strtotime($endtime) ) {
    //     $json = $this->JSON(2620, 'Invaild datetime.', null);
    //     return response($json);
    //   }
    //   // 格式化日期
    //   $starttime = date('Y-m-d H:i:s', strtotime($starttime));
    //   $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
    //   if ($min_worth < 0) {
    //     $json = $this->JSON(2621, "Invaild minimun({$min_worth}).", null);
    //     return response($json);
    //   }
    //   if ($max_worth < $min_worth) {
    //     $json = $this->JSON(2622, "Invaild maximun({$max_worth}).", null);
    //     return response($json);
    //   }
    //   $data = [
    //     'starttime' => $starttime,
    //     'endtime'   => $endtime,
    //     'min_worth' => $min_worth,
    //     'max_worth' => $max_worth,
    //     'status'    => $status
    //   ];
    //   $db = DB::table('activity')->where('aid', $aid)->update($data);
    //   if ($db) {
    //     $json = $this->JSON(0, null, ['msg' => 'Success.']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2623, 'Failed to insert into database.', null);
    //     return response($json);
    //   }
    // }
    //
    // // 删除活动
    // public function activity_delete() {
    //   if (!isset($_POST['aid']) || empty($_POST['aid'])){
    //     $json = $this->JSON(2624, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $aid                = $_POST['aid'];
    //   // 查询该AID是否存在
    //   $notice = DB::table('activity')->where('aid', $aid)->first();
    //   if (!$notice) {
    //     $json = $this->JSON(2625, "Failed to find this AID.", null);
    //     return response($json);
    //   }
    //   $notice = DB::table('activity')->where('aid', $aid)->delete();
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2626, "Failed to delete activity.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 搜索商品
    // function goods_search(int $gid) {
    //   $db = DB::table('shop')->where('gid', $gid)->first();
    //   if ($db) {
    //     $json = $this->JSON(0, null, ['msg' => 'Success.', 'data' => $db]);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2614, 'Failed to find this good.', null);
    //     return response($json);
    //   }
    // }
    //
    // // 增加商品
    // function goods_add() {
    //   $gname        = request()->post('gname');
    //   $cost         = request()->post('cost');
    //   $starttime    = request()->post('starttime');
    //   $endtime      = request()->post('endtime');
    //   $tid          = request()->post('tid');
    //   $sid          = request()->post('sid');
    //   $rebuy        = request()->post('rebuy');
    //   $all_count    = request()->post('all_count');
    //   $description  = request()->post('description');
    //   $image        = request()->post('image');
    //   $captcha      = request()->post('captcha');
    //   // 检查验证码
    //   if (!Captcha::check($captcha)) {
    //     $json = $this->JSON(2611, 'Bad captcha.', null);
    //     return response($json);
    //   }
    //   // process image link
    //   $image        = $image === 'null' ? '' : $image;
    //   // 判断日期格式
    //   if (!strtotime($starttime) || !strtotime($endtime) ) {
    //     $json = $this->JSON(2607, 'Invaild datetime.', null);
    //     return response($json);
    //   }
    //   // 格式化日期
    //   $starttime = date('Y-m-d H:i:s', strtotime($starttime));
    //   $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
    //   if ($cost < 0 || $tid < 0 || $sid < 0 || $rebuy < 0 || $all_count < 0) {
    //     $json = $this->JSON(2608, "Invaild params.", null);
    //     return response($json);
    //   }
    //   $data = [
    //     'gname'         => $gname,
    //     'cost'          => $cost,
    //     'starttime'     => $starttime,
    //     'endtime'       => $endtime,
    //     'tid'           => $tid,
    //     'sid'           => $sid,
    //     'all_count'     => $all_count,
    //     'image'         => $image,
    //     'rebuy'         => $rebuy,
    //     'description'   => $description,
    //     'status'        => 1
    //   ];
    //   $db = DB::table('shop')->insert($data);
    //   if ($db) {
    //     $json = $this->JSON(0, null, ['msg' => 'Success.']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2609, 'Failed to insert into database.', null);
    //     return response($json);
    //   }
    // }
    //
    // // 修改商品
    // function goods_update() {
    //   $gid          = request()->post('gid');
    //   $gname        = request()->post('gname');
    //   $cost         = request()->post('cost');
    //   $starttime    = request()->post('starttime');
    //   $endtime      = request()->post('endtime');
    //   $tid          = request()->post('tid');
    //   $sid          = request()->post('sid');
    //   $rebuy        = request()->post('rebuy');
    //   $all_count    = request()->post('all_count');
    //   $description  = request()->post('description');
    //   $image        = request()->post('image');
    //   $captcha      = request()->post('captcha');
    //   // 检查验证码
    //   if (!Captcha::check($captcha)) {
    //     $json = $this->JSON(2630, 'Bad captcha.', null);
    //     return response($json);
    //   }
    //   // process image link
    //   $image        = $image === 'null' ? '' : $image;
    //   // 判断日期格式
    //   if (!strtotime($starttime) || !strtotime($endtime) ) {
    //     $json = $this->JSON(2631, 'Invaild datetime.', null);
    //     return response($json);
    //   }
    //   // 格式化日期
    //   $starttime = date('Y-m-d H:i:s', strtotime($starttime));
    //   $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
    //   if ($cost < 0 || $tid < 0 || $sid < 0 || $rebuy < 0 || $all_count < 0) {
    //     $json = $this->JSON(2632, "Invaild params.", null);
    //     return response($json);
    //   }
    //   // 查找gid
    //   if( !DB::table('shop')->where('gid', $gid)->first() ) {
    //     $json = $this->JSON(2633, 'Failed to find this good.', null);
    //     return response($json);
    //   }
    //   $data = [
    //     'gname'         => $gname,
    //     'cost'          => $cost,
    //     'starttime'     => $starttime,
    //     'endtime'       => $endtime,
    //     'tid'           => $tid,
    //     'sid'           => $sid,
    //     'all_count'     => $all_count,
    //     'image'         => $image,
    //     'rebuy'         => $rebuy,
    //     'description'   => $description,
    //     'status'        => 1
    //   ];
    //   $db = DB::table('shop')->where('gid', $gid)->update($data);
    //   if ($db) {
    //     $json = $this->JSON(0, null, ['msg' => 'Success.']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2634, 'Failed to update into database.', null);
    //     return response($json);
    //   }
    // }
    //
    // // 删除商品
    // public function goods_delete() {
    //   $gid          = request()->post('gid');
    //   if (!$gid){
    //     $json = $this->JSON(2640, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   // 查询该GID是否存在
    //   $good = DB::table('shop')->where('gid', $gid)->first();
    //   if (!$good) {
    //     $json = $this->JSON(2641, "Failed to find this GID.", null);
    //     return response($json);
    //   }
    //   $good = DB::table('shop')->where('gid', $gid)->delete();
    //   if ($good) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2642, "Failed to delete this good.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 搜索公告
    // public function notices_search(int $nid) {
    //   $notice = DB::table('notices')
    //           ->where('nid', $nid)
    //           ->first();
    //   if (!$notice) {
    //     $json = $this->JSON(2901, "Failed to find this NID.", null);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(0, null, ['msg'=>'Success', 'data'=>$notice]);
    //     return response($json);
    //   }
    // }
    //
    // // 添加公告
    // public function notices_add() {
    //   if (!isset($_POST['place_id']) || empty($_POST['place_id'])
    //     || !isset($_POST['title']) || empty($_POST['title'])
    //     || !isset($_POST['content']) || empty($_POST['content'])
    //     || !isset($_POST['color']) || empty($_POST['color'])
    //     || !isset($_POST['priority']) || empty($_POST['priority'])
    //     || !isset($_POST['starttime']) || empty($_POST['starttime'])
    //     || !isset($_POST['endtime']) || empty($_POST['endtime'])
    //     || !isset($_POST['status']) || (empty($_POST['status']) && $_POST['status'] != 0)
    //   ){
    //     $json = $this->JSON(2902, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $starttime = date('Y-m-d H:i:s', strtotime($_POST['starttime']));
    //   $endtime   = date('Y-m-d H:i:s', strtotime($_POST['endtime']));
    //   $data['place_id']   = $_POST['place_id'];
    //   $data['title']      = $_POST['title'];
    //   $data['content']    = $_POST['content'];
    //   $data['color']      = $_POST['color'];
    //   $data['priority']   = $_POST['priority'];
    //   $data['starttime']  = $starttime;
    //   $data['endtime']    = $endtime;
    //   $data['status']     = $_POST['status'];
    //   $notice = DB::table('notices')->insert($data);
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2903, "Failed to add notice.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 修改公告
    // public function notices_update() {
    //   if (!isset($_POST['nid']) || empty($_POST['nid'])
    //     || !isset($_POST['place_id']) || empty($_POST['place_id'])
    //     || !isset($_POST['title']) || empty($_POST['title'])
    //     || !isset($_POST['content']) || empty($_POST['content'])
    //     || !isset($_POST['color']) || empty($_POST['color'])
    //     || !isset($_POST['priority']) || empty($_POST['priority'])
    //     || !isset($_POST['starttime']) || empty($_POST['starttime'])
    //     || !isset($_POST['endtime']) || empty($_POST['endtime'])
    //     || !isset($_POST['status']) || (empty($_POST['status']) && $_POST['status'] != 0)
    //   ){
    //     $json = $this->JSON(2904, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $nid                = $_POST['nid'];
    //   // 查询该NID是否存在
    //   $notice = DB::table('notices')->where('nid', $nid)->first();
    //   if (!$notice) {
    //     $json = $this->JSON(2905, "Failed to find this NID.", null);
    //     return response($json);
    //   }
    //   $starttime = date('Y-m-d H:i:s', strtotime($_POST['starttime']));
    //   $endtime   = date('Y-m-d H:i:s', strtotime($_POST['endtime']));
    //   $data['place_id']   = $_POST['place_id'];
    //   $data['title']      = $_POST['title'];
    //   $data['content']    = $_POST['content'];
    //   $data['color']      = $_POST['color'];
    //   $data['priority']   = $_POST['priority'];
    //   $data['starttime']  = $starttime;
    //   $data['endtime']    = $endtime;
    //   $data['status']     = $_POST['status'];
    //   $notice = DB::table('notices')->where('nid', $nid)->update($data);
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2906, "Failed to update notice.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 删除公告
    // public function notices_delete() {
    //   if (!isset($_POST['nid']) || empty($_POST['nid'])){
    //     $json = $this->JSON(2907, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $nid                = $_POST['nid'];
    //   // 查询该NID是否存在
    //   $notice = DB::table('notices')->where('nid', $nid)->first();
    //   if (!$notice) {
    //     $json = $this->JSON(2908, "Failed to find this NID.", null);
    //     return response($json);
    //   }
    //   $notice = DB::table('notices')->where('nid', $nid)->delete();
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(2909, "Failed to delete notice.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 搜索用户
    // public function users_search(int $uid) {
    //   $user = DB::table('user_accounts')
    //           ->where('uid', $uid)
    //           ->first();
    //   if (!$user) {
    //     $json = $this->JSON(3001, "Failed to find this UID.", null);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(0, null, ['msg'=>'Success', 'data'=>$user]);
    //     return response($json);
    //   }
    // }
    //
    // // 搜索用户 根据用户名
    // public function users_search_username(string $username) {
    //   $user = DB::table('user_accounts')
    //           ->where('username', $username)
    //           ->first();
    //   if (!$user) {
    //     $json = $this->JSON(3001, "Failed to find this Username.", null);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(0, null, ['msg'=>'Success', 'data'=>$user]);
    //     return response($json);
    //   }
    // }
    //
    // // 查询用户积分情况
    // public function users_points_get(int $uid) {
    //   $allWorth = DB::table('lists_v2')
    //           ->where('uid', $uid)
    //           ->sum('worth');
    //   $used     = DB::table('purchase_records')
    //           ->where('uid', $uid)
    //           ->sum('cost');
    //
    //   if ($allWorth === false || $used === false) {
    //     $json = $this->JSON(3601, "Failed to get infomation.", null);
    //     return response($json);
    //   }else{
    //     $data = [
    //       'allWorth'  => $allWorth,
    //       'used'      => $used
    //     ];
    //     $json = $this->JSON(0, null, ['msg'=>'Success', 'data'=>$data]);
    //     return response($json);
    //   }
    // }
    //
    // // 修改用户信息
    // public function users_update() {
    //   if (!isset($_POST['uid']) || empty($_POST['uid'])
    //     || !isset($_POST['username']) || empty($_POST['username'])
    //     || !isset($_POST['password'])
    //     || !isset($_POST['status']) || (empty($_POST['status']) && $_POST['status'] != 0)
    //   ){
    //     $json = $this->JSON(2904, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $admin_level = request()->get('_admin');
    //   $admin_level = $admin_level->level;
    //   $uid                = $_POST['uid'];
    //   // 查询该UID是否存在
    //   $notice = DB::table('user_accounts')->where('uid', $uid)->first();
    //   if (!$notice) {
    //     $json = $this->JSON(3002, "Failed to find this UID.", null);
    //     return response($json);
    //   }
    //   // 查询是否有权限修改此用户
    //   $level = DB::table('admin_level')->where('uid', $uid)->first();
    //   if ($level && $level->level >= $admin_level) {
    //     $json = $this->JSON(3004, "Have not rights to update this user.", null);
    //     return response($json);
    //   }
    //   $data['username']   = $_POST['username'];
    //   if (!empty($_POST['password'])){
    //     $data['password']   = $this->generate_password($_POST['password']);
    //   }
    //   $data['status']     = $_POST['status'];
    //   $notice = DB::table('user_accounts')->where('uid', $uid)->update($data);
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3003, "Failed to update user.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 用户提权
    // public function admins_rights_add() {
    //   if (!isset($_POST['uid']) || empty($_POST['uid'])
    //     || !isset($_POST['rid']) || empty($_POST['rid'])
    //   ){
    //     $json = $this->JSON(3101, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $uid = $_POST['uid'];
    //   $rid = $_POST['rid'];
    //   // 查找权限
    //   $admin_level = request()->get('_admin');
    //   $admin_level = $admin_level->level;
    //   $right  = DB::table('admin_rights_list')->where('rid', $rid)->first();
    //   if (!$right) {
    //     $json = $this->JSON(3102, "Failed to find right infomation.", null);
    //     return response($json);
    //   }
    //   // 检查是否有权限提权
    //   $level_need = $right->level_need;
    //   if ($admin_level < $level_need) {
    //     $json = $this->JSON(3103, "Higher admin level needed.", null);
    //     return response($json);
    //   }
    //   // 检查权限是否存在
    //   $have_right = DB::table('admin_register')->where('uid', $_POST['uid'])->where('rid', $_POST['rid'])->first();
    //   if ($have_right) {
    //     $json = $this->JSON(3105, "The admin has already had this right.", null);
    //     return response($json);
    //   }
    //   $data['uid']      = $_POST['uid'];
    //   $data['rid']      = $_POST['rid'];
    //   $data['status']   = 1;
    //   $notice = DB::table('admin_register')->insert($data);
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3104, "Failed to add rights.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 用户降权
    // public function admins_rights_del() {
    //   $uid = request()->post('uid');
    //   $rid = request()->post('rid');
    //   if (is_null($uid) || is_null($rid)){
    //     $json = $this->JSON(3701, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   // 判断权限
    //   $admin_level = request()->get('_admin');
    //   $admin_level = $admin_level->level;
    //   // 检查被降权用户权限等级
    //   $level = DB::table('admin_level')->where('uid', $uid)->first();
    //   if ($level && $level->level >= $admin_level) {
    //     $json = $this->JSON(3702, "Have no rights to delete the right of this user.", null);
    //     return response($json);
    //   }
    //   $notice = DB::table('admin_register')->where('uid', $uid)->where('rid', $rid)->delete();
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3103, "Failed to del rights.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 搜索勋章
    // public function badges_search(int $bid) {
    //   $badge = DB::table('badges')
    //           ->where('bid', $bid)
    //           ->first();
    //   if (!$badge) {
    //     $json = $this->JSON(3201, "Failed to find this BID.", null);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(0, null, ['msg'=>'Success', 'data'=>$badge]);
    //     return response($json);
    //   }
    // }
    //
    // // 添加勋章
    // public function badges_add() {
    //   $bname    = request()->post('bname');
    //   $image    = (!request()->post('image')) ? '' : request()->post('image');
    //   $fgcolor  = request()->post('fgcolor');
    //   $bgcolor  = request()->post('bgcolor');
    //   $gid      = request()->post('gid');
    //   $eid      = request()->post('eid');
    //   $status   = request()->post('status');
    //   if (!$bname || ($image !== '' && !$image) || !$fgcolor || !$bgcolor || !$gid || ($eid !== 0 && !$eid) || is_null($status)) {
    //     $json = $this->JSON(3202, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $data['bname']      = $bname;
    //   $data['image']      = $image;
    //   $data['fgcolor']    = $fgcolor;
    //   $data['bgcolor']    = $bgcolor;
    //   $data['gid']        = $gid;
    //   $data['eid']        = $eid;
    //   $data['status']     = $status;
    //   $notice = DB::table('badges')->insert($data);
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3203, "Failed to add badge.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 修改勋章
    // public function badges_update() {
    //   $bid      = request()->post('bid');
    //   $bname    = request()->post('bname');
    //   $image    = (!request()->post('image')) ? '' : request()->post('image');
    //   $fgcolor  = request()->post('fgcolor');
    //   $bgcolor  = request()->post('bgcolor');
    //   $gid      = request()->post('gid');
    //   $eid      = request()->post('eid');
    //   $status   = request()->post('status');
    //   if (!$bid || !$bname || ($image !== '' && !$image) || !$fgcolor || !$bgcolor || !$gid || ($eid !== 0 && !$eid) || is_null($status)) {
    //     $json = $this->JSON(3204, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $data['bname']      = $bname;
    //   $data['image']      = $image;
    //   $data['fgcolor']    = $fgcolor;
    //   $data['bgcolor']    = $bgcolor;
    //   $data['gid']        = $gid;
    //   $data['eid']        = $eid;
    //   $data['status']     = $status;
    //   $notice = DB::table('badges')->where('bid', $bid)->update($data);
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3205, "Failed to update badge.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 删除勋章
    // public function badges_delete() {
    //   $bid      = request()->post('bid');
    //   if (!$bid){
    //     $json = $this->JSON(3206, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   // 查询该BID是否存在
    //   $badge = DB::table('badges')->where('bid', $bid)->first();
    //   if (!$badge) {
    //     $json = $this->JSON(3207, "Failed to find this BID.", null);
    //     return response($json);
    //   }
    //   $notice = DB::table('badges')->where('bid', $bid)->delete();
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3208, "Failed to delete badge.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 搜索效果
    // public function effects_search(int $bid) {
    //   $badge = DB::table('effects')
    //           ->where('eid', $bid)
    //           ->first();
    //   if (!$badge) {
    //     $json = $this->JSON(3301, "Failed to find this EID.", null);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(0, null, ['msg'=>'Success', 'data'=>$badge]);
    //     return response($json);
    //   }
    // }
    //
    // // 添加效果
    // public function effects_add() {
    //   $times        = request()->post('times');
    //   $description  = request()->post('description');
    //   $status       = request()->post('status');
    //   if (!$times || !$description || is_null($status)) {
    //     $json = $this->JSON(3302, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $data['times']        = $times;
    //   $data['description']  = $description;
    //   $data['status']       = $status;
    //   $notice = DB::table('effects')->insert($data);
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3303, "Failed to add badge.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 修改效果
    // public function effects_update() {
    //   $eid          = request()->post('eid');
    //   $times        = request()->post('times');
    //   $description  = request()->post('description');
    //   $status       = request()->post('status');
    //   if (!$eid || !$times || !$description || is_null($status)) {
    //     $json = $this->JSON(3304, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   $data['eid']        = $eid;
    //   $data['times']        = $times;
    //   $data['description']  = $description;
    //   $data['status']       = $status;
    //   $notice = DB::table('effects')->where('eid', $eid)->update($data);
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3305, "Failed to update badge.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 删除效果
    // public function effects_delete() {
    //   $eid      = request()->post('eid');
    //   if (!$eid){
    //     $json = $this->JSON(3306, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   // 查询该BID是否存在
    //   $badge = DB::table('effects')->where('eid', $eid)->first();
    //   if (!$badge) {
    //     $json = $this->JSON(3307, "Failed to find this EID.", null);
    //     return response($json);
    //   }
    //   $notice = DB::table('effects')->where('eid', $eid)->delete();
    //   if ($notice) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3308, "Failed to delete badge.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 修改用户管理等级
    // public function admin_level_update() {
    //   $uid    = request()->post('uid');
    //   $t_level  = request()->post('level');
    //   if (is_null($uid) || is_null($t_level)) {
    //     $json = $this->JSON(3801, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   if ($t_level < 0) {
    //     $json = $this->JSON(3806, "Target admin level is invaild.", null);
    //     return response($json);
    //   }
    //   // 修改者等级
    //   $admin_level = request()->get('_admin');
    //   $admin_level = $admin_level->level;
    //   // 查询该UID是否存在
    //   $user = DB::table('user_accounts')->where('uid', $uid)->first();
    //   if (!$user) {
    //     $json = $this->JSON(3802, "Failed to find this UID.", null);
    //     return response($json);
    //   }
    //   // 查询是否有权限修改此用户
    //   $level = DB::table('admin_level')->where('uid', $uid)->first();
    //   if ($level && $level->level >= $admin_level) {
    //     $json = $this->JSON(3803, "Have not rights to update this user.", null);
    //     return response($json);
    //   }
    //   // 修改等级判定
    //   if ($t_level >= $admin_level) {
    //     $json = $this->JSON(3804, "Have not rights to update this user to this high admin level.", null);
    //     return response($json);
    //   }
    //   // 判断更新或插入
    //   $data['level'] = $t_level;
    //   $data['update_time'] = date('Y-m-d H:i:s');
    //   $data['status'] = 1;
    //   if ($level) {
    //     $db = DB::table('admin_level')->where('uid', $uid)->update($data);
    //   }else{
    //     $data['uid'] = $uid;
    //     $db = DB::table('admin_level')->insert($data);
    //   }
    //   if ($db) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3805, "Failed to update user.", null);
    //     return response($json);
    //   }
    // }
    //
    // // 清零用户管理等级
    // public function admin_level_remove() {
    //   $uid    = request()->post('uid');
    //   if (is_null($uid)) {
    //     $json = $this->JSON(3811, "Lost some infomation.", null);
    //     return response($json);
    //   }
    //   // 修改者等级
    //   $admin_level = request()->get('_admin');
    //   $admin_level = $admin_level->level;
    //   // 查询该UID是否存在
    //   $user = DB::table('user_accounts')->where('uid', $uid)->first();
    //   if (!$user) {
    //     $json = $this->JSON(3812, "Failed to find this UID.", null);
    //     return response($json);
    //   }
    //   // 查询是否有权限修改此用户
    //   $level = DB::table('admin_level')->where('uid', $uid)->first();
    //   if ($level && $level->level >= $admin_level) {
    //     $json = $this->JSON(3813, "Have not rights to update this user.", null);
    //     return response($json);
    //   }
    //   $db = DB::table('admin_level')->where('uid', $uid)->delete();
    //   if ($db) {
    //     $json = $this->JSON(0, null, ['msg'=>'Success']);
    //     return response($json);
    //   }else{
    //     $json = $this->JSON(3814, "Failed to remove admin level of this user.", null);
    //     return response($json);
    //   }
    // }
}
