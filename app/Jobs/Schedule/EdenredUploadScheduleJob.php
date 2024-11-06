<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use Carbon\Carbon;
use File;
use Storage;

class EdenredUploadScheduleJob implements ShouldQueue
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
        // //目的目錄
        // $uploadPath = public_path().'/upload/edenred_update/';
        // //檢查本地目錄是否存在，不存在則建立
        // !file_exists($uploadPath) ? File::makeDirectory($uploadPath, 0755, true) : '';
        // //刪掉資料夾裡面檔案
        // shell_exec("rm -rf $uploadPath*.csv");
        //找宜睿訂單資料
        $orders = OrderDB::where([['status','>',0],['pay_method','宜睿']])
            ->whereRaw("created_at >= DATE_ADD(NOW(),INTERVAL -31 DAY)")
            ->select([
                'id',
                'order_number',
                'status',
                'amount',
                'shipping_number',
                'user_memo',
            ])->get();
        $outFile = [];
        foreach($orders as $order){
            $file = ''; //檔案重置
            $userMemo = explode(",",$order->user_memo);
            $TransactionNo = str_replace("宜睿唯一碼:","",$userMemo["0"]); //宜睿唯一碼
            $EdenredOrderNumber = str_replace("宜睿訂編:","",$userMemo["1"]); //宜睿訂編
            $statusNmae = $this->statusName($order->status);
            $quantity = 0;
            $price = 0;
            $amount = 0;
            $item = OrderItemDB::where('order_id',$order->id)->first();
            if(!empty($item)){
                $quantity = $item->quantity;
                $price = $item->price;
                $amount = $item->price * $item->quantity;
                $file = "{$TransactionNo},{$EdenredOrderNumber},{$order->order_number},{$order->shipping_number},{$item->product_name},{$item->sku},{$quantity},{$price}.00,{$amount}.00,{$order->status},{$this->statusName($order->status)}";
            }
            $outFile[$order->order_number] = $file;
        }
        //生成檔案到指定目錄
        if(!empty($outFile)){
            foreach($outFile as $key => $value){
                // file_put_contents($uploadPath.$key.'.csv',$value,LOCK_EX);
                Storage::disk('EdenredSftp')->put($key.'.csv',$value); //直接sftp到宜睿
            }
        }
    }

    private function statusName($status)
    {
        switch($status){
            case 1:
                return "已付款待出貨";
            break;
            case 2:
                return "集貨中";
            break;
            case 3:
                return "已出貨";
            break;
            case 4:
                return "已完成";
            break;
            default:
                return "已完成";
            break;
        }
    }
}
