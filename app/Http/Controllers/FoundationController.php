<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FoundationController extends Controller {

    // 招募计划
    public function recruit() {
      return view('foundation.recruit');
    }

    // 创建新议项
    public function discuss_new() {
      return view('foundation.discuss.new_discuss');
    }

    // 议事中心
    public function discuss() {
      $tid         = request()->get('tid') ?? 1;
      if ($tid > 3) {
        $tid = 1;
      }
      $discussions = DB::table('v3_foundation_discuss')
                      ->join('v3_user_accounts', 'v3_user_accounts.uid', 'v3_foundation_discuss.uid')
                      ->where('v3_foundation_discuss.status', $tid)
                      ->orderBy('v3_foundation_discuss.level', 'desc')
                      ->paginate(10);
      // 查询评论数量
      $comments_count = [];
      foreach ($discussions as $discussion) {
        $count = DB::table('v3_foundation_discuss_posts')
                  ->where('did', $discussion->did)
                  ->where('status', 1)
                  ->count();
        $comments_count[$discussion->did] = $count ? $count : 0;
      }
      $data = [
        'discussions'     => $discussions,
        'comments_count'  => $comments_count,
      ];
      return view('foundation.discuss.discuss', $data);
    }

}
