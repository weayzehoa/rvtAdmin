<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Schedule\ShopeeOrderScheduleJob as ShopeeOrder;
use App\Jobs\Schedule\CurationJsonScheduleJob as CurationJson;


class EverydayScheduleJob implements ShouldQueue
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
     *
     * @return void
     */
    public function handle()
    {
        $ktime = time()-(14 * 60 * 60 * 24);
        env('APP_ENV') == 'production' ? ShopeeOrder::dispatchNow($ktime) : ''; //蝦皮訂單每天排程, 抓14天日期
        CurationJson::dispatchNow(); //首頁策展資料輸出Json File
    }
}
