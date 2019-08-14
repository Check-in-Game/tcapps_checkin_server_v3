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
      $uid         = request()->cookie('uid');
      $sid         = request()->get('sid') ?? 1;
      $mine        = request()->get('mine');
      if ($sid > 3) {
        $sid = 1;
      }
      $discussions = DB::table('v3_foundation_discuss')
                      ->join('v3_user_accounts', 'v3_user_accounts.uid', 'v3_foundation_discuss.uid')
                      ->where('v3_foundation_discuss.status', '<>', -1)
                      ->orderBy('v3_foundation_discuss.level', 'desc');
      if (is_null($mine)) {
        $discussions = $discussions->where('v3_foundation_discuss.status', $sid);
      }else{
        $discussions = $discussions->where('v3_foundation_discuss.uid', $uid)
                                  ->where('v3_foundation_discuss.status', '<>', 3);
      }
      // 关闭页倒序
      if ($sid == 3) {
        $discussions = $discussions->orderBy('v3_foundation_discuss.did', 'desc');
      }
      $discussions = $discussions->paginate(10);
      $discussions->withPath('?sid=' . $sid);
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

    // 查看议项
    public function details() {
      $did         = request()->get('did') ?? 1;
      $select = [
        'v3_foundation_discuss.did',
        'v3_foundation_discuss.uid',
        'v3_foundation_discuss.tid',
        'v3_foundation_discuss.topic',
        'v3_foundation_discuss.level',
        'v3_foundation_discuss.create_at',
        'v3_foundation_discuss.update_at',
        'v3_foundation_discuss.status',
        'v3_user_accounts.uid',
        'v3_user_accounts.nickname',
      ];
      $discussion = DB::table('v3_foundation_discuss')
                      ->join('v3_user_accounts', 'v3_user_accounts.uid', 'v3_foundation_discuss.uid')
                      ->where('v3_foundation_discuss.did', $did)
                      ->select($select)
                      ->first();
      $comments = DB::table('v3_foundation_discuss_posts')
                ->join('v3_user_accounts', 'v3_user_accounts.uid', 'v3_foundation_discuss_posts.uid')
                ->where('v3_foundation_discuss_posts.did', $did)
                ->where('v3_foundation_discuss_posts.status', 1)
                ->paginate(2);
      $comments->withPath('?did=' . $did);
      $data = [
        'discussion' => $discussion,
        'comments'    => $comments,
      ];
      return view('foundation.discuss.details', $data);
    }

    // 事务大厅
    public function business() {
      $uid         = request()->cookie('uid');
      // 查询基金会积分
      $foundation_point = DB::table('v3_foundation')
                            ->where('fkey', 'point')
                            ->value('fvalue');
      // 查询个人贡献
      $my_credit = DB::table('v3_foundation_credit')
                    ->where('uid', $uid)
                    ->value('credit');
      $my_credit = !$my_credit ? 0 : $my_credit;
      // 查询排行榜
      $charts = DB::table('v3_foundation_credit')
                  ->join('v3_user_accounts', 'v3_user_accounts.uid', '=', 'v3_foundation_credit.uid')
                  ->orderBy('v3_foundation_credit.credit', 'desc')
                  ->limit(10)
                  ->get();
      $data = [
        'charts'            => $charts,
        'my_credit'         => $my_credit,
        'foundation_point'  => $foundation_point,
      ];
      return view('foundation.business.business', $data);
    }
}
