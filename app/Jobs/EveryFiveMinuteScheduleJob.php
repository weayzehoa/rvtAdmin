<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Schedule\CheckPointsExprieScheduleJob as CheckPointsExprie;
use App\Jobs\Schedule\OpenInvoiceScheduleJob as OpenInvoice;
use App\Jobs\Schedule\UsdTwdExrateScheduleJob as UsdTwdExrate;
use App\Jobs\Schedule\PushMailScheduleJob as PushMail;
use App\Jobs\Schedule\ClearShopeeOrderScheduleJob as ClearShopeeOrder;

class EveryFiveMinuteScheduleJob implements ShouldQueue
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
        UsdTwdExrate::dispatchNow(); //美金匯率更新
        CheckPointsExprie::dispatchNow(); //檢查購物金到期
        env('APP_ENV') == 'production' ? OpenInvoice::dispatchNow() : ''; //開發票排程測試機不執行
        env('APP_ENV') == 'production' ? PushMail::dispatchNow() : ''; //訂單推送信件
        env('APP_ENV') == 'production' ? CleanShopeeOrder::dispatchNow() : ''; //清除蝦皮訂單
    }
}
