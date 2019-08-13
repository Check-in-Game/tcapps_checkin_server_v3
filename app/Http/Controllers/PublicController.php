<?php

namespace App\Http\Controllers;

use DB;
use Cookie;
use Captcha;
use Illuminate\Http\Request;
use App\Http\Controllers\Common\UserAuth;
use App\Http\Controllers\Common\BackpackManager as BM;

class PublicController extends Controller {

    public function index() {
      return view('public.index');
    }

    public function register() {
      if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['comfirm'])
        && !empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['comfirm'])
        && isset($_POST['captcha']) && !empty($_POST['captcha']) ){
        // 查询系统是否允许注册
        $reg = DB::table('system')->where('skey', 'register_available')->first();
        if (!$reg || $reg->svalue === 'false') {
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '非常抱歉！注册通道被临时关闭，请留意首页公告。',
          ]);
        }
        // 进入注册流程
        $username = strtolower($_POST['username']);
        $password = $_POST['password'];
        $comfirm = $_POST['comfirm'];
        $captcha = $_POST['captcha'];
        // 判断用户名是否合法
        $pattern = "/^[a-zA-Z][a-zA-Z0-9_]{4,15}$/";
        $preg = preg_match($pattern, $username);
        if (!$preg) {
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！用户名必须是5-16位的不含除下划线外的特殊字符的英文与数字的组合。',
          ]);
        }
        // 判断密码是否合法
        $pattern = "/^.{6,16}$/";
        if(!preg_match($pattern,$password)){
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！用户名长度不得超过16位，不得低于5位；密码长度不得超过16位，不得低于8位。',
          ]);
        }
        // 检查一致性
        if ($password !== $comfirm) {
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！两次密码不一致。',
          ]);
        }
        // 检查验证码
        if (!Captcha::check($captcha)) {
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '验证码不正确。',
          ]);
        }
        // 检查用户是否存在
        $db = DB::table('v3_user_accounts')->where('username', $username)->first();
        if ($db) {
          // 用户已经存在
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！该用户名可能已经存在。',
          ]);
        }
        $password = UserAuth::generate_password($password);
        // 写入用户表
        $data = array(
          'username'      => $username,
          'nickname'      => $username,
          'password'      => $password,
          'register_at'   => date('Y-m-d H:i:s'),
          'update_at'     => date('Y-m-d H:i:s'),
          'status'        => $status = 0, // 未验证邮箱
        );
        $uid = DB::table('v3_user_accounts')->insertGetId($data);
        if (!$uid) {
          return view('public.register',[
            'reg_status' => false,
            'reg_error' => '注册失败！未知原因。',
          ]);
        }else{
          $auth = UserAuth::generate_auth($password, $uid, $status);
          Cookie::queue('uid', $uid);
          Cookie::queue('auth', $auth);
          return redirect('user');
        }
      }else{
        return view('public.register');
      }
    }

    // 登录
    public function login() {
      return view('public.login');
    }

    // 老用户数据迁移
    public function login_old() {
      return view('public.login_old');
    }

    // 告警页面
    public function alert($error, $content) {
      return view('public.alert', ['error' => $error, 'content' => $content]);
    }
}
