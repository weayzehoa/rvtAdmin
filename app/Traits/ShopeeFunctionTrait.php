<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use App\Models\ReceiverBaseSetting as ReceiverBaseSettingDB;

trait ShopeeFunctionTrait
{
    protected function shopeeApiPost($api,$data,$key){
        $encodeData=json_encode($data);
        $authorization=hash_hmac('sha256', $api.'|'.$encodeData, $key);
        $curl = curl_init($api);
        if(strstr($api,'https://')){//SSL POST
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: '.$authorization
            )
        );
        curl_setopt($curl, CURLOPT_POSTFIELDS, $encodeData);
        return $response = curl_exec($curl);
    }

    protected function getOrdersList($partnerId, $shopId, $key, $createTimeTo = null, $createTimeFrom = null,$paginationEntriesPerPage = 50, $paginationOffset = 0)
    {
        empty($createTimeTo) ? $createTimeTo=time() : '';
        empty($createTimeFrom) ?  $createTimeFrom = $createTimeTo - (14 * 24 * 60 * 60) : '';
        $api="https://partner.shopeemobile.com/api/v1/orders/basics";
        $data = [
          "create_time_from"=>$createTimeFrom,
          "create_time_to"=>$createTimeTo,//最多15天
          "pagination_entries_per_page"=>$paginationEntriesPerPage,
          "pagination_offset"=>$paginationOffset,
          "partner_id"=>$partnerId,
          "shopid"=>$shopId,
          "timestamp"=>time(),
        ];
        return $this->shopeeApiPost($api,$data,$key);
    }

    protected function getOrderDetails($ordersnList, $partnerId, $shopId, $key){
        $api = "https://partner.shopeemobile.com/api/v1/orders/detail";
        $data = array(
          "ordersn_list" => $ordersnList,
          "partner_id" => $partnerId,
          "shopid" => $shopId,
          "timestamp" => time(),
        );
        return $this->shopeeApiPost($api,$data,$key);
    }

    //蝦皮台灣匯入的當天後 2 天，若【可出 / 出貨日】為【不可出】再往後找 1 天
    protected function shopeeBookShippingDate(){
        $selectDate = null;
        $startDate = substr(Carbon::now()->addDays(2),0,10); //後兩天
        $endDate = substr(Carbon::now()->addDays(9),0,10); //找出後9天資料
        $dates = $tmps = ReceiverBaseSettingDB::whereBetween('select_date',[$startDate,$endDate])->orderBy('select_date','asc')->get();
        foreach($dates as $date){
            if($date->type == 'out' && $date->is_ok == 1){
                $selectDate = $date->select_date;
                break;
            }
        }
        return $selectDate;
    }

}
