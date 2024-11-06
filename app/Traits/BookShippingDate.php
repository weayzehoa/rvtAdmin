<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use App\Models\ReceiverBaseSetting as ReceiverBaseSettingDB;

trait BookShippingDate
{
    public function bookShippingDate($pickupDate,$shippingMethod)
    {
        $date = new Carbon($pickupDate.' 00:00:00', 'Asia/Taipei');
        $startDate = substr($date->subDay(),0,10);
        $nextDate = substr($date->subDay(),0,10);
        $thirDate = substr($date->subDay(),0,10);
        $endDate = substr($date->subDay(10),0,10); //+取10天資料來推算
        $tmp1 = ReceiverBaseSettingDB::where([['select_date','>=',$nextDate],['select_date','<=',$startDate],['type','out']]);
        if($shippingMethod == 1){ //從前兩天算 (對調)
            $tmp1 = $tmp1->orderBy('select_date','asc')->get()->toArray();
        }else{
            $tmp1 = $tmp1->orderBy('select_date','desc')->get()->toArray();
        }
        $tmp2 = ReceiverBaseSettingDB::where([['select_date','>=',$endDate],['select_date','<=',$thirDate],['type','out']])->orderBy('select_date','desc')->get()->toArray();
        $tmps = array_merge($tmp1,$tmp2);
        $tmps = collect($tmps)->groupBy('select_date');
        foreach($tmps as $date => $tmp){
            foreach($tmp as $t){
                if($t['is_ok'] == 1){
                    $out = $date;
                    break 2;
                }
            }
        }
        return $out;
    }
}
