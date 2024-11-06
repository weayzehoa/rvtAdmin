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

class CleanShopeeOrderScheduleJob implements ShouldQueue
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
        //空欄位與null處理 (以前程式填錯資料)
        $orders = OrderDB::where('invoice_address','')->orWhere('invoice_address','null')->update(['invoice_address' => null]);
        $orders = OrderDB::where('invoice_number','')->orWhere('invoice_number','null')->update(['invoice_number' => null]);
        $orders = OrderDB::where('invoice_title','')->orWhere('invoice_title','null')->update(['invoice_title' => null]);
        $orders = OrderDB::where('receiver_id_card','')->orWhere('receiver_id_card','null')->update(['receiver_id_card' => null]);
        $orders = OrderDB::where('receiver_email','')->orWhere('receiver_email','null')->update(['receiver_email' => null]);
        $orders = OrderDB::where('buyer_email','')->orWhere('buyer_email','null')->update(['buyer_email' => null]);

        //找出重複的蝦皮訂單, 並刪除orders與orderItems資料
        $partnerOrderNumbers = OrderDB::whereIn('create_type',['shopee_tw','shopee_sg','shopee_my'])
            ->select('partner_order_number')->groupBy("partner_order_number")
            ->havingRaw("COUNT(partner_order_number) > 1")->distinct('partner_order_number')
            ->get();

        //刪除orderItems資料
        $orderItems = OrderItemDB::whereIn('order_id',OrderDB::where('status',-1)->whereIn('partner_order_number',$partnerOrderNumbers)
            ->select('id')->groupBy('id')->distinct('id')->get())->delete();

        //刪除orders資料
        $orders = OrderDB::where('status',-1)->whereIn('partner_order_number',$partnerOrderNumbers)
            ->select('id')->groupBy('id')->distinct('id')->delete();

        //再次檢查並刪除所有重複訂單orderItems資料 (包含所有狀態)
        $orderItems = OrderItemDB::whereIn('order_id',OrderDB::whereIn('partner_order_number',$partnerOrderNumbers)
            ->select('id')->groupBy('id')->distinct('id')->get())->delete();

        //再次檢查並刪除所有重複orders (包含所有狀態)
        $orders = OrderDB::whereIn('partner_order_number',$partnerOrderNumbers)
            ->select('id')->groupBy('id')->distinct('id')->delete();

        //復原狀態大於0被誤刪的訂單
        $orderItems = OrderItemDB::whereIn('order_id',OrderDB::where('status','>',0)->whereNotNull('deleted_at')->select('id')->groupBy('id')->withTrashed()->get())->withTrashed()->restore();
        $orderIds = OrderDB::where('status','>',0)->whereNotNull('deleted_at')->select('id')->groupBy('id')->withTrashed()->restore();
    }
}
