<?php

namespace App\Http\Controllers;

use DB;
use Mail;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController {
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // 生成返回JSON
    public function JSON(string $errno, $error, $body) {
      return [
        'errno'     => $errno,
        'error'     => $error,
        'body'      => $body
      ];
    }

    // 查询系统设置
    public function sysconfig(string $name) {
      $sys = DB::table('system')
              ->where('skey', $name)
              ->value('svalue');
      return $sys;
    }

    // 发送邮件
    public function sendMail(string $view, string $recipient, string $name, string $subject, array $data) : bool {
      set_time_limit(60);
      $data['_recipient'] = $recipient;
      $data['_name'] = $name;
      $data['_subject'] = $subject;
      Mail::send($view, $data, function($message) use($data){
          $message->to($data['_recipient'], $data['_name'])->subject($data['_subject']);
      });
      if (count(Mail::failures()) < 1) {
        return true;
      }else{
        return false;
      }
    }
}
