<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use App\Models\ReceiverBaseSetting as ReceiverBaseSettingDB;

trait ProductAvailableDate
{
    public function productAvailableDate($inputStockDays, $today = null, $type = null)
    {
        //由於有商品備貨日超過34天, 故 endDate 用庫存日+30天作為迴圈避免找錯日期
        $today == null ? $today = date('Y-m-d') : '';
        $today == date('Y-m-d') ? $endDate = Carbon::now()->addDays($inputStockDays + 30) : $endDate = Carbon::create(substr($today,0,4), substr($today,5,2), substr($today,8,2), 0)->addDays($inputStockDays + 30);
        $today == date('Y-m-d') ? $startHour = intval(date("G",strtotime(Carbon::now()))) : $startHour = 12;
        //訂單日期小於2020-01-01的訂單無法算出出貨與提貨日, 直接返回空值
        if($today < '2020-01-01'){
            return null;
        }
        //ReceiverBaseSetting 資料表只到2032年12月31日, 若此function還有在用的話, 需再增加資料, 否則 endDate 會停留在 2032-12-31
        $tmps = ReceiverBaseSettingDB::where([['select_date','>=',$today],['select_date','<=',$endDate]])->orderBy('select_date','asc')->get();
        $tmps = $tmps->groupBy('select_date');
        foreach($tmps as $d => $tmp){
            $everyDays[] = $d;
            foreach($tmp as $t){
                $dates[$d][$t->type] = $t->is_ok;
            }
        }
        $atLeastDays = 0;
        if($inputStockDays == 1){
            if($startHour < 11){ //商品備貨日1天內的商品，中午12點前下單且下單日為【出貨日】計算：提貨
                foreach($dates as $date => $val){
                    if($date == $today){
                        $availableDate = $date;
                        !empty($type) && $type == 'shipping' ? $availableShippingDate = $date : '';
                        break;
                    }
                }
            }else{ //商品備貨日1天內的商品，中午10點後下單 或 中午10點前下單但下單日不是【出貨日】計算：出貨、提貨
                foreach($everyDays as $k=>$d){
                    if($k>$atLeastDays){
                        if($dates[$d]['out']==1){
                            $atLeastDays=$k;
                            !empty($type) && $type == 'shipping' ? $availableShippingDate = $d : '';
                            break;
                        }
                    }
                }
                foreach($everyDays as $k=>$d){
                    if($k>$atLeastDays){
                        if($dates[$d]['pickup']==1){
                            $atLeastDays=$k;
                            $availableDate = $d;
                            break;
                        }
                    }
                }
            }
        }elseif($inputStockDays == 2){ //備貨日為2天
            foreach($everyDays as $k=>$d){
                if($k>$atLeastDays){
                    if($dates[$d]['out']==1){
                        $atLeastDays=$k;
                        !empty($type) && $type == 'shipping' ? $availableShippingDate = $d : '';
                        break;
                    }
                }
            }
            foreach($everyDays as $k=>$d){
                if($k>$atLeastDays){
                    if($dates[$d]['pickup']==1){
                        $atLeastDays=$k;
                        $availableDate = $d;
                        break;
                    }
                }
            }
        }elseif($inputStockDays == 3){
            foreach($everyDays as $k=>$d){
                if($k>$atLeastDays){
                    if($dates[$d]['call']==1){
                        $atLeastDays=$k;
                        break;
                    }
                }
            }
            foreach($everyDays as $k=>$d){
                if($k>$atLeastDays){
                    if($dates[$d]['out']==1){
                        $atLeastDays=$k;
                        !empty($type) && $type == 'shipping' ? $availableShippingDate = $d : '';
                        break;
                    }
                }
            }
            foreach($everyDays as $k=>$d){
                if($k>$atLeastDays){
                    if($dates[$d]['pickup']==1){
                        $atLeastDays=$k;
                        $availableDate = $d;
                        break;
                    }
                }
            }
        }else{ //備貨日為$n天
            for($n = 4; $n <= $inputStockDays ; $n++){
                $atLeastDays = 0; //跑迴圈要歸零,不然會被累加
                foreach($everyDays as $k=>$d){
                    if($k>$atLeastDays){
                        if($dates[$d]['call']==1){
                            $atLeastDays=$k;
                            break;
                        }
                    }
                }
                $needLogisticsDay=$n-3;
                $checkLogisticsDay=0;
                foreach($everyDays as $k=>$d){
                    if($k>$atLeastDays){
                        if($dates[$d]['logistics']==1){
                            $checkLogisticsDay+=1;
                            $atLeastDays=$k;
                            if($needLogisticsDay==$checkLogisticsDay){
                                break;
                            }
                        }
                    }
                }
                foreach($everyDays as $k=>$d){
                    if($k>$atLeastDays){
                        if($dates[$d]['out']==1){
                            $atLeastDays=$k;
                            !empty($type) && $type == 'shipping' ? $availableShippingDate = $d : '';
                            break;
                        }
                    }
                }
                foreach($everyDays as $k=>$d){
                    if($k>$atLeastDays){
                        if($dates[$d]['pickup']==1){
                            $atLeastDays=$k;
                            $availableDate = $d;
                            break;
                        }
                    }
                }
            }
        }
        if(!empty($type) && $type == 'shipping'){
            return $availableShippingDate;
        }
        return $availableDate;
    }
}
