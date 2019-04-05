<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PublicController extends Controller {

    public function index() {
      $db_prefix = env('DB_PREFIX');
      $db = DB::table('lists_v2')
              ->join('user_accounts', 'lists_v2.uid', '=', 'user_accounts.uid')
              ->select(DB::raw("{$db_prefix}user_accounts.uid,{$db_prefix}user_accounts.username,sum({$db_prefix}lists_v2.worth) as allWorth"))
              ->groupBy('user_accounts.username')
              ->orderBy('allWorth', 'desc')
              ->limit(100)
              ->get();
      return view('public.index', ['charts' => $db]);
    }

    public function register() {
      if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['comfirm'])
        && !empty($_POST['username'] && !empty($_POST['password']) && !empty($_POST['comfirm']))){
        // 进入注册流程
        $username = $_POST['username'];
        $password = $_POST['password'];
        $comfirm = $_POST['comfirm'];
        // 判断合法性
        if (mb_strlen($username) > 16 || mb_strlen($username) < 5 || mb_strlen($password) > 16 || mb_strlen($password) < 8) {
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！用户名长度不得超过16位，不得低于5位；密码长度不得超过16位，不得低于8位。'
          ]);
        }
        // 检查一致性
        if ($password !== $comfirm) {
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！两次密码不一致。'
          ]);
        }
        $pattern = "/^[a-zA-Z0-9_]+$/";
        $preg = preg_match($pattern, $username);
        if (!$preg) {
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！用户名中不能包含特殊字符。'
          ]);
        }
        // 检查用户是否存在
        $db = DB::table('user_accounts')->where('username', $username)->first();
        if ($db) {
          // 用户已经存在
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！该用户名可能已经存在。'
          ]);
        }
        $password = $this->generate_password($password);
        // 写入用户表
        $data = array(
          'username'    => $username,
          'password'    => $password,
          'status'      => 1          // 有效用户
        );
        $uid = DB::table('user_accounts')->insertGetId($data);
        if (!$uid) {
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！未知原因。'
          ]);
        }
        // 注册Token权限
        $data = array(
          'uid'       => $uid,
          'token'     => '',
          'status'    => -1   // 未初始化
        );
        $db = DB::table('tokens_v2')->insert($data);
        if ($db) {
          return redirect('home');
        }else{
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！未知原因。'
          ]);
        }
      }else{
        // 显示注册页面
        return view('public.register');
      }
    }

    // 在线签到器
    public function webCheckin() {
      return view('public.webCheckin');
    }

}
