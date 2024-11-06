<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order as OrderDB;
use App\Jobs\AdminInvoiceJob;

class OpenInvoiceScheduleJob implements ShouldQueue
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
        $param['type'] = 'create'; //類別:開立
        $param['return'] = false; //true 返回訊息 false 不返回

        $orderIds = OrderDB::where([
            ['is_invoice',0],
            ['pay_method','!=','宜睿'],
            ['created_at','>','2017-04-30 23:59:59'],
        ])->whereIn('status', [3,4])
        ->whereNotIn('create_type', ['klook','ctrip','asiamiles','KKday','momo'])
        ->whereNotIn('id', OrderDB::where('user_id', '2020')->whereIn('create_type', ['shopee','其他','klook','宜睿','KKday','HutchGo','蝦皮24H','Viva','玖盈','momo','生活市集','福委會','交流資服','ecKareHK','myhuo','17life','客路','hutchgo','Asiamiles'])->select('id')->groupBy('id')->get())
        ->where(function ($query) {
            $query->where('receiver_name', 'not like', '%蝦皮倉庫%')
            ->where('receiver_name', 'not like', '%蝦皮台灣特選店%')
            ->where('receiver_name', 'not like', '%蝦皮訂單：(台灣)%');
        })->select('id')->groupBy('id')->get()->pluck('id')->all();

        if(!empty($orderIds)){
            $param['id'] = $orderIds; //order id, 可用陣列或單一
            $result = AdminInvoiceJob::dispatchNow($param); //馬上執行
        }

        $orderIds = OrderDB::where([
            ['is_invoice',0],
            ['pay_method','!=','宜睿'],
            ['create_type','like','shopee%'],
            ['created_at','>','2017-04-30 23:59:59'],
            ['receiver_name','like','%蝦皮倉庫%'],
            ['receiver_address','like','%台北市信義區菸廠路88號9樓%'],
        ])->select('id')->groupBy('id')->get()->pluck('id')->all();

        if(!empty($orderIds)){
            $param['id'] = $orderIds; //order id, 可用陣列或單一
            $param['shopee'] = 1; //蝦皮訂單
            $result = AdminInvoiceJob::dispatchNow($param); //馬上執行
        }
    }
}
