<?php

namespace App\Http\Controllers\Api\Foundation;

use DB;
use Captcha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\BackpackManager as BM;

class discuss extends Controller {

  /**
   * 新建讨论
   */
  public function discuss_new() {
    $uid          = request()->cookie('uid');
    $topic        = request()->post('topic');
    $type         = request()->post('type');
    $content      = request()->post('content');
    if (is_null($topic) || is_null($type) || is_null($content)) {
      $json = $this->JSON(404, 'Not found.', null);
      return response($json, 404);
    }
    // 查询该用户账户建立日期
    $user = DB::table('v3_user_accounts')
              ->where('uid', $uid)
              ->where('register_at', '<=', date('Y-m-d H:i:s', strtotime('-1 weeks')))
              ->where('status', 1)
              ->first();
    // 新用户一周内无法新建话题
    if (!$user) {
      $json = $this->JSON(5901, 'Incorrect user status.', null);
      return response($json);
    }
    // 查询该用户今日创建话题数量
    $topics = DB::table('v3_foundation_discuss')
                ->where('uid', $uid)
                ->where('create_at', '>=', date('Y-m-d 00:00:00'))
                ->where('create_at', '<=', date('Y-m-d 23:59:59'))
                ->count();
    // 任何用户一天不能新建超过5个话题
    if ($topics >= 5) {
      $json = $this->JSON(5902, 'Too many posts.', null);
      return response($json);
    }
    // 检查类型
    $type = (int) $type;
    if (!is_numeric($type) || !is_int($type) || $type < 1 || $type > 3) {
      $json = $this->JSON(5903, 'Bad request.', null);
      return response($json);
    }
    // 创建话题
    $data = [
      'uid'   => $uid,
      'tid'   => $type,
      'topic' => $topic,
      'level' => 1,  // 默认等级1
      'create_at' => date('Y-m-d H:i:s'),
      'update_at' => date('Y-m-d H:i:s'),
      'status'    => 1,
    ];
    $did = DB::table('v3_foundation_discuss')->insertGetId($data);
    // 格式错误
    if (!$did) {
      $json = $this->JSON(5904, 'Bad request.', null);
      return response($json);
    }
    $data = [
      'did'       => $did,
      'uid'       => $uid,
      'create_at' => date('Y-m-d H:i:s'),
      'update_at' => date('Y-m-d H:i:s'),
      'content'   => $content,
      'status'    => 1,
    ];
    DB::table('v3_foundation_discuss_posts')->insert($data);
    $json = $this->JSON(0, null, ['msg'  => 'Success!']);
    return response($json);
  }

  /**
   * 新评论
   */
  public function comment_new() {
    $uid          = request()->cookie('uid');
    $did          = request()->post('did');
    $content      = request()->post('content');
    if (is_null($did) || is_null($content)) {
      $json = $this->JSON(404, 'Not found.', null);
      return response($json, 404);
    }
    // 查询该用户账户建立日期
    $user = DB::table('v3_user_accounts')
              ->where('uid', $uid)
              ->where('register_at', '<=', date('Y-m-d H:i:s', strtotime('-1 days')))
              ->where('status', 1)
              ->first();
    // 新用户一天内无法回复话题
    if (!$user) {
      $json = $this->JSON(6001, 'Incorrect user status.', null);
      return response($json);
    }
    // 查询该用户上次回复时间
    $reply = DB::table('v3_foundation_discuss_posts')
                ->where('uid', $uid)
                ->where('create_at', '>=', date('Y-m-d H:i:s', strtotime('-30 seconds')))
                ->exists();
    // 30秒内只能回复1次
    if ($reply) {
      $json = $this->JSON(6002, 'Too many reuqests.', null);
      return response($json);
    }
    // 检查类型
    $did = (int) $did;
    if (!is_numeric($did) || !is_int($did)) {
      $json = $this->JSON(6003, 'Bad request.', null);
      return response($json);
    }
    // 检查重复评论
    $repeat = DB::table('v3_foundation_discuss_posts')
                ->where('uid', $uid)
                ->where('content', $content)
                ->exists();
    // 禁止2次相同评论
    if ($repeat) {
      $json = $this->JSON(6004, 'No repeat.', null);
      return response($json);
    }
    // 查询主题是否存在或关闭
    $topic = DB::table('v3_foundation_discuss')
                ->where('did', $did)
                ->where('status', '<>', 3)
                ->exists();
    // 无法回复的话题
    if (!$topic) {
      $json = $this->JSON(6005, 'Closed or not exist topic.', null);
      return response($json);
    }
    $data = [
      'did'       => $did,
      'uid'       => $uid,
      'create_at' => date('Y-m-d H:i:s'),
      'update_at' => date('Y-m-d H:i:s'),
      'content'   => $content,
      'status'    => 1,
    ];
    DB::table('v3_foundation_discuss_posts')->insert($data);
    $json = $this->JSON(0, null, ['msg'  => 'Success!']);
    return response($json);
  }

  /**
   * 关闭话题
   */
  public function discuss_close() {
    $uid          = request()->cookie('uid');
    $did          = request()->post('did');
    if (is_null($did)) {
      $json = $this->JSON(404, 'Not found.', null);
      return response($json, 404);
    }
    // 检查类型
    $did = (int) $did;
    if (!is_numeric($did) || !is_int($did)) {
      $json = $this->JSON(6101, 'Bad request.', null);
      return response($json);
    }
    // 查询主题是否存在或关闭
    $topic = DB::table('v3_foundation_discuss')
                ->where('did', $did)
                ->where('uid', $uid)
                ->where('status', '<>', 3)
                ->exists();
    // 不是自己的话题或已经关闭或did不存在
    if (!$topic) {
      $json = $this->JSON(6102, 'Illegal Operation.', null);
      return response($json);
    }
    $data = [
      'status'    => 3,
    ];
    DB::table('v3_foundation_discuss')->where('did', $did)->update($data);
    $json = $this->JSON(0, null, ['msg'  => 'Success!']);
    return response($json);
  }
}
