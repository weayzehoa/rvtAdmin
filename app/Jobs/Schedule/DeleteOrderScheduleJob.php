<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User as UserDB;
use App\Models\UserPoint as UserPointDB;
use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\ShopcomOrder as ShopcomOrderDB;
use App\Models\Pay2Go as Pay2GoDB;
use Carbon\Carbon;
use DB;
use App\Traits\ShopcomFunctionTrait;

class DeleteOrderScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,ShopcomFunctionTrait;

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
     * 每天清除無效訂單
     * @return void
     */
    public function handle()
    {
        $yesterday = Carbon::now()->subDay();
        //找出訂單
        $orders = OrderDB::where([['status',0],['created_at','<',$yesterday],['create_type','!=','shopee']])
        ->whereNotIn('pay_method',['智付通CVS','智付通ATM'])
        ->select([
            '*',
            'RID' => ShopcomOrderDB::whereColumn('orders.id','shopcom_orders.order_id')->select('RID')->limit(1),
            'Click_ID' => ShopcomOrderDB::whereColumn('orders.id','shopcom_orders.order_id')->select('Click_ID')->limit(1),
        ])->get();
        // dd($orders);
        //處理訂單
        foreach ($orders as $order) {
            //退回購物金
            if($order->spend_point > 0){
                $user = UserDB::find($order->user_id);
                $balance = $user->point + $order->spend_point;
                $user = $user->update(['points', $balance]);
                //購物金紀錄
                $data['user_id'] = $order->user_id;
                $data['point_type'] = '';
                $data['points'] = '';
                $data['balance'] = $balance;
                $userPoint = UserPointDB::create([
                    'user_id' => $order->user_id,
                    'point_type' => "取消訂單 $order->order_number 退回購物金 $order->spend_point 點",
                    'points' => $order->spend_point,
                    'balance' => $balance,
                ]);
            }
            //Shopcom訂單取消處理
            if(!empty($order->RID)){
                $result = $this->cancelSendToShopcom($order->order_number,$order->created_at,$order->amount+$order->parcel_tax,$order->RID,$order->Click_ID);
            }
            //刪除訂單
            $order->fill(['is_del' => 1])->save();
            $order->delete();
        }

        //找出CVS,ATM未付款訂單
        $before14Days = Carbon::now()->subDays(14);
        $orders = OrderDB::where([['status',0],['created_at','<',$before14Days],['create_type','!=','shopee']])
        ->whereIn('pay_method',['智付通CVS','智付通ATM'])
        ->select([
            '*',
            'RID' => ShopcomOrderDB::whereColumn('orders.id','shopcom_orders.order_id')->select('RID')->limit(1),
            'Click_ID' => ShopcomOrderDB::whereColumn('orders.id','shopcom_orders.order_id')->select('Click_ID')->limit(1),
        ])->get();
        // dd($orders);
        //處理訂單
        foreach ($orders as $order) {
            //退回購物金
            if($order->spend_point > 0){
                $user = UserDB::find($order->user_id);
                $balance = $user->point + $order->spend_point;
                $user = $user->update(['points', $balance]);
                //購物金紀錄
                $data['user_id'] = $order->user_id;
                $data['point_type'] = '';
                $data['points'] = '';
                $data['balance'] = $balance;
                $userPoint = UserPointDB::create([
                    'user_id' => $order->user_id,
                    'point_type' => "取消訂單 $order->order_number 退回購物金 $order->spend_point 點",
                    'points' => $order->spend_point,
                    'balance' => $balance,
                ]);
            }
            //Shopcom訂單取消處理
            if(!empty($order->RID)){
                $result = $this->cancelSendToShopcom($order->order_number,$order->created_at,$order->amount+$order->parcel_tax,$order->RID,$order->Click_ID);
            }
            //刪除訂單
            $order->fill(['is_del' => 1])->save();
            $order->delete();
        }

        //pay2go重複
        // $orderNumbers = Pay2GoDB::select('order_number')->groupBy('order_number')->havingRaw('COUNT(id) > 1')->get();
        // $maxIds = Pay2GoDB::selectRaw('max(id)')->groupBy('order_number')->havingRaw('COUNT(id) > 1')->get();
        // $pay2go = Pay2GoDB::whereIn('order_number',$orderNumbers)->where('get_json','not like','SUCCESS%')->whereNotIn('id',$maxIds)->delete();
        //刪除pay2go失敗的
        $pay2gos = Pay2GoDB::where('get_json','not like','SUCCESS%')->delete();
    }
}
