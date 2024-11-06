<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Schedule\ShopeeOrderScheduleJob as ShopeeOrder;
use App\Jobs\Schedule\CleanShopeeOrderScheduleJob as CleanShopeeOrder;

class EveryTenMinuteScheduleJob implements ShouldQueue
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
        env('APP_ENV') == 'production' ? ShopeeOrder::dispatchNow() : ''; //檢查蝦皮訂單每十分鐘排程
        CleanShopeeOrder::dispatchNow(); //清除
    }
}
