<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\HotProduct as HotProductDB;
use App\Models\OrderItem as OrderItemDB;
use DB;

class HotProductScheduleJob implements ShouldQueue
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
     * 每天設定熱門商品
     * @return void
     */
    public function handle()
    {
        //清除資料表
        HotProductDB::truncate();
        //找出商家有上線,商品有上架,訂單已出貨或已完成的所有商品筆數
        $orderItems = OrderItemDB::join('vendors','vendors.id','order_items.vendor_id')
        ->join('products','products.id','order_items.product_id')
        ->join('product_models','product_models.id','order_items.product_model_id')
        ->join('orders','orders.id','order_items.order_id')
        ->where([['products.status',1],['vendors.is_on',1],['orders.status','>=',3]])
        ->select([
            'order_items.product_model_id',
            'order_items.product_id',
            'order_items.vendor_id',
            'products.category_id',
            DB::raw("sum(1) as hits"), //使用groupBy時計算筆數用sum(1)
            DB::raw("sum(order_items.quantity) as quantity"), //計算總數量
        ])->groupBy('product_model_id')->orderBy('hits','desc')->get();
        //資料放入資料表
        HotProductDB::insert($orderItems->toArray());
    }
}
