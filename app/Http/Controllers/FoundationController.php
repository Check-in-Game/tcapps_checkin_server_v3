<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FoundationController extends Controller {

    // 招募计划
    public function recruit() {
      $uid = request()->cookie('uid');
      return view('foundation.recruit');
    }
}
