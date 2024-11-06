<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ExportCenter as ExportCenterDB;
use Carbon\Carbon;

class CleanExportFileScheduleJob implements ShouldQueue
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
        $destPath = '/exports/';
        $exports = ExportCenterDB::where('created_at','>',Carbon::now()->subDays(14))->get();
        foreach($exports as $export){
            unlink(public_path().$destPath.$export->filename);
            $export->delete();
        }
    }
}
