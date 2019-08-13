<?php

namespace App\Http\Controllers\Api\Foundation;

use DB;
use Captcha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Common\BackpackManager as BM;

class business extends Controller {

  /**
   * 新手礼包（7日）
   */
  public function fresher_gift() {
    $uid          = request()->cookie('uid');
    // 查询该用户账户建立日期
    $user = DB::table('v3_user_accounts')
              ->where('uid', $uid)
              ->where('register_at', '>=', date('Y-m-d 00:00:00', strtotime('-6 days')))
              ->where('status', 1)
              ->first();
    // 超过7日的玩家不可领取
    if (!$user) {
      $json = $this->JSON(6401, 'Gifts was out-of-date.', null);
      return response($json);
    }
    // 查询该用户今日是否已经领取
    $exists = DB::table('v3_foundation_business_gifts_redeem')
                ->where('uid', $uid)
                ->where('gift_name', 'fresher_gift')
                ->where('reedem_at', '>=', date('Y-m-d 00:00:00'))
                ->where('reedem_at', '<=', date('Y-m-d 23:59:59'))
                ->sharedLock()
                ->exists();
    if ($exists) {
      $json = $this->JSON(6402, 'Already redeemed.', null);
      return response($json);
    }
    // 判断天数
    $start_time = strtotime($user->register_at);
    $today_time = strtotime(date('Y-m-d H:i:s'));
    $day = (int) ceil(($today_time - $start_time) / 60 / 60 / 24);
    // 发放礼包
    switch ($day) {
      case 1:
        BM::uid($user->uid)->add(16, 1, BM::LOCKED);  // 积分体验券
        BM::uid($user->uid)->add(1, 50, BM::LOCKED);  // 粉色可莫尔
        break;
      case 2:
        BM::uid($user->uid)->add(17, 1, BM::LOCKED);  // 小积分券
        BM::uid($user->uid)->add(2, 50, BM::LOCKED);  // 蓝色可莫尔
        break;
      case 3:
        BM::uid($user->uid)->add(18, 1, BM::LOCKED);  // 积分券
        BM::uid($user->uid)->add(3, 50, BM::LOCKED);  // 绿色可莫尔
        break;
      case 4:
        BM::uid($user->uid)->add(14, 10, BM::LOCKED);  // 挂售许可
        BM::uid($user->uid)->add(4, 50, BM::LOCKED);   // 黄色可莫尔
        break;
      case 5:
        BM::uid($user->uid)->add(13, 5, BM::LOCKED);  // WK兑换券
        break;
      case 6:
        BM::uid($user->uid)->add(15, 2, BM::LOCKED);  // WK升级卡
        break;
      case 7:
        BM::uid($user->uid)->add(15, 5, BM::LOCKED);  // WK升级卡
        break;
      default:
        $json = $this->JSON(6401, 'Gifts was out-of-date.', null);
        return response($json);
        break;
    }
    // 写入领取记录
    $data = [
      'uid'       => $uid,
      'gift_name' => 'fresher_gift',
      'reedem_at' => date('Y-m-d H:i:s'),
      'status'    => 1,
    ];
    DB::table('v3_foundation_business_gifts_redeem')->insert($data);
    $json = $this->JSON(0, null, ['msg'  => 'Success!']);
    return response($json);
  }

  // 捐赠积分
  public function donate_point() {
    $uid          = request()->cookie('uid');
    $point        = request()->post('point');
    if (is_null($point)) {
      $json = $this->JSON(404, 'Not found.', null);
      return response($json, 404);
    }
    // 检查数据是否正确
    $point = (int) $point;
    if ($point <= 0) {
      $json = $this->JSON(6501, 'Bad request.', null);
      return response($json);
    }
    // 查询今日是否已经捐赠
    $exists = DB::table('v3_foundation_business_donate_record_point')
                ->where('uid', $uid)
                ->where('donate_at', '>=', date('Y-m-d 00:00:00'))
                ->where('donate_at', '<=', date('Y-m-d 23:59:59'))
                ->where('status', 1)
                ->exists();
    if ($exists) {
      $json = $this->JSON(6502, 'Already donated today.', null);
      return response($json);
    }
    // 查询并扣除积分
    $db = DB::table('v3_user_point')
            ->where('uid', $uid)
            ->where('point', '>=', $point)
            ->lockForUpdate()
            ->decrement('point', $point);
    // 积分不足
    if (!$db) {
      $json = $this->JSON(6503, 'Insufficient point.', null);
      return response($json);
    }
    // 写入捐赠记录
    $data = [
      'uid'       => $uid,
      'point'     => $point,
      'donate_at' => date('Y-m-d H:i:s'),
      'status'    => 1,
    ];
    DB::table('v3_foundation_business_donate_record_point')->insert($data);
    // 增加贡献
    $db = DB::table('v3_foundation_credit')
            ->where('uid', $uid)
            ->sharedLock()
            ->exists();
    if (!$db) {
      // 创建记录
      $data = [
        'uid'     => $uid,
        'credit'  => $point,
      ];
      DB::table('v3_foundation_credit')->insert($data);
    }else{
      // 更新记录
      DB::table('v3_foundation_credit')
        ->where('uid', $uid)
        ->lockForUpdate()
        ->increment('credit', $point);
    }
    // 增加基金会积分
    DB::table('v3_foundation')
      ->where('fkey', 'point')
      ->lockForUpdate()
      ->increment('fvalue', $point);
    $json = $this->JSON(0, null, ['msg'  => 'Success!']);
    return response($json);
  }

}
