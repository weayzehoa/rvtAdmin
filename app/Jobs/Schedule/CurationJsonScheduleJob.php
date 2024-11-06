<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Storage;
use Curl;

class CurationJsonScheduleJob implements ShouldQueue
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
        //首頁策展API路徑
        $curationUrl = 'https://'.env('API_DOMAIN').'/web/v1/curation';
        //首頁策展資料
        $responses = Curl::to($curationUrl)->withData( ['cate' => 'home'] )->get();
        $curations = json_decode($responses);
        Storage::disk('frontend')->put('home.json', json_encode($curations->data,true));
        //首頁策展英文資料
        $responses = Curl::to($curationUrl)->withData( ['cate' => 'home', 'lang' => 'en'] )->get();
        $curations = json_decode($responses);
        Storage::disk('frontend')->put('home_en.json', json_encode($curations->data,true));
    }
}
