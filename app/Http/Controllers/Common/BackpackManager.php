<?php

namespace App\Http\Controllers\Common;

use DB;
use App\Http\Controllers\Controller;

class BackpackManager extends Controller {

  /**
   * 所有物品数量，含冻结
   */
  const ALL = 2;

  /**
   * 所有物品数量，不含冻结
   */
  const VALID = 3;

  /**
   * 锁定物品
   */
  const LOCKED = 1;

  /**
   * 通用物品
   */
  const GENERAL = 0;

  /**
   * 通用物品优先
   */
  const GENERAL_FIRST = 10;

  /**
   * 锁定物品优先
   */
  const LOCKED_FIRST = 11;

  /**
   * 通用物品优先
   */
  const GENERAL_ONLY = 12;

  /**
   * 锁定物品优先
   */
  const LOCKED_ONLY = 13;

  /**
   * 物品数量不足
   */
  const INSUFFICIENT = 20;

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

  /**
   * 物品操作数量记录
   * @var array
   */
  public $items = [
    'add' => [],
    'red' => [],
  ];

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
   * @param  int  $type
   * @return array
   */
  public function backpack(bool $detailed = false, int $type = self::ALL): array {
    if (!$this->uid) {
      return null;
    }
    $backpack = DB::table('v3_user_backpack')
                  ->where('v3_user_backpack.uid', $this->uid);
    if ($detailed === true) {
      $backpack = $backpack->join('v3_items', 'v3_items.iid', '=', 'v3_user_backpack.iid');
    }
    if ($this->range !== []) {
      $backpack = $backpack->whereIn('v3_user_backpack.iid', $this->range);
    }
    if ($type === self::LOCKED) {
      $backpack = $backpack->where('v3_user_backpack.locked_amount', '>', 0);
    }else if($type === self::GENERAL) {
      $backpack = $backpack->where('v3_user_backpack.amount', '>', 0);
    }
    $backpack = $backpack->where('v3_user_backpack.status', 1)
                        ->sharedLock()
                        ->get();
    $items = [];
    // 整理数据
    foreach ($backpack as $value) {
      $items[$value->iid] = [];
      $items[$value->iid]['iid'] = $value->iid;
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
        if (!isset($items[$iid]) && isset($items_info[$iid])) {
          $items[$iid]['iid'] = $iid;
          $items[$iid]['amount'] = 0;
          $items[$iid]['locked_amount'] = 0;
          $items[$iid]['frozen'] = 0;
          $items[$iid]['valid'] = 0;
          $items[$iid]['all'] = 0;
          if ($detailed === true) {
            $items[$iid]['iname'] = $items_info[$iid]->iname ?? '';
            $items[$iid]['tid'] = $items_info[$iid]->tid ?? '';
            $items[$iid]['image'] = $items_info[$iid]->image ?? '';
            $items[$iid]['description'] = $items_info[$iid]->description ?? '';
            $items[$iid]['recycle_value'] = $items_info[$iid]->recycle_value ?? '';
          }
        }
      }
    }
    // 重新按键值排序
    ksort($items);
    return $items;
  }

  /**
   * 判断指定物品是否充足
   * @param  int  $iid
   * @param  int  $amount
   * @param  int  $type
   * @return void
   */
  public function has(int $iid, int $amount, int $type = self::ALL) {
    $value = $this->items($iid, true)->backpack(false);
    $value = $value[$iid];
    if ($type === self::ALL) {
      return $value['all'] >= $amount;
    }else if($type === self::VALID) {
      return $value['valid'] >= $amount;
    }else if($type === self::GENERAL) {
      return $value['amount'] >= $amount;
    }else if($type === self::LOCKED) {
      return $value['locked_amount'] >= $amount;
    }else{
      return false;
    }
  }

  /**
   * 增加物品
   * @param  int  $iid
   * @param  int  $amount
   * @param  int  $is_locked
   * @return bool
   */
  public function add(int $iid, int $amount = 0, int $is_locked = self::GENERAL): bool {
    if ($is_locked === self::GENERAL) {
      $column = 'amount';
      $operation_amount = $amount;  // 操作数量
      $amount = $amount;
      $locked_amount = 0;
    }else if($is_locked === self::LOCKED){
      $column = 'locked_amount';
      $operation_amount = $amount;
      $locked_amount = $amount;
      $amount = 0;
    }else{
      return false;
    }
    // 检查物品信息是否存在
    $exists = DB::table('v3_user_backpack')
                ->where('uid', $this->uid)
                ->where('iid', $iid)
                ->sharedLock()
                ->exists();
    if ($exists) {
      $db = DB::table('v3_user_backpack')
              ->where('uid', $this->uid)
              ->where('iid', $iid)
              ->lockForUpdate()
              ->increment($column, $operation_amount);
    }else{
      $data = [
        'uid'     => $this->uid,
        'iid'     => $iid,
        'amount'  => $amount,
        'locked_amount' => $locked_amount,
        'status'  => 1
      ];
      $db = DB::table('v3_user_backpack')->insert($data);
    }
    return $db;
  }

  /**
   * 扣除物品
   * @param  int $iid
   * @param  int $amount
   * @param  int $type
   * @return bool|int
   */
  public function reduce(int $iid, int $amount = 0, int $type = self::LOCKED_FIRST) {
    // 检查物品信息是否存在
    $item = DB::table('v3_user_backpack')
                ->where('uid', $this->uid)
                ->where('iid', $iid)
                ->sharedLock()
                ->first();
    if ($item) {
      // 检查物品总数
      if ($type === self::GENERAL_FIRST || $type === self::LOCKED_FIRST) {
        if ($item->amount + $item->locked_amount < $amount) {
          return self::INSUFFICIENT;
        }
      }else if($type === self::GENERAL_ONLY || $type === self::LOCKED_ONLY) {
        if ($type === self::GENERAL_ONLY) {
          if ($item->amount < $amount) {
            return self::INSUFFICIENT;
          }
        }else if($type === self::LOCKED_ONLY) {
          if ($item->locked_amount < $amount) {
            return self::INSUFFICIENT;
          }
        }
      }
      // 声明函数
      $decrement = function(int $uid, int $iid, string $column, int $amount) {
        return DB::table('v3_user_backpack')
                  ->where('uid', $this->uid)
                  ->where('iid', $iid)
                  ->lockForUpdate()
                  ->decrement($column, $amount);
      };
      // 初始化
      $db1 = true;
      $db2 = true;
      $_general = $item->amount;
      $_locked  = $item->locked_amount;
      $_frozen  = $item->frozen;
      // 判断扣除模式
      if ($type === self::LOCKED_FIRST) {
        // 检查锁定物品数量
        if ($item->locked_amount > 0) {
          // 计算扣除数量
          $red_amount = $item->locked_amount > $amount ? $amount : $item->locked_amount;
          $db1 = $decrement($this->uid, $iid, 'locked_amount', $red_amount);
          $_locked -= $red_amount;
          $amount -= $red_amount;
        }
        // 扣除剩余部分
        if ($amount > 0) {
          $db2 = $decrement($this->uid, $iid, 'amount', $amount);
          $_general -= $amount;
        }
      }else if($type === self::GENERAL_FIRST){
        // 检查锁定物品数量
        if ($item['amount'] > 0) {
          // 计算扣除数量
          $red_amount = $item->amount > $amount ? $amount : $item->amount;
          $db1 = $decrement($this->uid, $iid, 'amount', $red_amount);
          $_general -= $red_amount;
          $amount -= $red_amount;
        }
        // 扣除剩余部分
        if ($amount > 0) {
          $db2 = $decrement($this->uid, $iid, 'locked_amount', $amount);
          $_locked -= $amount;
        }
      }else if($type === self::GENERAL_ONLY) {
        // 只扣除通用物品
        $db1 = $decrement($this->uid, $iid, 'amount', $amount);
        $_general -= $amount;
      }else if($type === self::LOCKED_ONLY) {
        // 只扣除锁定物品
        $db1 = $decrement($this->uid, $iid, 'locked_amount', $amount);
        $_locked -= $amount;
      }
      // 数据库节省
      if ($_general === 0 && $_locked === 0 && $_frozen === 0) {
        DB::table('v3_user_backpack')
                  ->where('uid', $this->uid)
                  ->where('iid', $iid)
                  ->lockForUpdate()
                  ->delete();
      }
      return $db1 && $db2;
    }else{
      // 物品不足
      return self::INSUFFICIENT;
    }
  }

  /**
   * 清除物品操作
   * @return object
   */
  public function clear(): object {
    $this->items = [];
    return $this;
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
