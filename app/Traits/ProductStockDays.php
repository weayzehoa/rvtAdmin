<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use App\Models\ReceiverBaseSetting as ReceiverBaseSettingDB;

trait ProductStockDays
{
    public function productStockDays($orderDate,$pickupDate)
    {
        $stockDays = 0;
        $startDate = date('Y-m-d',strtotime($orderDate));
        $endDate = date('Y-m-d',strtotime($pickupDate));
        $startHour = intval(date("G",strtotime($orderDate)));//取得開始的小時 因為快閃12點前後分水嶺
        $daysBetweenPickupDateToStartDate = ((strtotime($endDate)-strtotime($startDate)) / 86400);

        if($daysBetweenPickupDateToStartDate>0){ //避免輸入的日期相反

            //找出下單日到提貨日的設定資料
            $tmps = ReceiverBaseSettingDB::whereBetween('select_date',[$startDate,$endDate])->orderBy('select_date','asc')->get();
            $tmps = $tmps->groupBy('select_date');

            foreach($tmps as $d => $tmp){
                $everyDays[] = $d;
                foreach($tmp as $t){
                    $dates[$d][$t->type] = $t->is_ok;
                }
            }

            $tomorrow = date("Y-m-d", strtotime($startDate) + 1 * 86400);
            $theDayAfterTomorrow = date("Y-m-d", strtotime($startDate) + 2 * 86400);
            $twoDaysAfterTomorrow = date("Y-m-d", strtotime($startDate) + 3 * 86400);
            $max_n=$daysBetweenPickupDateToStartDate;

            for($i=1;$i<=1;$i++){ //用來給裡面的檢驗跳出用實際上只會跑一次
                if($daysBetweenPickupDateToStartDate >= 4){ //備貨日4天以上
                    //這迴圈必須符合有叫貨=>物流*($max_n-2)=>出貨日(檢驗)=>提貨
                    for($n=$max_n;$n>3;$n--){
                        $atLeastDays=0;
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
                        $checkOutDay = 0;
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['out']==1){
                                    $checkOutDay = 1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        if($checkOutDay == 0){ //檢查是否有out, 沒有則返回前一個迴圈
                            $n = $n-1;
                            continue;
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['pickup']==1){
                                    $atLeastDays=$k;
                                    $stockDays=$n;
                                    break 3;
                                }
                            }
                        }
                    }
                    //這迴圈須符合有叫貨=>出貨=>提貨
                    for($n=$max_n;$n>3;$n--){
                        $ifStockNeed3Days=0;
                        $atLeastDays=0;
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['call']==1){
                                    $ifStockNeed3Days+=1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['out']==1){
                                    $ifStockNeed3Days+=1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['pickup']==1){
                                    $ifStockNeed3Days+=1;
                                    $atLeastDays=$k;
                                    if($ifStockNeed3Days==3){
                                        $stockDays=3;
                                        break 3;
                                    }
                                }
                            }
                        }
                    }
                    //這迴圈檢查備貨2日的須符合有出貨=>提貨
                    for($n=$max_n;$n>3;$n--){
                        $ifStockNeed2Days=0;
                        $atLeastDays=0;
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['out']==1){
                                    $ifStockNeed2Days+=1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['pickup']==1){
                                    $ifStockNeed2Days+=1;
                                    $atLeastDays=$k;
                                    if($ifStockNeed2Days==2){
                                        $stockDays=2;
                                        $daysBetweenPickupDateToStartDate=3;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                    //這迴圈檢查備貨1日與0日的須符合有出貨=>提貨且檢查下單日的時間是否小於11點
                    for($n=$max_n;$n>3;$n--){
                        $ifStockNeed1Day=0;
                        $atLeastDays=-1;
                        if($startHour<11){
                            $atLeastDays=0;
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['out']==1){
                                    $ifStockNeed1Day+=1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['pickup']==1){
                                    $ifStockNeed1Day+=1;
                                    $atLeastDays=$k;
                                    if($ifStockNeed1Day==1){
                                        $stockDays=1;
                                        $daysBetweenPickupDateToStartDate=3;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
                if($stockDays == 0 || $daysBetweenPickupDateToStartDate==3){//備貨日為3天
                    if(isset($dates[$tomorrow]['call'])==1 && isset($dates[$theDayAfterTomorrow]['out'])==1 && isset($dates[$twoDaysAfterTomorrow]['pickup'])==1){
                        $stockDays=3;
                        break;
                    }elseif($stockDays==2){
                        break;
                    }elseif(isset($dates[$tomorrow]['out'])==1 && isset($dates[$twoDaysAfterTomorrow]['pickup'])==1){
                        $stockDays=2;
                        break;
                    }elseif(isset($dates[$theDayAfterTomorrow]['out'])==1 && isset($dates[$twoDaysAfterTomorrow]['pickup'])==1){
                        $stockDays=2;
                        break;
                    }elseif($startHour<11){
                        if(isset($dates[$tomorrow]['out'])==1 && isset($dates[$theDayAfterTomorrow]['pickup'])==1){
                            $stockDays=1;
                        }elseif(isset($dates[$twoDaysAfterTomorrow]['out'])==1 && isset($dates[$twoDaysAfterTomorrow]['pickup'])==1){
                            $stockDays=1;
                        }
                        $daysBetweenPickupDateToStartDate-=1;
                    }else{
                        $daysBetweenPickupDateToStartDate-=1;
                    }
                }
                if($stockDays == 0 || $daysBetweenPickupDateToStartDate==2){//備貨日為2天
                    if(isset($dates[$tomorrow]['out'])==1 && isset($dates[$theDayAfterTomorrow]['pickup'])==1){
                        $stockDays=2;
                        break;
                    }elseif($startHour<11 && isset($dates[$tomorrow]['out'])==1 && isset($dates[$theDayAfterTomorrow]['pickup'])==1){//符合第二天當天出貨
                        $stockDays=1;
                        break;
                    }else{
                        $daysBetweenPickupDateToStartDate-=1;
                    }
                }
                if($stockDays == 0 || $daysBetweenPickupDateToStartDate==1){//備貨日為1天
                    if($startHour<11 && isset($dates[$startDate]['out'])==1 && isset($dates[$tomorrow]['pickup'])==1){//符合當天出貨
                        $stockDays=1;
                        break;
                    }else{
                        $stockDays=0;
                        break;
                    }
                }
            }
        }
        return $stockDays;
    }
}
