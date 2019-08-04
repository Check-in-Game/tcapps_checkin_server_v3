<?php

namespace App\Http\Controllers\Common;

use DB;
use App\Http\Controllers\Controller;

class BackpackManager extends Controller {

  /**
   * 用户寻找方式
   * 1：UID
   * 2：Username
   * 3：Email
   */
  public $method = 1;

  /**
   * 唯一用户标识
   * @var int $uid
   * @var string $username
   * @var string $email
   */
  public $uid = null;
  public $username = null;
  public $email = null;

  /**
   * 物品范围
   * @var array
   */
  public $range = [];

  /**
   * 物品范围
   * @var bool
   */
  public $fill = false;

  // 指定获取用户 via ID
  static public function uid(int $uid): object {
    $self = new self;
    $self->method = 1;  // uid寻找
    $self->uid = $uid;
    return $self;
  }

  // 指定获取用户 via username
  static public function username(string $username): object {
    $self = new self;
    $self->method = 2;  // username寻找
    $self->username = $username;
    $self->_getUid();
    return $self;
  }

  // 指定获取用户 via email
  static public function email(string $email): object {
    $self = new self;
    $self->method = 3;  // email寻找
    $self->email = $email;
    $self->_getUid();
    return $self;
  }

  /**
   * 指定物品范围
   * @param int|array $range
   * @param int|array $range
   * @return object
   */
  public function items($range, bool $fill = false): object {
    // 自动转换数组
    if (!is_array($range)) {
      $range = [$range];
    }
    $this->range = $range;
    // 自动填充数据
    $this->fill = $fill;
    return $this;
  }

  /**
   * 获取用户背包数据
   * @param  bool $detailed
   * @return array
   */
  public function backpack(bool $detailed = false) {
    if (!$this->uid) {
      return null;
    }
    $backpack = DB::table('v3_user_backpack');
    if ($detailed === true) {
      $backpack = $backpack->join('v3_items', 'v3_items.iid', '=', 'v3_user_backpack.iid');
    }
    if ($this->range === []) {
      $backpack = $backpack->where('v3_user_backpack.uid', $this->uid);
    }else{
      $backpack = $backpack->where('v3_user_backpack.uid', $this->uid)
                          ->whereIn('v3_user_backpack.iid', $this->range);
    }
    $backpack = $backpack->where('v3_user_backpack.status', 1)
                        ->sharedLock()
                        ->get();
    $items = [];
    // 整理数据
    foreach ($backpack as $value) {
      $items[$value->iid] = [];
      $items[$value->iid]['amount'] = $value->amount;
      $items[$value->iid]['locked_amount'] = $value->locked_amount;
      $items[$value->iid]['frozen'] = $value->frozen;
      $items[$value->iid]['valid'] = $value->amount + $value->locked_amount;
      $items[$value->iid]['all'] = $value->amount + $value->locked_amount + $value->frozen;
      if ($detailed === true) {
        $items[$value->iid]['iname'] = $value->iname;
        $items[$value->iid]['tid'] = $value->tid;
        $items[$value->iid]['image'] = $value->image;
        $items[$value->iid]['description'] = $value->description;
        $items[$value->iid]['recycle_value'] = $value->recycle_value;
      }
    }
    // 填充数据
    if ($this->fill === true) {
      // 查询物品基本信息
      $_items_info = DB::table('v3_items')->whereIn('iid', $this->range)->get();
      // 整理物品基本信息
      $items_info = [];
      foreach ($_items_info as $key => $value) {
        $items_info[$value->iid] = $value;
      }
      foreach ($this->range as $iid) {
        if (!isset($items[$iid])) {
          $items[$iid]['amount'] = 0;
          $items[$iid]['locked_amount'] = 0;
          $items[$iid]['frozen'] = 0;
          $items[$iid]['valid'] = 0;
          $items[$iid]['all'] = 0;
          if ($detailed === true) {
            $items[$iid]['iname'] = $items_info[$iid]->iname ?? null;
            $items[$iid]['tid'] = $items_info[$iid]->tid ?? null;
            $items[$iid]['image'] = $items_info[$iid]->image ?? null;
            $items[$iid]['description'] = $items_info[$iid]->description ?? null;
            $items[$iid]['recycle_value'] = $items_info[$iid]->recycle_value ?? null;
          }
        }
      }
    }
    return $items;
  }

  /**
   * 获取用户UID
   * @param  void
   * @return void
   */
  private function _getUid() {
    // 用户名查找UID
    if ($this->method === 2) {
      $uid = DB::table('v3_user_accounts')
                ->where('username', $this->username)
                ->value('uid');
    }
    // email查找UID
    if ($this->method === 3) {
      $uid = DB::table('v3_user_accounts')
              ->where('email', $this->email)
              ->value('uid');
    }
    if ($uid) {
      $this->uid = $uid;
    }
  }

}
