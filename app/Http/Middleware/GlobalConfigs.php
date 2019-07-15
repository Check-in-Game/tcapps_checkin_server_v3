<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class GlobalConfigs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $cdn_prefix = DB::table('system')->where('skey', 'cdn_prefix')->value('svalue');
      $data = [
        'cdn_prefix'  => $cdn_prefix
      ];
      // $request->attributes->add($data);
      view()->share('_system', $data);
      return $next($request);
    }
}
