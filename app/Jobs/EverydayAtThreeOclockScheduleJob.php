<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Schedule\DeleteOrderScheduleJob as DeleteOrder;
use App\Jobs\Schedule\HotProductScheduleJob as HotProduct;

class EverydayAtThreeOclockScheduleJob implements ShouldQueue
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
        //每天清除無效訂單
        DeleteOrder::dispatchNow();
        //每天設定熱門商品
        HotProduct::dispatchNow();
    }
}
