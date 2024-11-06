<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SystemSetting as SystemSettingDB;


class UsdTwdExrateScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * 每天更新system_settings的exchange_rate_USD欄位[列印物流單(順風速運V2)]
     * @return void
     */
    public function handle()
    {
        $url="http://api.k780.com:88/?app=finance.rate&scur=USD&tcur=TWD&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4";
        $data=file_get_contents($url);
        $data=preg_replace("/^\xef\xbb\xbf/", '', $data);
        $json=json_decode($data,true);
        if(!empty($json)){
            if(!empty($json["success"])){
                if(!empty($json["result"]["rate"])){
                    $SystemSetting = SystemSettingDB::find(1);
                    $SystemSetting = $SystemSetting->update(['exchange_rate_USD' => $json["result"]["rate"]]);
                }
            }
        }
    }
}
