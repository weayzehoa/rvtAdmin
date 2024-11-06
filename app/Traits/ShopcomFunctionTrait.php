<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Curl;

trait ShopcomFunctionTrait
{
    protected function cancelSendToShopcom($order_number,$order_time,$Refund_Amount=0,$RID,$Click_ID){
        if(empty($RID)){
            return false;
        }
        $Refund_Amount=($Refund_Amount>0)?$Refund_Amount*-1:$Refund_Amount;
        //Refund_Amount=消費者實際針對商品所支付的金額，也就是扣除運費以及折價券後的金額。
        /*
        <Offer_ID> 是固定字串,我們會於完成第一階段的「雙方網站連結」測試後提供給您 = 3445
        <Advertiser_ID>是固定字串,我們會於完成第一階段的「雙方網站連結」測試後提供給您 = 3523
        <Commission_Amount>請輸入佣金金額，負數，計算方式為退款的金額乘上店家夥伴商店欲撥給美安公司的佣金百分比。 4%
        <Refund_Amount>請輸入退款金額，負數
        <Origingal_Order_ID>請輸入此筆訂單原本的訂單編號
        <RID>請輸入此筆訂單原本所記錄的RID號碼  [不用]
        <Click_ID>請輸入此筆訂單原本所記錄的Click_ID號碼 [不用]
        <yyyy-mm-dd> 請輸入訂單日期	 [order_time]
        */
        $Advertiser_ID=3523;
        $Offer_Id=3445;
        $Commission_Amount=$Refund_Amount*0.04;
        $url="https://api.hasoffers.com/Api?Format=json&Target=Conversion&Method=create&Service=HasOffers&Version=2&NetworkId=marktamerica&NetworkToken=NETPYKNAYOswzsboApxaL6GPQRiY2s&data[offer_id]={$Offer_Id}&data[advertiser_id]={$Advertiser_ID}&data[sale_amount]={$Refund_Amount}&data[affiliate_id]=3&data[payout]={$Commission_Amount}&data[revenue]={$Commission_Amount}&data[advertiser_info]={$order_number}&data[affiliate_info1]={$RID}&data[ad_id]={$Click_ID}&data[is_adjustment]=1&data[session_datetime]={$order_time}";

        $Advertiser_ID=3535;
        $Offer_Id=3455;
        $Commission_Amount=$Refund_Amount*0.06;
        $url="https://api.hasoffers.com/Api?Format=json&Target=Conversion&Method=create&Service=HasOffers&Version=2&NetworkId=marktamerica&NetworkToken=NETPYKNAYOswzsboApxaL6GPQRiY2s&data[offer_id]={$Offer_Id}&data[advertiser_id]={$Advertiser_ID}&data[sale_amount]={$Refund_Amount}&data[affiliate_id]=3&data[payout]={$Commission_Amount}&data[revenue]={$Commission_Amount}&data[advertiser_info]={$order_number}&data[affiliate_info1]={$RID}&data[ad_id]={$Click_ID}&data[is_adjustment]=1&data[session_datetime]={$order_time}";
        $response = Curl::to($url)->withHeaders(['Content-Type:text/html','charset:utf-8','Accept:text/html'])->get();
        return $response;
    }

    protected function cancelSendToTradevan($order_number,$order_time,$Refund_Amount=0,$RID,$Click_ID){
        if(empty($RID)){
            return false;
        }
        $Refund_Amount=($Refund_Amount>0)?$Refund_Amount*-1:$Refund_Amount;
        //Refund_Amount=消費者實際針對商品所支付的金額，也就是扣除運費以及折價券後的金額。
        $TL_Offer_ID="jZ4fa+yxTn8yohBAjj2Kpqe+F5yltyHtdhoTtQmCdjE=";
        $TL_Advertiser_ID="SDmCkqOZqrIvDOAfpucjD6MXvSfLDIrK9ZKdlzD5bks=";
        $Commission_Amount=$Refund_Amount*0.06;
        $url="https://likeytw.tradevan.com.tw/aptry/likeytw/Api?Method=delete&Service=HasOffers&TL_Offer_ID={$TL_Offer_ID}&TL_Advertiser_ID={$TL_Advertiser_ID}&TL_Refund_Amount={$Refund_Amount}&TL_Commission_Amount={$Commission_Amount}&TL_Order_Id={$order_number}&TL_Rid={$RID}&TL_Click_ID={$Click_ID}&Date_Time={$order_time}";

        $response = Curl::to($url)->withHeaders(['Content-Type:text/html','charset:utf-8','Accept:text/html'])->get();
        return $response;
    }

    protected function shopeeSetTrackingNo($ordersn,$shippingNumber,$partnerId,$shopid,$key){
        $timestamp=time();
        $api="https://partner.shopeemobile.com/api/v1/logistics/tracking_number/set_mass";
        $infoIist=array();
        $infoIist[]=array(
            "ordersn"=>$ordersn,
            "tracking_number"=>$shippingNumber
        );
        $data = array(
        "info_list"=>$infoIist,
        "partner_id"=>$partnerId,
        "shopid"=>$shopid,
        "timestamp"=>$timestamp
        );
        $r=$this->shopeeApiPost($api,$data,$key);
        $re=json_decode($r,true);
        if(isset($re["result"]["errors"][0])){
            return $this->shopeeSetTrackingNoSingle($ordersn,$shippingNumber,$partnerId,$shopid,$key);
        }
        return $r;
    }

    protected function shopeeSetTrackingNoSingle($ordersn,$shippingNumber,$partnerId,$shopid,$key){
        $timestamp=time();
        $api="https://partner.shopeemobile.com/api/v1/logistics/offline/set";
        $data = array(
        "ordersn"=>$ordersn,
        "tracking_number"=>$shippingNumber,
        "partner_id"=>$partnerId,
        "shopid"=>$shopid,
        "timestamp"=>$timestamp
        );
        return $this->shopeeApiPost($api,$data,$key);
    }

    protected function shopeeApiPost($api,$data,$key){
        $encode_data=json_encode($data);
        $authorization=hash_hmac('sha256', $api.'|'.$encode_data, $key);
        $curl = curl_init($api);
        if(strstr($api,'https://')){//SSL POST
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: '.$authorization
            )
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $encode_data);
        return $response = curl_exec($curl);
    }

    protected function writeShopComLog($api,$data,$key){

    }
}
