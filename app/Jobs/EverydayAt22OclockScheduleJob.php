<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Jobs\Schedule\GovernmentPlanScheduleJob as GovernmentPlan;
use App\Jobs\Schedule\CleanExportFileScheduleJob as CleanExportFile;

class EverydayAt22OclockScheduleJob implements ShouldQueue
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
        env('APP_ENV') == 'production' ? GovernmentPlan::dispatchNow() : ''; //政府計畫排程
        env('APP_ENV') == 'production' ? CleanExportFile::dispatchNow() : ''; //清除匯出中心檔案
    }
}
