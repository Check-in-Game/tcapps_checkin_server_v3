<?php

namespace App\Http\Controllers\Api\Admin\Foundation;

use DB;
use Cookie;
use Captcha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class discuss extends Controller {

  /**
   * 设置话题状态
   */
  function setStatus() {
    $did          = request()->post('did');
    $status       = request()->post('status');
    if (is_null($did) || is_null($status)) {
      $json = $this->JSON(404, 'Not found.', null);
      return response($json, 404);
    }
    // 检查类型
    $did = (int) $did;
    $status = (int) $status;
    if (!is_numeric($did) || !is_int($did)
      || !is_numeric($status) || !is_int($status) || !in_array($status, [-1, 1, 2, 3])) {
      $json = $this->JSON(6201, 'Bad request.', null);
      return response($json);
    }
    $data = [
      'update_at' => date('Y-m-d H:i:s'),
      'status'    => $status,
    ];
    DB::table('v3_foundation_discuss')->where('did', $did)->update($data);
    $json = $this->JSON(0, null, ['msg'  => 'Success!']);
    return response($json);
  }
}
