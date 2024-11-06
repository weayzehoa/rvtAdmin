<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SmsSchedule as SmsScheduleDB;
use App\Jobs\AdminSendSMS;
use DB;

class SMSScheduleJob implements ShouldQueue
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
        $smsSchedules = SmsScheduleDB::where('is_send','!=',1)->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d') = CURRENT_DATE()")
            ->select([
                '*',
                DB::raw("(UNIX_TIMESTAMP(created_at)-3600) as create_timestamp"),
            ])->orderBy('id','asc')->get();
        if(!empty($smsSchedules)){
            foreach($smsSchedules as $smsSchedule){
                if((time()+(7*60*60)) >= $smsSchedule->create_timestamp){
                    if(!empty(trim($smsSchedule->message))){
                        $sms['supplier'] = 'aws';
                        $sms['user_id'] = $smsSchedule->user_id;
                        $sms['phone'] = $smsSchedule->mobile;
                        $sms['message'] = trim($smsSchedule->message);
                        $sms['return'] = true;
                        $status = AdminSendSMS::dispatchNow($sms); //馬上執行
                    }
                }
                if($status['status'] == '傳送成功'){
                    $smsSchedule->update(['is_send' => 1, 'sms_vendor' => $status['sms_vendor']]);
                }
            }
        }
    }
}
