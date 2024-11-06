<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\GateIpAddress as IpAddressDB;
use App\Models\GateSystemSetting as SystemSettingDB;

class CheckIpMiddleware
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
        $whiteIps = IpAddressDB::where('is_on',1)->select('ip')->get()->pluck('ip')->all();
        $systemSetting = SystemSettingDB::first();
        !empty($systemSetting->disable_ip_start) ? $startTime = $systemSetting->disable_ip_start : $startTime = null;
        !empty($systemSetting->disable_ip_end) ? $endTime = $systemSetting->disable_ip_end : $endTime = null;

        if(strtotime($startTime) <= strtotime(date('Y-m-d')) && strtotime(date('Y-m-d')) <= strtotime($endTime)){
            if (auth()->user() && in_array(auth()->id(), [40])) {
                \Debugbar::enable();
            }
            else {
                \Debugbar::disable();
            }
        }else{
            if(!empty($request->header('x-forwarded-for'))){
                $ip = $request->header('x-forwarded-for');
            }else{
                $ip = $request->ip();
            }
            if (!in_array($ip, $whiteIps)) {
                return response("your ip address ( $ip ) is not valid.")->header('Content-Type', 'text/plain');
                // return redirect()->to('https://google.com');
            }else{
                if (auth()->user() && in_array(auth()->id(), [40])) {
                    \Debugbar::enable();
                }
                else {
                    \Debugbar::disable();
                }
            }
        }
        return $next($request);
    }
}
