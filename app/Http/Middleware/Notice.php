<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;

class Notice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, string $place_id) {
        // var_dump($place_id);
        $place_id = explode('+', $place_id);
        $notices = [];
        $now = date('Y-m-d H:i:s');
        foreach ($place_id as $key => $value) {
          $notice = DB::table('notices')
                  ->where('endtime', '>=', $now)
                  ->where('place_id', $value)
                  ->where('starttime', '<=', $now)
                  ->where('status', 1)
                  ->orWhere('endtime', '1970-01-01 00:00:00')
                  ->where('place_id', $value)
                  ->where('starttime', '<=', $now)
                  ->where('status', 1)
                  ->orderBy('priority', 'asc')
                  ->get()
                  ->map(function($value){return (Array)$value;})
                  ->toArray();
          if ($notice) {
            $notices = array_merge($notices, $notice);
          }
        }
        $data = [
          'notices'  => $notices
        ];
        // $request->attributes->add($data);
        view()->share('_notices', $notices);
        return $next($request);
    }
}
