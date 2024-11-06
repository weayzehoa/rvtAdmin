<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Order as OrderDB;
use App\Models\User as UserDB;
use App\Models\UserPoint as UserPointDB;
use App\Models\ShippingVendor as ShippingVendorDB;
use App\Models\ServiceMessage as ServiceMessageDB;
use App\Models\SystemSetting as SystemSettingDB;
use App\Models\SmsSchedule as SmsScheduleDB;

use App\Jobs\AdminSendSMS;
use App\Jobs\AdminInvoiceJob;
use Auth;
use Curl;

use App\Traits\ShopcomFunctionTrait;

class AdminOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,ShopcomFunctionTrait;

    protected $param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $param = $this->param;
        isset($param['ids']) ? is_array($param['ids']) ? $this->ids = $param['ids'] : $this->ids = $param['ids'] = [$param['ids']] : '';
        isset($param['oldStatus']) ? is_array($param['oldStatus']) ? $this->oldStatus = $param['oldStatus'] : $this->oldStatus = $param['oldStatus'] = [$param['oldStatus']] : '';
        isset($param['return']) ? $returnFlag = $param['return'] : $returnFlag = false;

        if(count($param['ids']) == count($param['oldStatus'])){
            for($i=0;$i<count($param['ids']);$i++){
                $id = $param['ids'][$i];
                $oldStatus = $param['oldStatus'][$i];
                $notify = false;
                //取出訂單資料
                $order = OrderDB::with('user','items','shippings','shopcom','tradevan')->withTrashed()->findOrFail($id);
                //新狀態
                $newStatus = $order->status;
                //取出物流單號並去除前後空白
                foreach (explode(',',$order->shipping_number) as $shippingNumber){
                    $shippingNumbers[] = trim($shippingNumber);
                }
                //取貨時間及電話資料
                $receiverKeyTime5char=str_replace('-', '/', substr($order->receiver_key_time, 5, 5));
                $receiverKeyTimeHm=substr($order->receiver_key_time, 11, 5);
                $receiverTel=str_replace(array("o","-"," ","++"),array("+","","","+"),$order->receiver_tel);

                //取消訂單返回購物金及其他動作
                if($order->status == -1 && $oldStatus != -1 ){
                    if($order->spend_point > 0){
                        //花費購物金 + 使用者剩餘購物金
                        $balance = $order->spend_point + $order->user->point;
                        $order->user->update(['points' => $balance]);
                        $userPoint = UserPointDB::create([
                            'user_id' => $order->user->id,
                            'point_type' => "取消訂單 {$order->order_number} 退回購物金 {$order->spend_point} 點",
                            'points' => $order->spend_point,
                            'balance' => $balance,
                        ]);
                    }
                    if($order->shopcom){
                        $this->cancelSendToShopcom($order->order_number,$order->created_at,$order->amount+$order->parcel_tax,$order->shopcom->RID,$order->shopcom->Click_ID);
                    }
                    if($order->tradevan){
                        $this->cancelSendToTradevan($order->order_number,$order->created_at,$order->amount+$order->parcel_tax,$order->tradevan->RID,$order->tradevan->Click_ID);
                    }

                    //作廢發票, 檢查是否有發票號碼, 且不曾作廢過
                    if($order->is_invoice_no && $order->is_invoice_cancel == 0){
                        $set['id'] = $id; //order id, 可用陣列或單一
                        $set['type'] = 'cancel'; //類別:開立
                        $set['reason'] = '取消訂單'; //取消理由
                        $set['return'] = false; //true 返回訊息 false 不返回
                        env('APP_ENV') == 'production' ? AdminInvoiceJob::dispatchNow($set) : ''; //測試機不執行
                    }

                    //通知購買者訂單已取消 {{ 功能尚未製作 }}
                }

                //訂單狀態等於 3 且 receiver_name != "蝦皮台灣特選店" 更新訂單 shipping_time 欄位
                if($order->status == 3  && $oldStatus <= 2 && $order->receiver_name != '蝦皮台灣特選店'){
                    //檢查是否有物流單資料, 且不等於廠商發貨.
                    //廠商發貨則不發通知, 由廠商後台來發 (此job可與廠商後台共用)
                    if(count($order->shippings)>0){
                        $notify = true;
                        foreach($order->shippings as $shipping){
                            $shipping->express_way == '廠商發貨' ? $notify = false : '';
                            break;
                        }
                    }
                }

                //取發票
                if( $notify == true ){
                    //到Pay2Go取發票, 檢查是否沒有發票號碼, 且不曾作廢過
                    if($order->is_invoice_no == '' && $order->is_invoice_cancel == 0){
                        $set['id'] = $id; //order id, 可用陣列或單一
                        $set['type'] = 'create'; //類別:開立
                        $set['return'] = false; //true 返回訊息 false 不返回
                        env('APP_ENV') == 'production' ? AdminInvoiceJob::dispatchNow($set) : ''; //測試機不執行
                    }
                    //通知旗標等於true則更新出貨時間欄位
                    $order->update(['shipping_time' => date('Y-m-d H:i:s')]);
                }

                //發出貨通知
                if( $notify == true ){//因為要將server_message 中的klook原先填寫訂單編號改成合作編號=>klook編號
                    if($order->shipping_method==1){
                        $flyMessage = [];
                        $flyMessageEn = [];
                        if(count($shippingNumbers) > 0){
                            foreach($shippingNumbers as $key => $shippingNumber){
                                foreach($order->shippings as $shipping){
                                    $shipping->express_no == $shippingNumber ? $expressWay = $shipping->express_way : '';
                                    break;
                                }
                                if(($order->create_type=="klook"  || $order->create_type=="KKday") && $order->receiver_address=="松山機場/第一航廈台灣宅配通（E門旁）"){
                                    $flyMessage[]="您好，您訂購的iCarry【訂單編號 / {$order->partner_order_number}】可在{$receiverKeyTime5char}於松山機場提貨。我司會將商品交寄至松山機場內的“台灣宅配通服務台”位於E門旁，您的商品取貨號：{$shippingNumber}，取件人:{$order->receiver_name} 。";
                                    $flyMessageEn[]="Hi! Your order【{$order->partner_order_number}】 will be ready for pick up on {$receiverKeyTime5char} at Pelican Songshan Airport counter which is next to Door E. ✈Pickup No.: {$shippingNumber} Receiver: {$order->receiver_name} .";
                                }elseif($expressWay=="台灣宅配通"){
                                    if($order->create_type=="klook" || $order->create_type=="KKday"){
                                        if($order->receiver_address=="桃園機場/第一航廈出境大廳門口"){
                                            $flyMessage[]="您好，您訂購的iCarry【訂單編號 / {$order->partner_order_number}】可在{$receiverKeyTime5char}於桃園機場提貨。我司會將商品交寄至宅配通第一航廈（桃園機場航廈-營業站）即「行李寄存打包處」，位於出境大厅12號櫃台旁，您的商品取貨號：{$shippingNumber} 取件人: {$order->receiver_name}。";
                                            $flyMessageEn[]="Hi! Your order【{$order->partner_order_number}】 will be ready for pick up on {$receiverKeyTime5char} at Pelican Tayouan T1 counter which is next to No.12 check-in counter (north side of the 1st floor departure hall). ✈Pickup No.: {$shippingNumber} Receiver: {$order->receiver_name}";
                                        }elseif($order->receiver_address=="桃園機場/第二航廈出境大廳門口"){
                                            $flyMessage[]="您好，您訂購的iCarry【訂單編號 / {$order->partner_order_number}】可在{$receiverKeyTime5char}於桃園機場提貨。我司會將商品交寄至宅配通第二航廈（桃園機場航廈-營業站）即「行李寄存打包處」，位於出境大厅19號櫃台旁，您的商品取貨號：{$shippingNumber} 取件人: {$order->receiver_name}。";
                                            $flyMessageEn[]="Hi! Your order【{$order->partner_order_number}】 will be ready for pick up on {$receiverKeyTime5char} at Pelican Tayouan T2 counter which is next to No.19 check-in counter (south side of the 3rd floor departure hall). ✈Pickup No.: {$shippingNumber} Receiver: {$order->receiver_name}";
                                        }else if($order->receiver_address=="松山機場/第一航廈台灣宅配通（E門旁）"){
                                            $flyMessage[]="您好，您訂購的iCarry【訂單編號 / {$order->partner_order_number}】可在{$receiverKeyTime5char}於松山機場提貨。我司會將商品交寄至松山機場內的“台灣宅配通服務台”位於E門旁，您的商品取貨號：{$shippingNumber}，取件人:{$order->receiver_name}，黑猫松机服務櫃檯電話:02-25464772。";
                                            $flyMessageEn[]="Hi! Your order【{$order->partner_order_number}】 will be ready for pick up on {$receiverKeyTime5char} at Pelican Songshan Airport counter which is next to Door E. ✈Pickup No.: {$shippingNumber}  Receiver: {$order->receiver_name}, Counter Tel.: 02-25464772";
                                        }elseif($order->receiver_address=="花蓮航空站/挪亞方舟旅遊"){
                                            $flyMessage[]="您好，您訂購的iCarry【訂單編號 / {$order->partner_order_number}】可在{$receiverKeyTime5char}於桃園機場提貨。我司會將商品交寄至諾亞方舟旅遊位於 1 樓國際線入境大廳出口處，您的商品取貨號：{$shippingNumber} 取件人: {$order->receiver_name}。";
                                            $flyMessageEn[]="Hi! Your order【{$order->partner_order_number}】 will be ready for pick up on {$receiverKeyTime5char} at Noah’s Ark Tour is located at the exit of the International Line Entry Hall on the 1st floor. ✈Pickup No.: {$shippingNumber} Receiver: {$order->receiver_name}";
                                        }
                                    }else{
                                        if($order->receiver_address=="桃園機場/第一航廈出境大廳門口"){
                                            $flyMessage[]="您好，您訂購的iCarry【訂單編號 / {$order->order_number}】可在{$receiverKeyTime5char}於桃園機場提貨。我司會將商品交寄至宅配通第一航廈（桃園機場航廈-營業站）即「行李寄存打包處」，位於出境大厅12號櫃台旁，您的商品取貨號：{$shippingNumber} 取件人: {$order->receiver_name}。";
                                            $flyMessageEn[]="Hi! Your order【{$order->order_number}】 will be ready for pick up on {$receiverKeyTime5char} at Pelican Tayouan T1 counter which is next to No.12 check-in counter (north side of the 1st floor departure hall). ✈Pickup No.: {$shippingNumber} Receiver: {$order->receiver_name}";
                                        }elseif($order->receiver_address=="桃園機場/第二航廈出境大廳門口"){
                                            $flyMessage[]="您好，您訂購的iCarry【訂單編號 / {$order->order_number}】可在{$receiverKeyTime5char}於桃園機場提貨。我司會將商品交寄至宅配通第二航廈（桃園機場航廈-營業站）即「行李寄存打包處」，位於出境大厅19號櫃台旁，您的商品取貨號：{$shippingNumber} 取件人: {$order->receiver_name}。";
                                            $flyMessageEn[]="Hi! Your order【{$order->order_number}】 will be ready for pick up on {$receiverKeyTime5char} at Pelican Tayouan T2 counter which is next to No.19 check-in counter (south side of the 3rd floor departure hall). ✈Pickup No.: {$shippingNumber} Receiver: {$order->receiver_name}";
                                        }else if($order->receiver_address=="松山機場/第一航廈台灣宅配通（E門旁）"){
                                            $flyMessage[]="您好，您訂購的iCarry【訂單編號 / {$order->order_number}】可在{$receiverKeyTime5char}於松山機場提貨。我司會將商品交寄至松山機場內的“台灣宅配通服務台”位於E門旁，您的商品取貨號：{$shippingNumber}，取件人:{$order->receiver_name}，黑猫松机服務櫃檯電話:02-25464772。";
                                            $flyMessageEn[]="Hi! Your order【{$order->order_number}】 will be ready for pick up on {$receiverKeyTime5char} at Pelican Songshan Airport counter which is next to Door E. ✈Pickup No.: {$shippingNumber}  Receiver: {$order->receiver_name}, Counter Tel.: 02-25464772";
                                        }elseif($order->receiver_address=="花蓮航空站/挪亞方舟旅遊"){
                                            $flyMessage[]="您好，您訂購的iCarry【訂單編號 / {$order->order_number}】可在{$receiverKeyTime5char}於桃園機場提貨。我司會將商品交寄至諾亞方舟旅遊位於 1 樓國際線入境大廳出口處，您的商品取貨號：{$shippingNumber} 取件人: {$order->receiver_name}。";
                                            $flyMessageEn[]="Hi! Your order【{$order->order_number}】 will be ready for pick up on {$receiverKeyTime5char} at Noah’s Ark Tour is located at the exit of the International Line Entry Hall on the 1st floor. ✈Pickup No.: {$shippingNumber} Receiver: {$order->receiver_name}";
                                        }
                                    }
                                }
                            }
                        }
                    }else{
                        if(count($shippingNumbers) == 0){
                            if($order->create_type=="klook" || $order->create_type=="KKday"){
                                $message="您好，您的訂單【訂單編號 / $order->partner_order_number 】已經為您出貨了，謝謝您。";
                            }else{
                                $message="您好，您的訂單【訂單編號 / $order->order_number 】已經為您出貨了，謝謝您。";
                            }
                        }else{
                            $expressWay = "";
                            $expressWayEn = "";
                            foreach($order->shippings as $shipping){
                                $tmp = ShippingVendorDB::where('name',$shipping->express_way)->select('name_en')->first();
                                if(count($order->shippings)<=1){
                                    $expressWay = $shipping->express_way;
                                    $expressWayEn = $tmp->name_en;
                                }else{
                                    $expressWay.=','.$shipping->express_way;
                                    $expressWayEn.=','.$tmp->name_en;
                                }
                            }
                            $expressWay = ltrim($expressWay,',');
                            $expressWayEn = ltrim($expressWayEn,',');
                            if($order->create_type=="klook" || $order->create_type=="KKday" || $order->create_type=="Amazon"){
                                $message="您好，您的訂單【訂單編號 / {$order->partner_order_number}】已經為您出貨了，您可以至【物流業者 / {$expressWay}】查詢您的快遞單號【快遞單號 / {$order->shipping_number}】，謝謝您。";
                                $messageEn="Hi! Your order【{$order->partner_order_number}】 has been shipped out. Your tracking number is {$order->shipping_number} . You may check your parcel via {$expressWayEn}. Thank you!";
                            }else{
                                $message="您好，您的訂單【訂單編號 / {$order->order_number}】已經為您出貨了，您可以至【物流業者 / {$expressWay}】查詢您的快遞單號【快遞單號 / {$order->shipping_number}】，謝謝您。";
                                $messageEn = '';
                            }
                        }
                    }

                    if($message){
                        $serviceMessage = ServiceMessageDB::create([
                            'from_id' => 0,
                            'to_id' => $order->user_id,
                            'message' => $message,
                            'admin_id' => Auth::user()->id ?? '',
                        ]);
                        if($messageEn){
                            $serviceMessage = ServiceMessageDB::create([
                                'from_id' => 0,
                                'to_id' => $order->user_id,
                                'message' => $messageEn,
                                'admin_id' => Auth::user()->id ?? '',
                            ]);
                        }
                    }else{
                        if(count($flyMessage) > 0){
                            foreach($flyMessage as $key => $Message){
                                $serviceMessage = ServiceMessageDB::create([
                                    'from_id' => 0,
                                    'to_id' => $order->user_id,
                                    'message' => $Message,
                                    'admin_id' => Auth::user()->id ?? '',
                                ]);

                                $serviceMessage = ServiceMessageDB::create([
                                    'from_id' => 0,
                                    'to_id' => $order->user_id,
                                    'message' => $flyMessageEn[$key],
                                    'admin_id' => Auth::user()->id ?? '',
                                ]);

                                //發送SMS改由排程方式
                                if(!empty($Message)){ //中文訊息
                                    $smsSchedule = SmsScheduleDB::create([
                                        'order_id' => $order->id,
                                        'user_id' => $order->user_id,
                                        'mobile' => UserDB::selectRaw("CONCAT(nation,mobile) as mobile")->find($order->user_id),
                                        'message' => $Message,
                                    ]);
                                }
                                if(!empty($flyMessageEn[$key])){ //英文訊息
                                    $smsSchedule = SmsScheduleDB::create([
                                        'order_id' => $order->id,
                                        'user_id' => $order->user_id,
                                        'mobile' => UserDB::selectRaw("CONCAT(nation,mobile) as mobile")->find($order->user_id),
                                        'message' => $flyMessageEn[$key],
                                    ]);
                                }

                                // //發送SMS
                                // $systemSetting = SystemSettingDB::first();
                                // $sms['user_id'] = $order->user_id;
                                // $sms['supplier'] = $systemSetting->sms_supplier;
                                // $sms['message'] = $Message.' '.$flyMessageEn[$key];
                                // $sms['phone'] = $order->receiver_tel;
                                // // AdminSendSMS::dispatch($sms); //放入隊列
                                // AdminSendSMS::dispatchNow($sms); //馬上執行
                            }
                        }
                    }

                    //蝦皮訂單
                    if($order->create_type == 'shopee'){
                        $shippingNumbers = explode(',',$order->shipping_number);
                        if(count($shippingNumbers > 0)){
                            foreach($shippingNumbers as $key => $shippingNumber){
                                if(!empty($shippingNumber)){
                                    $country= strtoupper($order->partner_country);
                                    $this->shopeeSetTrackingNo($order->partner_order_number,$shippingNumber,$shopee[$country]["Partnerid"],$shopee[$country]["Shopid"],$shopee[$country]["SecretKey"]);
                                }
                            }
                        }
                    }
                }
            }
            if($returnFlag){
                return true;
            }
        }else{
            return null;
        }
    }

    // 下面 function 改由 Traits 共用

    // public function cancelSendToShopcom($order_number,$order_time,$Refund_Amount=0,$RID,$Click_ID){
    //     if(empty($RID)){
    //         return false;
    //     }
    //     $Refund_Amount=($Refund_Amount>0)?$Refund_Amount*-1:$Refund_Amount;
    //     //Refund_Amount=消費者實際針對商品所支付的金額，也就是扣除運費以及折價券後的金額。
    //     /*
    //     <Offer_ID> 是固定字串,我們會於完成第一階段的「雙方網站連結」測試後提供給您 = 3445
    //     <Advertiser_ID>是固定字串,我們會於完成第一階段的「雙方網站連結」測試後提供給您 = 3523
    //     <Commission_Amount>請輸入佣金金額，負數，計算方式為退款的金額乘上店家夥伴商店欲撥給美安公司的佣金百分比。 4%
    //     <Refund_Amount>請輸入退款金額，負數
    //     <Origingal_Order_ID>請輸入此筆訂單原本的訂單編號
    //     <RID>請輸入此筆訂單原本所記錄的RID號碼  [不用]
    //     <Click_ID>請輸入此筆訂單原本所記錄的Click_ID號碼 [不用]
    //     <yyyy-mm-dd> 請輸入訂單日期	 [order_time]
    //     */
    //     $Advertiser_ID=3523;
    //     $Offer_Id=3445;
    //     $Commission_Amount=$Refund_Amount*0.04;
    //     $url="https://api.hasoffers.com/Api?Format=json&Target=Conversion&Method=create&Service=HasOffers&Version=2&NetworkId=marktamerica&NetworkToken=NETPYKNAYOswzsboApxaL6GPQRiY2s&data[offer_id]={$Offer_Id}&data[advertiser_id]={$Advertiser_ID}&data[sale_amount]={$Refund_Amount}&data[affiliate_id]=3&data[payout]={$Commission_Amount}&data[revenue]={$Commission_Amount}&data[advertiser_info]={$order_number}&data[affiliate_info1]={$RID}&data[ad_id]={$Click_ID}&data[is_adjustment]=1&data[session_datetime]={$order_time}";

    //     $Advertiser_ID=3535;
    //     $Offer_Id=3455;
    //     $Commission_Amount=$Refund_Amount*0.06;
    //     $url="https://api.hasoffers.com/Api?Format=json&Target=Conversion&Method=create&Service=HasOffers&Version=2&NetworkId=marktamerica&NetworkToken=NETPYKNAYOswzsboApxaL6GPQRiY2s&data[offer_id]={$Offer_Id}&data[advertiser_id]={$Advertiser_ID}&data[sale_amount]={$Refund_Amount}&data[affiliate_id]=3&data[payout]={$Commission_Amount}&data[revenue]={$Commission_Amount}&data[advertiser_info]={$order_number}&data[affiliate_info1]={$RID}&data[ad_id]={$Click_ID}&data[is_adjustment]=1&data[session_datetime]={$order_time}";
    //     $response = Curl::to($url)->withHeaders(['Content-Type:text/html','charset:utf-8','Accept:text/html'])->get();
    //     return $response;
    // }

    // public function cancelSendToTradevan($order_number,$order_time,$Refund_Amount=0,$RID,$Click_ID){
    //     if(empty($RID)){
    //         return false;
    //     }
    //     $Refund_Amount=($Refund_Amount>0)?$Refund_Amount*-1:$Refund_Amount;
    //     //Refund_Amount=消費者實際針對商品所支付的金額，也就是扣除運費以及折價券後的金額。
    //     $TL_Offer_ID="jZ4fa+yxTn8yohBAjj2Kpqe+F5yltyHtdhoTtQmCdjE=";
    //     $TL_Advertiser_ID="SDmCkqOZqrIvDOAfpucjD6MXvSfLDIrK9ZKdlzD5bks=";
    //     $Commission_Amount=$Refund_Amount*0.06;
    //     $url="https://likeytw.tradevan.com.tw/aptry/likeytw/Api?Method=delete&Service=HasOffers&TL_Offer_ID={$TL_Offer_ID}&TL_Advertiser_ID={$TL_Advertiser_ID}&TL_Refund_Amount={$Refund_Amount}&TL_Commission_Amount={$Commission_Amount}&TL_Order_Id={$order_number}&TL_Rid={$RID}&TL_Click_ID={$Click_ID}&Date_Time={$order_time}";

    //     $response = Curl::to($url)->withHeaders(['Content-Type:text/html','charset:utf-8','Accept:text/html'])->get();
    //     return $response;
    // }

    // public function shopeeSetTrackingNo($ordersn,$shippingNumber,$partnerId,$shopid,$key){
    //     $timestamp=time();
    //     $api="https://partner.shopeemobile.com/api/v1/logistics/tracking_number/set_mass";
    //     $infoIist=array();
    //     $infoIist[]=array(
    //         "ordersn"=>$ordersn,
    //         "tracking_number"=>$shippingNumber
    //     );
    //     $data = array(
    //     "info_list"=>$infoIist,
    //     "partner_id"=>$partnerId,
    //     "shopid"=>$shopid,
    //     "timestamp"=>$timestamp
    //     );
    //     $r=$this->shopeeApiPost($api,$data,$key);
    //     $re=json_decode($r,true);
    //     if(isset($re["result"]["errors"][0])){
    //         return $this->shopeeSetTrackingNoSingle($ordersn,$shippingNumber,$partnerId,$shopid,$key);
    //     }
    //     return $r;
    // }

    // public function shopeeSetTrackingNoSingle($ordersn,$shippingNumber,$partnerId,$shopid,$key){
    //     $timestamp=time();
    //     $api="https://partner.shopeemobile.com/api/v1/logistics/offline/set";
    //     $data = array(
    //     "ordersn"=>$ordersn,
    //     "tracking_number"=>$shippingNumber,
    //     "partner_id"=>$partnerId,
    //     "shopid"=>$shopid,
    //     "timestamp"=>$timestamp
    //     );
    //     return $this->shopeeApiPost($api,$data,$key);
    // }

    // public function shopeeApiPost($api,$data,$key){
    //     $encode_data=json_encode($data);
    //     $authorization=hash_hmac('sha256', $api.'|'.$encode_data, $key);
    //     $curl = curl_init($api);
    //     if(strstr($api,'https://')){//SSL POST
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    //     }
    //     curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    //     curl_setopt($curl, CURLOPT_HEADER, false);
    //     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    //             'Content-Type: application/json',
    //             'Authorization: '.$authorization
    //         )
    //     );
    //     curl_setopt($curl, CURLOPT_POSTFIELDS, $encode_data);
    //     return $response = curl_exec($curl);
    // }
}
