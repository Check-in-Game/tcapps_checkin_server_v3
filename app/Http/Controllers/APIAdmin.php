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

    // 搜索活动
    function activity_search(int $aid) {
        $db = DB::table('activity')->where('aid', $aid)->first();
        if ($db) {
          $json = $this->JSON(0, null, ['msg' => 'Success.', 'data' => $db]);
          return response($json);
        }else{
          $json = $this->JSON(2617, 'Failed to find this AID.', null);
          return response($json);
        }
    }

    // 增加活动
    function activity_add() {
      if (!isset($_POST['starttime']) || empty($_POST['starttime'])
        || !isset($_POST['endtime']) || empty($_POST['endtime'])
        || !isset($_POST['min_worth']) || empty($_POST['min_worth'])
        || !isset($_POST['max_worth']) || empty($_POST['max_worth'])
        || !isset($_POST['status']) || empty($_POST['status'])
      ){
        $json = $this->JSON(2618, "Lost some infomation.", null);
        return response($json);
      }
      $starttime  = $_POST['starttime'];
      $endtime    = $_POST['endtime'];
      $min_worth  = $_POST['min_worth'];
      $max_worth  = $_POST['max_worth'];
      $status     = $_POST['status'];
      // 判断日期格式
      if (!strtotime($starttime) || !strtotime($endtime) ) {
        $json = $this->JSON(2603, 'Invaild datetime.', null);
        return response($json);
      }
      // 格式化日期
      $starttime = date('Y-m-d H:i:s', strtotime($starttime));
      $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
      if ($min_worth < 0) {
        $json = $this->JSON(2604, "Invaild minimun({$min_worth}).", null);
        return response($json);
      }
      if ($max_worth < $min_worth) {
        $json = $this->JSON(2605, "Invaild maximun({$max_worth}).", null);
        return response($json);
      }
      $data = [
        'starttime' => $starttime,
        'endtime'   => $endtime,
        'min_worth' => $min_worth,
        'max_worth' => $max_worth,
        'status'    => $status
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

    // 修改活动
    function activity_update() {
      if (!isset($_POST['aid']) || empty($_POST['aid'])
        || !isset($_POST['starttime']) || empty($_POST['starttime'])
        || !isset($_POST['endtime']) || empty($_POST['endtime'])
        || !isset($_POST['min_worth']) || empty($_POST['min_worth'])
        || !isset($_POST['max_worth']) || empty($_POST['max_worth'])
        || !isset($_POST['status']) || empty($_POST['status'])
      ){
        $json = $this->JSON(2619, "Lost some infomation.", null);
        return response($json);
      }
      $aid        = $_POST['aid'];
      $starttime  = $_POST['starttime'];
      $endtime    = $_POST['endtime'];
      $min_worth  = $_POST['min_worth'];
      $max_worth  = $_POST['max_worth'];
      $status     = $_POST['status'];
      // 判断日期格式
      if (!strtotime($starttime) || !strtotime($endtime) ) {
        $json = $this->JSON(2620, 'Invaild datetime.', null);
        return response($json);
      }
      // 格式化日期
      $starttime = date('Y-m-d H:i:s', strtotime($starttime));
      $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
      if ($min_worth < 0) {
        $json = $this->JSON(2621, "Invaild minimun({$min_worth}).", null);
        return response($json);
      }
      if ($max_worth < $min_worth) {
        $json = $this->JSON(2622, "Invaild maximun({$max_worth}).", null);
        return response($json);
      }
      $data = [
        'starttime' => $starttime,
        'endtime'   => $endtime,
        'min_worth' => $min_worth,
        'max_worth' => $max_worth,
        'status'    => $status
      ];
      $db = DB::table('activity')->where('aid', $aid)->update($data);
      if ($db) {
        $json = $this->JSON(0, null, ['msg' => 'Success.']);
        return response($json);
      }else{
        $json = $this->JSON(2623, 'Failed to insert into database.', null);
        return response($json);
      }
    }

    // 删除活动
    public function activity_delete() {
      if (!isset($_POST['aid']) || empty($_POST['aid'])){
        $json = $this->JSON(2624, "Lost some infomation.", null);
        return response($json);
      }
      $aid                = $_POST['aid'];
      // 查询该AID是否存在
      $notice = DB::table('activity')->where('aid', $aid)->first();
      if (!$notice) {
        $json = $this->JSON(2625, "Failed to find this AID.", null);
        return response($json);
      }
      $notice = DB::table('activity')->where('aid', $aid)->delete();
      if ($notice) {
        $json = $this->JSON(0, null, ['msg'=>'Success']);
        return response($json);
      }else{
        $json = $this->JSON(2626, "Failed to delete activity.", null);
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

    // 搜索公告
    public function notices_search(int $nid) {
      $notice = DB::table('notices')
              ->where('nid', $nid)
              ->first();
      if (!$notice) {
        $json = $this->JSON(2901, "Failed to find this NID.", null);
        return response($json);
      }else{
        $json = $this->JSON(0, null, ['msg'=>'Success', 'data'=>$notice]);
        return response($json);
      }
    }

    // 添加公告
    public function notices_add() {
      if (!isset($_POST['place_id']) || empty($_POST['place_id'])
        || !isset($_POST['title']) || empty($_POST['title'])
        || !isset($_POST['content']) || empty($_POST['content'])
        || !isset($_POST['color']) || empty($_POST['color'])
        || !isset($_POST['priority']) || empty($_POST['priority'])
        || !isset($_POST['starttime']) || empty($_POST['starttime'])
        || !isset($_POST['endtime']) || empty($_POST['endtime'])
        || !isset($_POST['status']) || empty($_POST['status'])
      ){
        $json = $this->JSON(2902, "Lost some infomation.", null);
        return response($json);
      }
      $starttime = date('Y-m-d H:i:s', strtotime($starttime));
      $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
      $data['place_id']   = $_POST['place_id'];
      $data['title']      = $_POST['title'];
      $data['content']    = $_POST['content'];
      $data['color']      = $_POST['color'];
      $data['priority']   = $_POST['priority'];
      $data['starttime']  = $starttime;
      $data['endtime']    = $endtime;
      $data['status']     = $_POST['status'];
      $notice = DB::table('notices')->insert($data);
      if ($notice) {
        $json = $this->JSON(0, null, ['msg'=>'Success']);
        return response($json);
      }else{
        $json = $this->JSON(2903, "Failed to add notice.", null);
        return response($json);
      }
    }

    // 修改公告
    public function notices_update() {
      if (!isset($_POST['nid']) || empty($_POST['nid'])
        || !isset($_POST['place_id']) || empty($_POST['place_id'])
        || !isset($_POST['title']) || empty($_POST['title'])
        || !isset($_POST['content']) || empty($_POST['content'])
        || !isset($_POST['color']) || empty($_POST['color'])
        || !isset($_POST['priority']) || empty($_POST['priority'])
        || !isset($_POST['starttime']) || empty($_POST['starttime'])
        || !isset($_POST['endtime']) || empty($_POST['endtime'])
        || !isset($_POST['status']) || empty($_POST['status'])
      ){
        $json = $this->JSON(2904, "Lost some infomation.", null);
        return response($json);
      }
      $nid                = $_POST['nid'];
      // 查询该NID是否存在
      $notice = DB::table('notices')->where('nid', $nid)->first();
      if (!$notice) {
        $json = $this->JSON(2905, "Failed to find this NID.", null);
        return response($json);
      }
      $starttime = date('Y-m-d H:i:s', strtotime($starttime));
      $endtime   = date('Y-m-d H:i:s', strtotime($endtime));
      $data['place_id']   = $_POST['place_id'];
      $data['title']      = $_POST['title'];
      $data['content']    = $_POST['content'];
      $data['color']      = $_POST['color'];
      $data['priority']   = $_POST['priority'];
      $data['starttime']  = $starttime;
      $data['endtime']    = $endtime;
      $data['status']     = $_POST['status'];
      $notice = DB::table('notices')->where('nid', $nid)->update($data);
      if ($notice) {
        $json = $this->JSON(0, null, ['msg'=>'Success']);
        return response($json);
      }else{
        $json = $this->JSON(2906, "Failed to update notice.", null);
        return response($json);
      }
    }

    // 删除公告
    public function notices_delete() {
      if (!isset($_POST['nid']) || empty($_POST['nid'])){
        $json = $this->JSON(2907, "Lost some infomation.", null);
        return response($json);
      }
      $nid                = $_POST['nid'];
      // 查询该NID是否存在
      $notice = DB::table('notices')->where('nid', $nid)->first();
      if (!$notice) {
        $json = $this->JSON(2908, "Failed to find this NID.", null);
        return response($json);
      }
      $notice = DB::table('notices')->where('nid', $nid)->delete();
      if ($notice) {
        $json = $this->JSON(0, null, ['msg'=>'Success']);
        return response($json);
      }else{
        $json = $this->JSON(2909, "Failed to delete notice.", null);
        return response($json);
      }
    }

    // 搜索用户
    public function users_search(int $uid) {
      $notice = DB::table('user_accounts')
              ->where('uid', $uid)
              ->first();
      if (!$notice) {
        $json = $this->JSON(3001, "Failed to find this UID.", null);
        return response($json);
      }else{
        $json = $this->JSON(0, null, ['msg'=>'Success', 'data'=>$notice]);
        return response($json);
      }
    }

    // 修改用户信息
    public function users_update() {
      if (!isset($_POST['uid']) || empty($_POST['uid'])
        || !isset($_POST['username']) || empty($_POST['username'])
        || !isset($_POST['password'])
        || !isset($_POST['status']) || empty($_POST['status'])
      ){
        $json = $this->JSON(2904, "Lost some infomation.", null);
        return response($json);
      }
      $admin_level = request()->get('_admin');
      $admin_level = $admin_level->level;
      $uid                = $_POST['uid'];
      // 查询该NID是否存在
      $notice = DB::table('user_accounts')->where('uid', $uid)->first();
      if (!$notice) {
        $json = $this->JSON(3002, "Failed to find this UID.", null);
        return response($json);
      }
      // 查询是否有权限修改此用户
      $level = DB::table('admin_level')->where('uid', $uid)->first();
      if ($level && $level->level >= $admin_level) {
        $json = $this->JSON(3004, "Have not rights to update this user.", null);
        return response($json);
      }
      $data['username']   = $_POST['username'];
      if (!empty($_POST['password'])){
        $data['password']   = $this->generate_password($_POST['password']);
      }
      $data['status']     = $_POST['status'];
      $notice = DB::table('user_accounts')->where('uid', $uid)->update($data);
      if ($notice) {
        $json = $this->JSON(0, null, ['msg'=>'Success']);
        return response($json);
      }else{
        $json = $this->JSON(3003, "Failed to update user.", null);
        return response($json);
      }
    }

    // 用户提权
    public function admins_rights_add() {
      if (!isset($_POST['uid']) || empty($_POST['uid'])
        || !isset($_POST['rid']) || empty($_POST['rid'])
      ){
        $json = $this->JSON(3101, "Lost some infomation.", null);
        return response($json);
      }
      $uid = $_POST['uid'];
      $rid = $_POST['rid'];
      // 判断权限
      $admin_level = request()->get('_admin');
      $admin_level = $admin_level->level;
      $right  = DB::table('admin_rights_list')->where('rid', $rid)->first();
      if (!$right) {
        $json = $this->JSON(3102, "Failed to find right infomation.", null);
        return response($json);
      }
      $level_need = $right->level_need;
      if ($admin_level < $level_need) {
        $json = $this->JSON(3103, "Higher admin level needed.", null);
        return response($json);
      }
      $data['uid']      = $_POST['uid'];
      $data['rid']      = $_POST['rid'];
      $data['status']   = 1;
      $notice = DB::table('admin_register')->insert($data);
      if ($notice) {
        $json = $this->JSON(0, null, ['msg'=>'Success']);
        return response($json);
      }else{
        $json = $this->JSON(3104, "Failed to add rights.", null);
        return response($json);
      }
    }
}
