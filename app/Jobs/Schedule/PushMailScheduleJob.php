<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order as OrderDB;
use App\Models\ShipmentLog as ShipmentLogDB;
use DB;
use App\Jobs\AdminSendEmail;

class PushMailScheduleJob implements ShouldQueue
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
        $orders = OrderDB::where([
            ['status',3],
            ['pay_method','!=','宜睿'],
            ['receiver_email','!=',''],
            ['updated_at','>=','2019-06-05 00:00:00'],
        ])->whereNotNull('receiver_email')
        // ->whereNotIn('id',ShipmentLogDB::select('order_id')->get()->pluck('order_id')->all())
        ->whereRaw(" id not in (SELECT order_id FROM shipment_logs) ") //上面方式 陣列太多筆數，會導致 Prepared statement contains too many placeholders 錯誤，故改成使用原生語句.
        ->select([
            'id',
            'order_number',
            'user_id',
            'shipping_method',
            'create_type',
            DB::raw("MD5(CONCAT('ica',partner_order_number,'ry')) as am_md5"),
            DB::raw("DATE_FORMAT(pay_time,'%Y-%m-%d') as pay_time"),
            'receiver_email', //濾掉 receiver_email 不存在的  以2019-06-05為主 06/19 新增額外濾掉機場提貨
            'receiver_key_time',
            'receiver_address',
            'receiver_name',
            'shipping_number',
            'created_at',
        ])->orderBy('id','desc')->get();

        foreach ($orders as $order) {
            if(strtolower($order->create_type) == 'asiamiles'){
                $mail['type'] = 'asiamilesCertificate'; //信件類別
                $mail['subject'] = 'asiamiles訂單憑證';
            }else{
                $order->shipping_method == 1 ? $mail['type'] = 'orderShipToAirPortNotice' : $mail['type'] = 'orderShipOutNotice';
                $mail['subject'] = 'iCarry訂單出貨通知#'.$order->order_number;
            }
            $mail['to'] = [$order->receiver_email]; //需使用陣列
            $mail['data'] = $order; //製作Body的資料
            $mail['admin_id'] = 0; //系統id=0
            $result = AdminSendEmail::dispatchNow($mail); //馬上執行
            //紀錄shipment_log
            if($result == true){
                ShipmentLogDB::create([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'user_id' => $order->user_id,
                    'shipping_method' => $order->shipping_method,
                    'send' => 1,
                ]);
            }
        }
    }
}
