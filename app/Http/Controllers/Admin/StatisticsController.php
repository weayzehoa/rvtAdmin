<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryOrderItem as OrderItemDB;
use App\Models\iCarrySource as SourceDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryDigiwinPayment as DigiwinPaymentDB;
use App\Models\iCarryShippingFee as ShippingFeeDB;
use App\Models\GateSell as SellDB;
use App\Models\GateSellReturn as SellReturnDB;
use Carbon\Carbon;
use DB;

class StatisticsController extends Controller
{
    public function __construct()
    {
        // 先經過 middleware 檢查
        $this->middleware('auth:admin');
        $this->iCarry = ['002','003','004','005','006','007','008','009','037','063','073','086'];
    }

    public function userMonthlyTotal()
    {
        $menuCode = 'M7S1';
        $appends = [];
        $compact = [];

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }
        $users = UserDB::where('status',1);

        if(isset($year)){
            $users = $users->whereBetween('create_time',[$year.'-01-01 00:00:00',$year.'-12-31 23:59:59']);
        }
        $users = $users->select([
            DB::raw('DATE_FORMAT(create_time,"%Y-%m") as yyyymm'),
            DB::raw('COUNT(id) as total'),
        ]);
        if (!isset($list)) {
            $list = 15;
            $compact = array_merge($compact, ['list']);
        }
        $users = $users->groupBy('yyyymm')->orderBy('create_time', 'desc')->paginate($list);
        $totalUsers = UserDB::where('status',1)->count();
        $years = UserDB::where('status',1)->selectRaw('DATE_FORMAT(create_time,"%Y") as yyyy')->groupBy('yyyy')->orderBy('create_time', 'desc')->get();
        $yearTotal = 0;
        if(isset($year)){
            foreach($users as $user){
                $yearTotal += $user->total;
            }
        }
        $compact = array_merge($compact,['menuCode', 'users', 'totalUsers', 'years', 'yearTotal']);
        return view('admin.statistics.user_monthly_total',compact($compact));
    }

    public function orderDailyTotalOne()
    {
        $menuCode = 'M7S10';
        $compact = [];
        $startYear = 2015;
        $finalYear = date('Y');
        $yyyymm = date('Y').'-'.date('m');
        env('APP_ENV') != 'production' ? $yyyymm = '2023-11' : ''; //測試用
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            $compact = array_merge($compact, [$key]);
        }
        $firstDayofMonth = Carbon::parse($yyyymm.'-10')->firstOfMonth()->toDateString(); //本月第一天
        $lastDayofMonth = Carbon::parse($yyyymm.'-10')->endOfMonth()->toDateString(); //本月最後一天
        $total = ['text' => $yyyymm.' 月份統計', 'monthly_order' => 0, 'total_money' => 0, 'total_shipping_tax' => 0, 'not_ok_total' => 0, 'avg' => 0, 'user_total' => 0,'distinct_buyer_total' => 0];
        $users = UserDB::where('status',1)->whereBetween('create_time',[$firstDayofMonth.' 00:00:00',$lastDayofMonth.' 23:59:59']);
        $users = $users->select([
            DB::raw("DATE_FORMAT(create_time,'%Y-%m') as yyyymm"),
            DB::raw("DATE_FORMAT(create_time,'%Y-%m-%d') as yyyymmdd"),
            DB::raw("COUNT(id) as user_total"),
        ])->groupBy('yyyymmdd')->orderBy('yyyymmdd','desc')->get();
        $noPayOrders = OrderDB::where('status',0)->whereBetween('create_time',[$firstDayofMonth.' 00:00:00',$lastDayofMonth.' 23:59:59']);
        isset($source) ? (!empty($source) ? ($source == 'iCarryWeb' ? $noPayOrders = $noPayOrders->where('create_type','web') : $noPayOrders = $noPayOrders->where('digiwin_payment_id',$source)) : '') : '';
        $noPayOrders = $noPayOrders->select([
            DB::raw("DATE_FORMAT(create_time,'%Y-%m') as yyyymm"),
            DB::raw("DATE_FORMAT(create_time,'%Y-%m-%d') as yyyymmdd"),
            DB::raw("COUNT(id) as not_ok_total"),
        ])->groupBy('yyyymmdd')->orderBy('yyyymmdd','desc')->get();
        $orders = OrderDB::where('status','>',0)->whereBetween('pay_time',[$firstDayofMonth.' 00:00:00',$lastDayofMonth.' 23:59:59']);
        isset($source) ? (!empty($source) ? ($source == 'iCarryWeb' ? $orders = $orders->where('create_type','web') : $orders = $orders->where('digiwin_payment_id',$source)) : '') : '';
        $orders = $orders->select([
            DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
            DB::raw("DATE_FORMAT(pay_time,'%Y-%m-%d') as yyyymmdd"),
            DB::raw("COUNT(id) as total_order"),
            DB::raw("SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as total_money"),
            DB::raw("SUM(shipping_fee+parcel_tax) as total_shipping_tax"),
            DB::raw("truncate(SUM(amount+shipping_fee+parcel_tax-discount-spend_point) / COUNT(id),2) as avg"),
            DB::raw("COUNT(distinct(user_id)) as distinct_buyer_total"),
        ])->groupBy('yyyymmdd')->orderBy('yyyymmdd','desc')->get();

        //找出本月第一天到今天
        for($i=str_replace('-','',$firstDayofMonth); $i<=str_replace('-','',$lastDayofMonth);$i++){
            $allDates[] = substr($i,0,4).'-'.substr($i,4,2).'-'.substr($i,6,2);
        }
        //找出缺少訂單資料的日期
        $orderDates = $orders->pluck('yyyymmdd')->all();
        $diffDates = array_diff($allDates,$orderDates);
        sort($diffDates);

        for($i=0;$i<count($allDates);$i++){
            foreach ($orders as $order) {
                if($order->yyyymmdd == $allDates[$i]){
                    $orderData[$i]['yyyymmdd'] = $order->yyyymmdd;
                    $orderData[$i]['total_order'] = $order->total_order;
                    $orderData[$i]['total_money'] = $order->total_money;
                    $orderData[$i]['total_shipping_tax'] = $order->total_shipping_tax;
                    $orderData[$i]['avg'] = $order->avg;
                    $orderData[$i]['not_ok_total'] = 0;
                    $orderData[$i]['distinct_buyer_total'] = $order->distinct_buyer_total;
                    $orderData[$i]['user_total'] = 0;
                    foreach ($users as $user) {
                        if($user->yyyymmdd == $order->yyyymmdd){
                            $orderData[$i]['user_total'] = $user->user_total;
                            break;
                        }
                    }
                    foreach ($noPayOrders as $noPayOrder) {
                        if($noPayOrder->yyyymmdd == $order->yyyymmdd){
                            $orderData[$i]['not_ok_total'] = $noPayOrder->not_ok_total;
                            break;
                        }
                    }
                    break;
                }
            }

            //找不到當日訂單資料時
            if(!isset($orderData[$i])){
                $orderData[$i]['yyyymmdd'] = $allDates[$i];
                $orderData[$i]['user_total'] = $orderData[$i]['total_order'] = $orderData[$i]['total_money'] = $orderData[$i]['total_shipping_tax'] = $orderData[$i]['avg'] = $orderData[$i]['distinct_buyer_total'] = $orderData[$i]['not_ok_total'] = 0;
                foreach ($users as $user) {
                    if($user->yyyymmdd == $allDates[$i]){
                        $orderData[$i]['user_total'] = $user->user_total;
                        break;
                    }
                }
            }
        }
        for($i=0;$i<count($orderData);$i++){
            $total['monthly_order'] += $orderData[$i]['total_order'];
            $total['total_money'] += $orderData[$i]['total_money'];
            $total['total_shipping_tax'] += $orderData[$i]['total_shipping_tax'];
            $total['not_ok_total'] += $orderData[$i]['not_ok_total'];
            $total['user_total'] += $orderData[$i]['user_total'];
            $total['distinct_buyer_total'] += $orderData[$i]['distinct_buyer_total'];
        }

        if($total['monthly_order'] > 0 && $total['monthly_order'] > 0){
            $total['avg'] = round($total['total_money'] / $total['monthly_order'],2);
        }

        $sources = DigiwinPaymentDB::where(function($query){
            $query->where('customer_no','<=','999')
            ->orWhereIn('customer_no',['065001','065002','065003','065004','065005','065006','065007','065008','065009','065010','065011','AC0001','AC000101','AC000102','AC000103']);
        })->select([
            'customer_no as source',
            'customer_name as name'
        ])->orderBy('source','asc')->get();
        $compact = array_merge($compact,['menuCode', 'orders', 'total', 'sources', 'startYear', 'finalYear','yyyymm','orderData']);
        return view('admin.statistics.order_daily_total_one',compact($compact));
    }

    public function orderMonthlyTotalOne()
    {
        $menuCode = 'M7S11';
        $compact = [];
        $total = [];
        $startYear = 2016;
        $finalYear = date('Y');
        for($y = $startYear; $y<= $finalYear; $y++){
            for($i=1; $i<=12; $i++){
                $i <= 9 ? $m = '0'.$i : $m = $i;
                $tmp = $y.'-'.$m;
                $yyyymm[] = $tmp;
                if($tmp == date('Y').'-'.date('m')){
                    break 2;
                }
            }
        }
        rsort($yyyymm); //反排
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            $compact = array_merge($compact, [$key]);
        }
        $orders = OrderDB::where('status','>',0)->whereBetween('pay_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
        isset($source) ? (!empty($source) ? ($source == 'iCarryWeb' ? $orders = $orders->where('create_type','web') : $orders = $orders->where('digiwin_payment_id',$source)) : '') : '';
        $orders = $orders->select([
            DB::raw("DATE_FORMAT(pay_time,'%Y') as year"),
            DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
            DB::raw("COUNT(id) as pay_orders"),
            DB::raw("SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as pay_money_total"),
            DB::raw("SUM(shipping_fee+parcel_tax) as ffeight_tariff_total"),
            DB::raw("truncate(SUM(amount+shipping_fee+parcel_tax-discount-spend_point) / COUNT(id),2) as avg_orders_money"),
            DB::raw("COUNT(distinct(user_id)) as no_repeat_consumption"),
        ])->groupBy('yyyymm')->orderBy('yyyymm', 'desc')->get()->groupBy('year')->all();
        $noPayOrders = OrderDB::where('status',0)->whereBetween('create_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
        isset($source) ? (!empty($source) ? ($source == 'iCarryWeb' ? $noPayOrders = $noPayOrders->where('create_type','web') : $noPayOrders = $noPayOrders->where('digiwin_payment_id',$source)) : '' ) : '';
        $noPayOrders = $noPayOrders->select([
            DB::raw("DATE_FORMAT(create_time,'%Y') as year"),
            DB::raw("DATE_FORMAT(create_time,'%Y-%m') as yyyymm"),
            DB::raw("COUNT(id) as not_ok_total"),
        ])->groupBy('yyyymm')->orderBy('yyyymm','desc')->get();
        $users = UserDB::where('status',1)->whereBetween('create_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
        $users = $users->select([
            DB::raw("DATE_FORMAT(create_time,'%Y') as year"),
            DB::raw("DATE_FORMAT(create_time,'%Y-%m') as yyyymm"),
            DB::raw("COUNT(id) as user_total"),
        ])->groupBy('yyyymm')->orderBy('yyyymm','desc')->get();
        foreach ($orders as $year => $values) {
            $pay_orders = $pay_money_total = $avg_orders_money = $ffeight_tariff_total = $no_pay_orders = $registered_num = $no_repeat_consumption = 0;
            foreach ($values as $order) {
                foreach ($users as $user) {
                    $order->registered_num = 0;
                    if($user->yyyymm == $order->yyyymm){
                        $order->registered_num = $user->user_total;
                        break;
                    }
                }
                foreach ($noPayOrders as $noPayOrder) {
                    $order->no_pay_orders = 0;
                    if($noPayOrder->yyyymm == $order->yyyymm && $noPayOrder->source == $order->source){
                        $order->no_pay_orders = $noPayOrder->not_ok_total;
                        break;
                    }
                }
                $pay_orders += $order->pay_orders;
                $pay_money_total += $order->pay_money_total;
                $ffeight_tariff_total += $order->ffeight_tariff_total;
                $no_pay_orders += $order->no_pay_orders;
                $registered_num += $order->registered_num;
                $order->create_type == 'web' ? $no_repeat_consumption += $order->no_repeat_consumption : $no_repeat_consumption += 0;
            }
            if($pay_orders > 0 && $pay_money_total > 0){
                $avg_orders_money = round($pay_money_total / $pay_orders,2);
            }
            $total[$year] = ['text' => $year.' 年統計', 'pay_orders' => $pay_orders, 'pay_money_total' => $pay_money_total, 'avg_orders_money' => $avg_orders_money, 'ffeight_tariff_total' => $ffeight_tariff_total, 'no_pay_orders' => $no_pay_orders, 'registered_num' => $registered_num, 'no_repeat_consumption' => $no_repeat_consumption];
        }
        $sources = DigiwinPaymentDB::where(function($query){
            $query->where('customer_no','<=','999')
            ->orWhereIn('customer_no',['065001','065002','065003','065004','065005','065006','065007','065008','065009','065010','065011','AC0001','AC000101','AC000102','AC000103']);
        })->select([
            'customer_no as source',
            'customer_name as name'
        ])->orderBy('source','asc')->get();
        $compact = array_merge($compact,['menuCode', 'orders', 'sources', 'total']);
        return view('admin.statistics.order_monthly_total_one',compact($compact));
    }

    public function orderDailyTotal()
    {
        $menuCode = 'M7S2';
        $compact = [];
        $yyyymm = date('Y').'-'.date('m');
        env('APP_ENV') != 'production' ? $yyyymm = '2019-08' : ''; //測試用

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            $compact = array_merge($compact, [$key]);
        }
        $firstDayofMonth = Carbon::parse($yyyymm.'-10')->firstOfMonth()->toDateString(); //本月第一天
        $lastDayofMonth = Carbon::parse($yyyymm.'-10')->endOfMonth()->toDateString(); //本月最後一天
        //當月天數及當月所有日期
        $countDay = date("t",strtotime($yyyymm));
        for($i=1;$i<=$countDay;$i++){
            if($i<=9){
                $date[] = $yyyymm.'-0'.$i;
            }else{
                $date[] = $yyyymm.'-'.$i;
            }
        }
        rsort($date); //反排
        if(!empty($source)){
            $source = explode(',',$source);
            $chkWeb = 0;
            for($i=0;$i<count($source);$i++){
                if($source[$i] == '001'){
                    $chkWeb = 1;
                    break;
                }
            }
            $tmps = OrderDB::where('status','>',0)->whereBetween('pay_time',[$firstDayofMonth.' 00:00:00',$lastDayofMonth.' 23:59:59']);
            $chkWeb == 1 ? $tmps = $tmps->where(function($query)use($source){$query->whereIn('digiwin_payment_id',$source)->orWhere('create_type','web');}) : $tmps = $tmps->whereIn('digiwin_payment_id',$source);
            $tmps = $tmps->select([
                DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
                DB::raw("DATE_FORMAT(pay_time,'%Y-%m-%d') as yyyymmdd"),
                DB::raw("COUNT(id) as total_order"),
                DB::raw("SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as total_money"),
                'create_type',
                'digiwin_payment_id as source'
            ])->groupBy('yyyymmdd','source')->orderBy('yyyymmdd','desc')->get()->groupBy('source')->all();
            for ($d=0; $d<count($date); $d++) {
                for($i=0;$i<count($source);$i++){
                    $order = $money = 0;
                    if($source[$i] == '001'){
                        foreach($tmps as $sourc => $value){
                            foreach($value as $v){
                                if($v->yyyymmdd == $date[$d] && $v->create_type == 'web'){
                                    $order += $v->total_order;
                                    $money += $v->total_money;
                                }
                            }
                        }
                        $orders[$date[$d]][$source[$i]]['orders'] = $order;
                        $orders[$date[$d]][$source[$i]]['money'] = $money;
                        $totals[$source[$i]]['orders'][$d] = $order;
                        $totals[$source[$i]]['money'][$d] = $money;
                    }else{
                        foreach($tmps as $sourc => $value){
                            foreach($value as $v){
                                if($v->yyyymmdd == $date[$d] && $v->source == $source[$i]){
                                    $order = $v->total_order;
                                    $money = $v->total_money;
                                    break 2;
                                }
                            }
                        }
                        $orders[$date[$d]][$source[$i]]['orders'] = $order;
                        $orders[$date[$d]][$source[$i]]['money'] = $money;
                        $totals[$source[$i]]['orders'][$d] = $order;
                        $totals[$source[$i]]['money'][$d] = $money;
                    }
               }
               ksort($orders[$date[$d]]);
            }
            ksort($totals);
            //計算資料
            for ($i=0;$i<count($source);$i++) {
                $total[$i]['orders'] = collect($totals[$source[$i]]['orders'])->sum();
                $total[$i]['money'] = collect($totals[$source[$i]]['money'])->sum();
            }
        }else{
            $total = ['text' => $yyyymm.' 月份統計', 'monthly_order' => 0, 'total_money' => 0, 'total_shipping_tax' => 0, 'not_ok_total' => 0, 'avg' => 0, 'user_total' => 0,'distinct_buyer_total' => 0];
            $users = UserDB::where('status',1)->whereBetween('create_time',[$firstDayofMonth.' 00:00:00',$lastDayofMonth.' 23:59:59']);
            $users = $users->select([
                DB::raw("DATE_FORMAT(create_time,'%Y-%m') as yyyymm"),
                DB::raw("DATE_FORMAT(create_time,'%Y-%m-%d') as yyyymmdd"),
                DB::raw("COUNT(id) as user_total"),
            ])->groupBy('yyyymmdd')->orderBy('yyyymmdd','desc')->get();
            $noPayOrders = OrderDB::where('status',0)->whereBetween('create_time',[$firstDayofMonth.' 00:00:00',$lastDayofMonth.' 23:59:59']);
            $noPayOrders = $noPayOrders->select([
                DB::raw("DATE_FORMAT(create_time,'%Y-%m') as yyyymm"),
                DB::raw("DATE_FORMAT(create_time,'%Y-%m-%d') as yyyymmdd"),
                DB::raw("COUNT(id) as not_ok_total"),
            ])->groupBy('yyyymmdd')->orderBy('yyyymmdd','desc')->get();
            $orders = OrderDB::where('status','>',0)->whereBetween('pay_time',[$firstDayofMonth.' 00:00:00',$lastDayofMonth.' 23:59:59']);
            $orders = $orders->select([
                DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
                DB::raw("DATE_FORMAT(pay_time,'%Y-%m-%d') as yyyymmdd"),
                DB::raw("COUNT(id) as total_order"),
                DB::raw("SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as total_money"),
                DB::raw("SUM(shipping_fee+parcel_tax) as total_shipping_tax"),
                DB::raw("truncate(SUM(amount+shipping_fee+parcel_tax-discount-spend_point) / COUNT(id),2) as avg"),
                DB::raw("COUNT(distinct(user_id)) as distinct_buyer_total"),
            ])->groupBy('yyyymmdd')->orderBy('yyyymmdd','desc')->get();

            foreach ($orders as $order) {
                foreach ($users as $user) {
                    $order->user_total = 0;
                    if($user->yyyymmdd == $order->yyyymmdd){
                        $order->user_total = $user->user_total;
                        break;
                    }
                }
                foreach ($noPayOrders as $noPayOrder) {
                    $order->not_ok_total = 0;
                    if($noPayOrder->yyyymmdd == $order->yyyymmdd){
                        $order->not_ok_total = $noPayOrder->not_ok_total;
                        break;
                    }
                }
                $total['monthly_order'] += $order->total_order;
                $total['total_money'] += $order->total_money;
                $total['total_shipping_tax'] += $order->total_shipping_tax;
                $total['not_ok_total'] += $order->not_ok_total;
                $total['user_total'] += $order->user_total;
                $total['distinct_buyer_total'] += $order->distinct_buyer_total;
            }
            if($total['monthly_order'] > 0 && $total['monthly_order'] > 0){
                $total['avg'] = round($total['total_money'] / $total['monthly_order'],2);
            }
        }
        $startYear = 2015;
        $finalYear = date('Y');
        $sources = DigiwinPaymentDB::whereNotIn('customer_no',$this->iCarry)->where(function($query){
            $query->where('customer_no','<=','999')
            ->orWhereIn('customer_no',['065001','065002','065003','065004','065005','065006','065007','065008','065009','065010','065011']);
        })->select([
            'customer_no as source',
            'customer_name as name'
        ])->orderBy('source','asc')->get();
        foreach($sources as $s){
            if($s->source == '001'){
                $s->name = 'iCarry Web';
            }
        }
        $compact = array_merge($compact,['menuCode', 'orders', 'total', 'sources', 'startYear', 'finalYear','yyyymm']);
        return view('admin.statistics.order_daily_total',compact($compact));
    }

    public function orderMonthlyTotal()
    {
        $menuCode = 'M7S3';
        $compact = [];
        $total = [];
        $startYear = 2016;
        $finalYear = date('Y');
        for($y = $startYear; $y<= $finalYear; $y++){
            for($i=1; $i<=12; $i++){
                $i <= 9 ? $m = '0'.$i : $m = $i;
                $tmp = $y.'-'.$m;
                $yyyymm[] = $tmp;
                if($tmp == date('Y').'-'.date('m')){
                    break 2;
                }
            }
        }
        rsort($yyyymm); //反排
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            $compact = array_merge($compact, [$key]);
        }
        if (!empty($source)) {
            $source = explode(',', $source);
            $chkWeb = 0;
            for($i=0;$i<count($source);$i++){
                if($source[$i] == '001'){
                    $chkWeb = 1;
                    break;
                }
            }
            $tmps = OrderDB::where('status','>',0)->whereBetween('pay_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $chkWeb == 1 ? $tmps = $tmps->where(function($query)use($source){$query->whereIn('digiwin_payment_id',$source)->orWhere('create_type','web');}) : $tmps = $tmps->whereIn('digiwin_payment_id',$source);
            $tmps = $tmps->select([
                DB::raw("DATE_FORMAT(pay_time,'%Y') as year"),
                DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
                DB::raw("COUNT(id) as pay_orders"),
                DB::raw("SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as pay_money_total"),
                'digiwin_payment_id as source',
                'create_type',
            ])->groupBy('yyyymm','source')->orderBy('yyyymm', 'desc')->get();
            for($y = $startYear; $y<= $finalYear; $y++){
                for($m = 1; $m <=12; $m++){
                    $m <= 9 ? $m = '0'.$m : '';
                    $ym = $y.'-'.$m;
                    if($ym > date('Y').'-'.date('m')){
                        break 2;
                    }
                    for($i=0;$i<count($source);$i++){
                        $order = $money = 0;
                        if($source[$i] == '001') {
                            foreach ($tmps as $tmp) {
                                if($tmp->yyyymm == $ym && $tmp->create_type == 'web'){
                                    $order += $tmp->pay_orders;
                                    $money += $tmp->pay_money_total;
                                }
                            }
                            $orders[$y][$ym]['001']['orders'] = $order;
                            $orders[$y][$ym]['001']['money'] = $money;
                            $total['001'][$y]['orders'][] = $order;
                            $total['001'][$y]['money'][] = $money;
                        }else{
                            foreach ($tmps as $tmp) {
                                if($tmp->yyyymm == $ym && $tmp->source == $source[$i]){
                                    $order = $tmp->pay_orders;
                                    $money = $tmp->pay_money_total;
                                    break;
                                }
                            }
                            $source[$i] == '' ? $s = 'all' : $s = $source[$i];
                            $orders[$y][$ym][$s]['orders'] = $order;
                            $orders[$y][$ym][$s]['money'] = $money;
                            $total[$s][$y]['orders'][] = $order;
                            $total[$s][$y]['money'][] = $money;
                        }
                    }
                krsort($orders[$y]);
                }
            }
            krsort($orders);
            //將all還原並計算資料
            for ($i=0;$i<count($source);$i++) {
                $source[$i] == '' ? $source[$i] = 'all' : '';
                for($y = $startYear; $y<= $finalYear; $y++){
                    $total[$i][$y]['orders'] = collect($total[$source[$i]][$y]['orders'])->sum();
                    $total[$i][$y]['money'] = collect($total[$source[$i]][$y]['money'])->sum();
                }
                unset($total[$source[$i]]);
            }
        }else{
            $orders = OrderDB::where('status','>',0)->whereBetween('pay_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $orders = $orders->select([
                DB::raw("DATE_FORMAT(pay_time,'%Y') as year"),
                DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
                DB::raw("COUNT(id) as pay_orders"),
                DB::raw("SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as pay_money_total"),
                DB::raw("SUM(shipping_fee+parcel_tax) as ffeight_tariff_total"),
                DB::raw("truncate(SUM(amount+shipping_fee+parcel_tax-discount-spend_point) / COUNT(id),2) as avg_orders_money"),
                DB::raw("COUNT(distinct(user_id)) as no_repeat_consumption"),
            ])->groupBy('yyyymm')->orderBy('yyyymm', 'desc')->get()->groupBy('year')->all();
            $noPayOrders = OrderDB::where('status',0)->whereBetween('create_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $noPayOrders = $noPayOrders->select([
                DB::raw("DATE_FORMAT(create_time,'%Y') as year"),
                DB::raw("DATE_FORMAT(create_time,'%Y-%m') as yyyymm"),
                DB::raw("COUNT(id) as not_ok_total"),
            ])->groupBy('yyyymm')->orderBy('yyyymm','desc')->get();
            $users = UserDB::where('status',1)->whereBetween('create_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $users = $users->select([
                DB::raw("DATE_FORMAT(create_time,'%Y') as year"),
                DB::raw("DATE_FORMAT(create_time,'%Y-%m') as yyyymm"),
                DB::raw("COUNT(id) as user_total"),
            ])->groupBy('yyyymm')->orderBy('yyyymm','desc')->get();
            foreach ($orders as $year => $values) {
                $pay_orders = $pay_money_total = $avg_orders_money = $ffeight_tariff_total = $no_pay_orders = $registered_num = $no_repeat_consumption = 0;
                foreach ($values as $order) {
                    foreach ($users as $user) {
                        $userTotal = 0;
                        if($user->yyyymm == $order->yyyymm){
                            $order->registered_num = $user->user_total;
                            break;
                        }
                    }
                    foreach ($noPayOrders as $noPayOrder) {
                        $notOkTotal = 0;
                        if($noPayOrder->yyyymm == $order->yyyymm && $noPayOrder->source == $order->source){
                            $order->no_pay_orders = $noPayOrder->not_ok_total;
                            break;
                        }
                    }
                    $pay_orders += $order->pay_orders;
                    $pay_money_total += $order->pay_money_total;
                    $ffeight_tariff_total += $order->ffeight_tariff_total;
                    $no_pay_orders += $order->no_pay_orders;
                    $registered_num += $order->registered_num;
                    $no_repeat_consumption += $order->no_repeat_consumption;
                }
                if($pay_orders > 0 && $pay_money_total > 0){
                    $avg_orders_money = round($pay_money_total / $pay_orders,2);
                }
                $total[$year] = ['text' => $year.' 年統計', 'pay_orders' => $pay_orders, 'pay_money_total' => $pay_money_total, 'avg_orders_money' => $avg_orders_money, 'ffeight_tariff_total' => $ffeight_tariff_total, 'no_pay_orders' => $no_pay_orders, 'registered_num' => $registered_num, 'no_repeat_consumption' => $no_repeat_consumption];
            }
        }
        $sources = DigiwinPaymentDB::whereNotIn('customer_no',$this->iCarry)->where(function($query){
            $query->where('customer_no','<=','999')
            ->orWhereIn('customer_no',['065001','065002','065003','065004','065005','065006','065007','065008','065009','065010','065011']);
        })->select([
            'customer_no as source',
            'customer_name as name'
        ])->orderBy('source','asc')->get();
        foreach($sources as $s){
            if($s->source == '001'){
                $s->name = 'iCarry Web';
            }
        }
        $compact = array_merge($compact,['menuCode', 'orders', 'sources', 'total']);
        return view('admin.statistics.order_monthly_total',compact($compact));
    }

    public function orderMonthlySellTotal()
    {
        $menuCode = 'M7S9';
        $compact = $total = [];
        $startYear = 2023;
        $sellTable = env('DB_ERPGATE').'.'.(new SellDB)->getTable();
        $sellReturnTable = env('DB_ERPGATE').'.'.(new SellReturnDB)->getTable();
        $orderTable = env('DB_ICARRY').'.'.(new OrderDB)->getTable();
        $today = date('Y-m-d');
        $finalYear = date('Y');
        for($y = $startYear; $y<= $finalYear; $y++){
            for($i=1; $i<=12; $i++){
                $i <= 9 ? $m = '0'.$i : $m = $i;
                $tmp = $y.'-'.$m;
                $yyyymm[] = $tmp;
                if($tmp == date('Y').'-'.date('m')){
                    break 2;
                }
            }
        }
        rsort($yyyymm); //反排
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            $compact = array_merge($compact, [$key]);
        }

        if (!empty($source)) {
            $source = explode(',', $source);
            $chkWeb = 0;
            for($i=0;$i<count($source);$i++){
                if($source[$i] == '001'){
                    $chkWeb = 1;
                    break;
                }
            }
            // $tmps = OrderDB::where('status','>=',3)->whereBetween('pay_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            // $tmps = $tmps->whereIn('create_type',$source);
            // $tmps = $tmps->select([
            //     DB::raw("DATE_FORMAT(pay_time,'%Y') as year"),
            //     DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
            //     DB::raw("COUNT(id) as pay_orders"),
            //     DB::raw("SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as pay_money_total"),
            //     'create_type as source'
            // ])->groupBy('yyyymm','source')->orderBy('yyyymm', 'desc')->get();

            $tmps = SellDB::join($orderTable,$orderTable.'.id',$sellTable.'.order_id')
            ->where($sellTable.'.is_del',0)
            ->whereBetween($sellTable.'.sell_date',['2023-01-01',$today]);

            $chkWeb == 1 ? $tmps = $tmps->where(function($query)use($source,$orderTable){$query->whereIn($orderTable.'.digiwin_payment_id',$source)->orWhere($orderTable.'.create_type','web');}) : $tmps = $tmps->whereIn($orderTable.'.digiwin_payment_id',$source);

            $tmps = $tmps->select([
                DB::raw("DATE_FORMAT($sellTable.sell_date,'%Y') as year"),
                DB::raw("DATE_FORMAT($sellTable.sell_date,'%Y-%m') as yyyymm"),
                DB::raw("SUM($sellTable.amount) as pay_money_total"),
                $orderTable.'.digiwin_payment_id as source',
                $orderTable.'.create_type',
            ])->groupBy('yyyymm','source')->orderBy('yyyymm', 'desc')->get();

            $sellOrderIds = SellDB::where('is_del',0)->whereBetween('sell_date',['2023-01-01',$today])->select('order_id')->groupBy('order_id');
            $iCarryOrders = OrderDB::whereIn('id',$sellOrderIds);
            $iCarryOrders = $iCarryOrders->select([
                DB::raw("DATE_FORMAT((Select sell_date from $sellTable where $sellTable.order_id = $orderTable.id order by sell_date desc limit 1)  ,'%Y') as year"),
                DB::raw("DATE_FORMAT((Select sell_date from $sellTable where $sellTable.order_id = $orderTable.id order by sell_date desc limit 1) ,'%Y-%m') as yyyymm"),
                DB::raw("COUNT(id) as pay_orders"),
                'digiwin_payment_id as source',
                'create_type',
            ])->groupBy('yyyymm','source')->orderBy('yyyymm', 'desc')->get();

            //銷退折讓金額
            $sellReturnOrderIds = SellDB::where('is_del',0)->whereBetween('sell_date',['2023-01-01',date('Y-m-d')])->select('order_id')->groupBy('order_id');
            $sellReturns = SellReturnDB::join($orderTable,$orderTable.'.id',$sellReturnTable.'.order_id')
            ->where($sellReturnTable.'.is_del',0)
            ->whereIn($sellReturnTable.'.order_id',$sellReturnOrderIds);
            $sellReturns = $sellReturns->select([
                DB::raw("DATE_FORMAT((Select sell_date from $sellTable where $sellTable.order_id = $sellReturnTable.order_id order by sell_date desc limit 1),'%Y') as year"),
                DB::raw("DATE_FORMAT((Select sell_date from $sellTable where $sellTable.order_id = $sellReturnTable.order_id order by sell_date desc limit 1),'%Y-%m') as yyyymm"),
                DB::raw("SUM(price) as sell_return"),
                $orderTable.'.digiwin_payment_id as source',
                $orderTable.'.create_type',
            ])->groupBy('yyyymm','source')->orderBy('yyyymm', 'desc')->get();

            for($y = $startYear; $y<= $finalYear; $y++){
                for($m = 1; $m <=12; $m++){
                    $m <= 9 ? $m = '0'.$m : '';
                    $ym = $y.'-'.$m;
                    if($ym > date('Y').'-'.date('m')){
                        break 2;
                    }
                    for($i=0;$i<count($source);$i++){
                        $return = $money = $order = 0;
                        foreach ($tmps as $tmp) {
                            if($source[$i] == '001'){
                                if($tmp->yyyymm == $ym && $tmp->create_type == 'web'){
                                    $money = $tmp->pay_money_total;
                                    break;
                                }
                            }else{
                                if($tmp->yyyymm == $ym && $tmp->source == $source[$i]){
                                    $money = $tmp->pay_money_total;
                                    break;
                                }
                            }
                        }
                        foreach ($iCarryOrders as $iCarryOrder) {
                            if($source[$i] == '001'){
                                if($iCarryOrder->yyyymm == $ym && $iCarryOrder->create_type == 'web'){
                                    $order = $iCarryOrder->pay_orders;
                                    break;
                                }
                            }else{
                                if($iCarryOrder->yyyymm == $ym && $iCarryOrder->source == $source[$i]){
                                    $order = $iCarryOrder->pay_orders;
                                    break;
                                }
                            }
                        }
                        foreach ($sellReturns as $sellReturn) {
                            if($source[$i] == '001'){
                                if($sellReturn->yyyymm == $ym && $sellReturn->create_type == 'web'){
                                    $return = $sellReturn->sell_return;
                                    break;
                                }
                            }else{
                                if($sellReturn->yyyymm == $ym && $sellReturn->source == $source[$i]){
                                    $return = $sellReturn->sell_return;
                                    break;
                                }
                            }
                        }
                        $source[$i] == '' ? $s = 'all' : $s = $source[$i];
                        $orders[$y][$ym][$s]['orders'] = $order;
                        $orders[$y][$ym][$s]['money'] = $money;
                        $orders[$y][$ym][$s]['return'] = $return;
                        $total[$i][$y]['orders'][] = $order;
                        $total[$i][$y]['money'][] = $money;
                        $total[$i][$y]['return'][] = $return;
                    }
                    krsort($orders[$y]);
                }
            }
            krsort($orders);
            //將all還原並計算資料
            for ($i=0;$i<count($source);$i++) {
                $source[$i] == '' ? $source[$i] = 'all' : '';
                for($y = $startYear; $y<= $finalYear; $y++){
                    $total[$i][$y]['orders'] = collect($total[$i][$y]['orders'])->sum();
                    $total[$i][$y]['money'] = collect($total[$i][$y]['money'])->sum();
                    $total[$i][$y]['return'] = collect($total[$i][$y]['return'])->sum();
                }
            }
            // dd($total);
        }else{
            $orders = SellDB::where('is_del',0)->whereBetween('sell_date',['2023-01-01',$today]);
            $orders = $orders->select([
                DB::raw("DATE_FORMAT(sell_date,'%Y') as year"),
                DB::raw("DATE_FORMAT(sell_date,'%Y-%m') as yyyymm"),
                DB::raw("SUM(amount) as pay_money_total"),
            ])->groupBy('yyyymm')->orderBy('yyyymm', 'desc')->get()->groupBy('year')->all();

            $sellOrderIds = SellDB::where('is_del',0)->whereBetween('sell_date',['2023-01-01',$today])->select('order_id')->groupBy('order_id');
            $iCarryOrders = OrderDB::whereIn('id',$sellOrderIds);
            $iCarryOrders = $iCarryOrders->select([
                DB::raw("DATE_FORMAT( (Select sell_date from $sellTable where $sellTable.order_id = $orderTable.id order by sell_date desc limit 1)  ,'%Y') as year"),
                DB::raw("DATE_FORMAT((Select sell_date from $sellTable where $sellTable.order_id = $orderTable.id order by sell_date desc limit 1) ,'%Y-%m') as yyyymm"),
                DB::raw("COUNT(id) as pay_orders"),
                DB::raw("SUM((shipping_fee+parcel_tax) / 1.05) as ffeight_tariff_total"),
            ])->groupBy('yyyymm')->orderBy('yyyymm', 'desc')->get();

            //銷退折讓金額
            $sellReturnOrderIds = SellDB::where('is_del',0)->whereBetween('sell_date',['2023-01-01',date('Y-m-d')])->select('order_id')->groupBy('order_id');
            $sellReturns = SellReturnDB::where('is_del',0)->whereIn('order_id',$sellReturnOrderIds);
            $sellReturns = $sellReturns->select([
                DB::raw("DATE_FORMAT((Select sell_date from $sellTable where $sellTable.order_id = $sellReturnTable.order_id order by sell_date desc limit 1),'%Y') as year"),
                DB::raw("DATE_FORMAT((Select sell_date from $sellTable where $sellTable.order_id = $sellReturnTable.order_id order by sell_date desc limit 1),'%Y-%m') as yyyymm"),
                DB::raw("SUM(price) as not_ok_total"),
            ])->groupBy('yyyymm')->orderBy('yyyymm', 'desc')->get();

            foreach ($orders as $year => $values) {
                $sell_return_total = $pay_orders = $pay_money_total = $avg_orders_money = $ffeight_tariff_total = $no_pay_orders = 0;
                foreach ($values as $order) {
                    $order->pay_orders = $order->sell_return_total = 0;
                    foreach ($sellReturns as $sellReturn) {
                        if($sellReturn->yyyymm == $order->yyyymm){
                            $order->sell_return_total = $sellReturn->not_ok_total;
                            break;
                        }
                    }
                    foreach ($iCarryOrders as $iCarryOrder) {
                        if($iCarryOrder->yyyymm == $order->yyyymm){
                            $order->pay_orders = $iCarryOrder->pay_orders;
                            $order->ffeight_tariff_total = round($iCarryOrder->ffeight_tariff_total,2);
                            $order->avg_orders_money = round($order->pay_money_total / $iCarryOrder->pay_orders,2);
                            break;
                        }
                    }
                    $pay_orders += $order->pay_orders;
                    $pay_money_total += $order->pay_money_total;
                    $ffeight_tariff_total += $order->ffeight_tariff_total;
                    $sell_return_total += $order->sell_return_total;
                }
                if($pay_orders > 0 && $pay_money_total > 0){
                    $avg_orders_money = round($pay_money_total / $pay_orders,2);
                }
                $total[$year] = ['text' => $year.' 年統計', 'pay_orders' => $pay_orders, 'pay_money_total' => $pay_money_total, 'avg_orders_money' => $avg_orders_money, 'ffeight_tariff_total' => $ffeight_tariff_total, 'sell_return_total' => $sell_return_total];
            }
        }
        $sources = DigiwinPaymentDB::whereNotIn('customer_no',$this->iCarry)->where(function($query){
            $query->where('customer_no','<=','999')
            ->orWhereIn('customer_no',['065001','065002','065003','065004','065005','065006','065007','065008','065009','065010','065011','AC0001','AC000101','AC000102','AC000103']);
        })->select([
            'customer_no as source',
            'customer_name as name'
        ])->orderBy('source','asc')->get();
        foreach($sources as $s){
            if($s->source == '001'){
                $s->name = 'iCarry Web';
            }
        }
        $compact = array_merge($compact,['menuCode', 'orders', 'sources', 'total']);
        return view('admin.statistics.order_monthly_sell_total',compact($compact));
    }

    public function shippingMonthlyTotal()
    {
        $menuCode = 'M7S5';
        $compact = $total = [];
        $amount = 'amount+shipping_fee+parcel_tax-discount-spend_point';
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            $compact = array_merge($compact, [$key]);
        }
        $orders = OrderDB::where('status','>=',1)->whereBetween('shipping_method',[1,6])
            ->whereBetween('pay_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
        if(!empty($source)){
            $source == '001' ? $orders = $orders->where('create_type','web') : $orders = $orders->where('digiwin_payment_id',$source);
        }
        $orders = $orders->select([
            DB::raw("DATE_FORMAT(pay_time,'%Y') as yyyy"),
            DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
            DB::raw("SUM(CASE WHEN shipping_method = 1 THEN 1 ELSE 0 END) as S1C"),
            DB::raw("SUM(CASE WHEN shipping_method = 1 THEN $amount ELSE 0 END) as S1"),
            DB::raw("SUM(CASE WHEN shipping_method = 2 THEN 1 ELSE 0 END) as S2C"),
            DB::raw("SUM(CASE WHEN shipping_method = 2 THEN $amount ELSE 0 END) as S2"),
            DB::raw("SUM(CASE WHEN shipping_method = 3 THEN 1 ELSE 0 END) as S3C"),
            DB::raw("SUM(CASE WHEN shipping_method = 3 THEN $amount ELSE 0 END) as S3"),
            DB::raw("SUM(CASE WHEN shipping_method = 4 THEN 1 ELSE 0 END) as S4C"),
            DB::raw("SUM(CASE WHEN shipping_method = 4 THEN $amount ELSE 0 END) as S4"),
            DB::raw("SUM(CASE WHEN shipping_method = 5 THEN 1 ELSE 0 END) as S5C"),
            DB::raw("SUM(CASE WHEN shipping_method = 5 THEN $amount ELSE 0 END) as S5"),
            DB::raw("SUM(CASE WHEN shipping_method = 6 THEN 1 ELSE 0 END) as S6C"),
            DB::raw("SUM(CASE WHEN shipping_method = 6 THEN $amount ELSE 0 END) as S6"),
            DB::raw("SUM(1) as allCount"),
            DB::raw("SUM($amount) as allAmount"),
        ])->groupBy('yyyymm')->orderBy('yyyymm','desc')->get()->groupBy('yyyy')->all();

        if(!empty($orders)){
            foreach($orders as $year => $value){
                $total[$year]['S1C'] = $total[$year]['S1'] = $total[$year]['S2C'] = $total[$year]['S2'] = $total[$year]['S3C'] = $total[$year]['S3'] = $total[$year]['S4C'] = $total[$year]['S4'] = $total[$year]['S5C'] = $total[$year]['S5'] = $total[$year]['S6C'] = $total[$year]['S6'] = $total[$year]['allCount'] = $total[$year]['allAmount'] = 0;
                foreach($value as $order){
                    $total[$year]['S1C'] += $order->S1C;
                    $total[$year]['S1'] += $order->S1;
                    $total[$year]['S2C'] += $order->S2C;
                    $total[$year]['S2'] += $order->S2;
                    $total[$year]['S3C'] += $order->S3C;
                    $total[$year]['S3'] += $order->S3;
                    $total[$year]['S4C'] += $order->S4C;
                    $total[$year]['S4'] += $order->S4;
                    $total[$year]['S5C'] += $order->S5C;
                    $total[$year]['S5'] += $order->S5;
                    $total[$year]['S6C'] += $order->S6C;
                    $total[$year]['S6'] += $order->S6;
                    $total[$year]['allCount'] += $order->allCount;
                    $total[$year]['allAmount'] += $order->allAmount;
                }
            }
        }
        // dd($total);
        // dd($orders->groupBy('yyyy')->toArray());
        $sources = DigiwinPaymentDB::whereNotIn('customer_no',$this->iCarry)->where(function($query){
            $query->where('customer_no','<=','999')
            ->orWhereIn('customer_no',['065001','065002','065003','065004','065005','065006','065007','065008','065009','065010','065011']);
        })->select([
            'customer_no as source',
            'customer_name as name'
        ])->orderBy('source','asc')->get();
        foreach($sources as $s){
            if($s->source == '001'){
                $s->name = 'iCarry Web';
            }
        }
        $compact = array_merge($compact,['menuCode', 'orders', 'sources', 'total']);
        return view('admin.statistics.shipping_monthly_total',compact($compact));
    }

    public function intervalStatistics()
    {
        $menuCode = 'M7S4';
        $compact = $orders = $total = [];

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            $compact = array_merge($compact, [$key]);
        }
        if(isset($pay_time) && isset($pay_time_end)){
            $tmps = OrderDB::where([['status','>',0],['pay_time','>=',$pay_time],['pay_time','<=',$pay_time_end]]);
            !empty($to) ? $tmps = $tmps->where('ship_to',$to) : '';
            if (!empty($source)) {
                $source = explode(',', $source);
                $chkWeb = 0;
                for($i=0;$i<count($source);$i++){
                    if($source[$i] == '001'){
                        $chkWeb = 1;
                        break;
                    }
                }
                $chkWeb == 1 ? $tmps = $tmps->where(function($query)use($source){$query->whereIn('digiwin_payment_id',$source)->orWhere('create_type','web');}) : $tmps = $tmps->whereIn('digiwin_payment_id',$source);
            }
            $tmps = $tmps->select([
                DB::raw("COUNT(id) as pay_orders"),
                'digiwin_payment_id',
                'create_type',
                DB::raw('DATE_FORMAT(pay_time,"%Y-%m-%d") as date'),
                DB::raw('SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as total'),
            ]);
            $tmps = $tmps->groupBy('date','create_type')->orderBy('date', 'desc')->get();
            $dates = $this->dateRange($pay_time,$pay_time_end);
            rsort($dates);
            for($d=0;$d<count($dates);$d++){
                for ($i=0;$i<count($source);$i++) {
                    $order = $money = 0;
                    if($source[$i]=='001'){
                        foreach ($tmps as $value) {
                            if ($value->create_type == 'web' && $value->date == $dates[$d]) {
                                $money += $value->total;
                                $order += $value->pay_orders;
                            }
                        }
                    }else{
                        foreach ($tmps as $value) {
                            if ($value->digiwin_payment_id == $source[$i] && $value->date == $dates[$d]) {
                                $money += $value->total;
                                $order += $value->pay_orders;
                            }
                        }
                    }
                    $orders[$dates[$d]][$source[$i]]['orders'] = $order;
                    $orders[$dates[$d]][$source[$i]]['money'] = $money;
                    $total[$i]['orders'][] = $order;
                    $total[$i]['money'][] = $money;
                }
            }
            //總計資料
            for ($i=0;$i<count($source);$i++) {
                $total[$i]['orders'] = collect($total[$i]['orders'])->sum();
                $total[$i]['money'] = collect($total[$i]['money'])->sum();
            }
        }
        $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
        $countries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->distinct($countryTable.'.name')->get();
        $sources = DigiwinPaymentDB::whereNotIn('customer_no',$this->iCarry)->where(function($query){
            $query->where('customer_no','<=','999')
            ->orWhereIn('customer_no',['065001','065002','065003','065004','065005','065006','065007','065008','065009','065010','065011','AC0001','AC000101','AC000102','AC000103']);
        })->select([
            'customer_no as source',
            'customer_name as name'
        ])->orderBy('source','asc')->get();
        foreach($sources as $s){
            if($s->source == '001'){
                $s->name = 'iCarry Web';
            }
        }
        $compact = array_merge($compact,['menuCode', 'orders', 'countries', 'sources', 'total']);
        return view('admin.statistics.interval_statistics',compact($compact));
    }

    public function productSales()
    {
        $menuCode = 'M7S6';
        $orderItems = $total = $appends = $compact = [];
        $allQuantity = $allPrice = $totalItems = 0;

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        if(!isset($sort)){
            $sort = 'totalQuantity';
            $compact = array_merge($compact, ['sort']);
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }

        $orderTable = env('DB_ICARRY').'.'.(new OrderDB)->getTable();
        $orderItemTable = env('DB_ICARRY').'.'.(new OrderItemDB)->getTable();
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();

        if (isset($pay_time) && isset($pay_time_end)) {
            $compact = array_merge($compact, ['pay_time','pay_time_end']);
            //找出區間訂單裡面的商品
            $orderItems = OrderItemDB::join($orderTable,$orderTable.'.id',$orderItemTable.'.order_id')
                // ->join($productModelTable,$productModelTable.'.id',$orderItemTable.'.product_model_id')
                // ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                // ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                ->where($orderTable.'.status','>=',1)
                ->where($orderItemTable.'.is_del',0)
                ->whereBetween($orderTable.'.pay_time',[$pay_time,$pay_time_end]);

            //商家條件
            !empty($vendor_id) ? $orderItems = $orderItems->where($orderItemTable.'.vendor_id',$vendor_id) : '';

            //渠道條件
            !empty($source) ? $source == '001' ? $orderItems = $orderItems->where($orderTable.'.create_type','web') : $orderItems = $orderItems->where($orderTable.'.digiwin_payment_id',$source) : '';

            $orderItems = $orderItems->select([
                    // $orderItemTable.'.*',
                    $orderItemTable.'.id',
                    $orderItemTable.'.vendor_id',
                    $orderItemTable.'.product_model_id',
                    // $orderItemTable.'.price',
                    // $productTable.'.id as product_id',
                    // $vendorTable.'.id as vendor_id',
                    // $vendorTable.'.name as vendor_name',
                    // $vendorTable.'.is_on as vendor_ison',
                    // DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
                    // $productTable.'.unit_name',
                    'product_name' => ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                    ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                    ->whereColumn($productModelTable.'.id',$orderItemTable.'.product_model_id')
                    ->select([
                        DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
                    ])->limit(1),
                    'vendor_name' => VendorDB::whereColumn($vendorTable.'.id',$orderItemTable.'.vendor_id')->select('name')->limit(1),
                    'vendor_ison' => VendorDB::whereColumn($vendorTable.'.id',$orderItemTable.'.vendor_id')->select('is_on')->limit(1),
                    'unit_name' => ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')->whereColumn($productModelTable.'.id',$orderItemTable.'.product_model_id')->select($productTable.'.unit_name')->limit(1),
                    'origin_digiwin_no' => ProductModelDB::whereColumn($productModelTable.'.id',$orderItemTable.'.product_model_id')->select($productModelTable.'.origin_digiwin_no')->limit(1),
                    DB::raw("SUM($orderItemTable.quantity) as totalQuantity"),
                    DB::raw("SUM($orderItemTable.quantity * $orderItemTable.price) as totalPrice"),
                ])->groupBy($orderItemTable.'.product_model_id');
            $orderItems = $orderItems->orderBy($sort,'desc')->paginate($list);
            // $totalItems = $orderItems->total();

            //計算及找其它資料
            foreach($orderItems as $item){
                if(!empty($item->origin_digiwin_no)){
                    $tmp = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                    ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                    ->where($productModelTable.'.digiwin_no',$item->origin_digiwin_no)
                    ->select([
                        $productTable.'.unit_name',
                        DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
                    ])->first();
                    if(!empty($tmp)){
                        $item->unit_name = $tmp->unit_name;
                        $item->product_name = $tmp->product_name;
                    }
                }
                $allQuantity += $item->totalQuantity;
                $allPrice += $item->totalPrice;
            }
        }
        $sources = DigiwinPaymentDB::whereNotIn('customer_no',$this->iCarry)->where(function($query){
            $query->where('customer_no','<=','999')
            ->orWhereIn('customer_no',['065001','065002','065003','065004','065005','065006','065007','065008','065009','065010','065011','AC0001','AC000101','AC000102','AC000103']);
        })->select([
            'customer_no as source',
            'customer_name as name'
        ])->orderBy('source','asc')->get();
        foreach($sources as $s){
            if($s->source == '001'){
                $s->name = 'iCarry Web';
            }
        }
        $vendors = VendorDB::select(['id','name','is_on'])->orderBy('is_on','desc')->get();

        $compact = array_merge($compact,['menuCode','appends','orderItems','sources','vendors','allQuantity','allPrice']);
        return view('admin.statistics.product_sales',compact($compact));
    }

    public function vendorSales()
    {
        $menuCode = 'M7S7';
        $orderItems = $compact = $total = $appends = [];

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        if(!isset($sort)){
            $sort = 'totalQuantity';
            $compact = array_merge($compact, ['sort']);
        }
        if (!isset($list)) {
            $list = 15;
            $compact = array_merge($compact, ['list']);
        }

        $orderTable = env('DB_ICARRY').'.'.(new OrderDB)->getTable();
        $orderItemTable = env('DB_ICARRY').'.'.(new OrderItemDB)->getTable();
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();

        if (isset($pay_time) && isset($pay_time_end)) {
            $compact = array_merge($compact, ['pay_time','pay_time_end']);
            //找出區間訂單裡面的商品
            $orderItems = OrderItemDB::join($orderTable,$orderTable.'.id',$orderItemTable.'.order_id')
                ->join($productModelTable,$productModelTable.'.id',$orderItemTable.'.product_model_id')
                ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                ->where($orderItemTable.'.is_del',0)
                ->where($orderTable.'.status','>=',1)
                ->whereBetween($orderTable.'.pay_time',[$pay_time,$pay_time_end]);

            if(!empty($source)){
                if($source == '001'){
                    $orderItems = $orderItems->where($orderTable.'.create_type','web');
                    $allQuantity = OrderItemDB::join($orderTable,$orderTable.'.id',$orderItemTable.'.order_id')->where([[$orderTable.'.status','>=',1],[$orderTable.'.create_type','web']])->whereBetween($orderTable.'.pay_time',[$pay_time,$pay_time_end])->where($orderItemTable.'.is_del',0)->select($orderItemTable.'.quantity')->sum('quantity');
                }else{
                    $orderItems = $orderItems->where($orderTable.'.digiwin_payment_id',$source);
                    $allQuantity = OrderItemDB::join($orderTable,$orderTable.'.id',$orderItemTable.'.order_id')->where([[$orderTable.'.status','>=',1],[$orderTable.'.digiwin_payment_id',$source]])->whereBetween($orderTable.'.pay_time',[$pay_time,$pay_time_end])->where($orderItemTable.'.is_del',0)->select($orderItemTable.'.quantity')->sum('quantity');
                }
            }else{
                //所有數量(子查詢句子)
                // $allQuantity = "(SELECT SUM(quantity) from order_item join orders where orders.id = order_item.order_id and orders.status >= 1 and orders.pay_time >= '{$pay_time}' and orders.pay_time <= '{$pay_time_end}' limit 1)";
                $allQuantity = OrderItemDB::join($orderTable ,$orderTable.'.id',$orderItemTable.'.order_id')->where($orderTable.'.status','>=',1)->whereBetween($orderTable.'.pay_time',[$pay_time,$pay_time_end])->where($orderItemTable.'.is_del',0)->select($orderItemTable.'.quantity')->sum('quantity');
            }

            $orderItems = $orderItems->select([
                $vendorTable.'.id as vendor_id',
                $vendorTable.'.name as vendor_name',
                $vendorTable.'.is_on as vendor_ison',
                DB::raw("Count($orderItemTable.order_id) as totalOrder"),
                DB::raw("SUM($orderItemTable.quantity) as totalQuantity"),
                DB::raw("Truncate((SUM($orderItemTable.quantity) / {$allQuantity} * 100),2) as percent"),
                DB::raw("SUM($orderItemTable.quantity * $orderItemTable.price) as totalPrice"),
                DB::raw("(SUM($orderItemTable.quantity * $orderItemTable.price) / SUM($orderItemTable.quantity) ) as totalAvg"),
            ])->groupBy($vendorTable.'.id');

            $orderItems = $orderItems->orderBy($sort,'desc')->paginate($list);

            //計算
            $totalOrder = $totalQuantity = $totalPercent = $totalAvg = $totalPrice = 0;
            foreach($orderItems as $item){
                $totalOrder += $item->totalOrder;
                $totalQuantity += $item->totalQuantity;
                $totalPrice += $item->totalPrice;
                $totalPercent += $item->percent;
            }
            if($totalPrice > 0 && $totalQuantity > 0){
                $totalAvg = $totalPrice / $totalQuantity;
            }
            $total = ['totalOrder' => $totalOrder, 'totalQuantity' => $totalQuantity, 'totalPercent' => $totalPercent, 'totalPrice' => $totalPrice, 'totalAvg' => $totalAvg];
        }
        $sources = DigiwinPaymentDB::whereNotIn('customer_no',$this->iCarry)->where(function($query){
            $query->where('customer_no','<=','999')
            ->orWhereIn('customer_no',['065001','065002','065003','065004','065005','065006','065007','065008','065009','065010','065011','AC0001','AC000101','AC000102','AC000103']);
        })->select([
            'customer_no as source',
            'customer_name as name'
        ])->orderBy('source','asc')->get();
        foreach($sources as $s){
            if($s->source == '001'){
                $s->name = 'iCarry Web';
            }
        }
        $vendors = VendorDB::select(['id','name','is_on'])->orderBy('is_on','desc')->get();

        $compact = array_merge($compact,['menuCode','appends','orderItems','sources','vendors','total']);
        return view('admin.statistics.vendor_sales',compact($compact));
    }

    private function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d')
    {
        $dates   = array();
        $current = strtotime($first);
        $last    = strtotime($last);
        while ($current <= $last) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }
        return $dates;
    }

    // 以下function已改用直接抓取 OrderDB 計算, 保留參考用.

    // public function orderDailyTotal()
    // {
    //     $menuCode = 'M7S2';
    //     $compact = [];
    //     //將進來的資料作參數轉換及附加到appends及compact中
    //     foreach (request()->all() as $key => $value) {
    //         $$key = $value;
    //         $compact = array_merge($compact, [$key]);
    //     }
    //     if(!isset($yyyymm) || $yyyymm == ''){
    //         $yyyymm = date('Y').'-'.date('m');
    //         env('APP_ENV') != 'production' ? $yyyymm = '2019-08' : ''; //測試用
    //     }
    //     //當月天數及當月所有日期
    //     $countDay = date("t",strtotime($yyyymm));
    //     for($i=1;$i<=$countDay;$i++){
    //         if($i<=9){
    //             $date[] = $yyyymm.'-0'.$i;
    //         }else{
    //             $date[] = $yyyymm.'-'.$i;
    //         }
    //     }
    //     rsort($date); //反排
    //     if(!empty($source)){
    //         $source = array_filter(explode(',',$source));
    //         sort($source);
    //         $tmps = OrderDailyTotalDB::where('yyyymm',$yyyymm)->whereIn('source',$source)->orderBy('yyyymmdd', 'desc')->get()->groupBy('source')->all();
    //         for ($d=0; $d<count($date); $d++) {
    //             for($i=0;$i<count($source);$i++){
    //                 $order = 0;
    //                 $money = 0;
    //                 foreach($tmps as $sourc => $value){
    //                     foreach($value as $v){
    //                         if($v->yyyymmdd == $date[$d] && $v->source == $source[$i]){
    //                             $order = $v->total_order;
    //                             $money = $v->total_money;
    //                             break 2;
    //                         }
    //                     }
    //                 }
    //                 $orders[$date[$d]][$source[$i]]['orders'] = $order;
    //                 $orders[$date[$d]][$source[$i]]['money'] = $money;
    //                 $total[$i]['orders'][] = $order;
    //                 $total[$i]['money'][] = $money;
    //             }
    //         }
    //         //計算資料
    //         for ($i=0;$i<count($source);$i++) {
    //             $total[$i]['orders'] = collect($total[$i]['orders'])->sum();
    //             $total[$i]['money'] = collect($total[$i]['money'])->sum();
    //         }
    //     }else{
    //         $total = ['text' => $yyyymm.' 月份統計', 'monthly_order' => 0, 'total_money' => 0, 'total_shipping_tax' => 0, 'not_ok_total' => 0, 'avg' => 0, 'user_total' => 0,'distinct_buyer_total' => 0];
    //         $orders = OrderDailyTotalDB::where('yyyymm',$yyyymm)
    //             ->select([
    //                 'yyyymmdd',
    //                 DB::raw("SUM(total_order) as total_order"),
    //                 DB::raw("SUM(total_money) as total_money"),
    //                 DB::raw("SUM(total_shipping_tax) as total_shipping_tax"),
    //                 DB::raw("SUM(not_ok_total) as not_ok_total"),
    //                 'user_total',
    //                 DB::raw("truncate(SUM(total_money) / SUM(total_order),2) as avg"),
    //                 DB::raw("SUM(distinct_buyer_total) as distinct_buyer_total"),
    //             ])->groupBy('yyyymmdd')->orderBy('yyyymmdd', 'desc')->get();
    //         foreach ($orders as $order) {
    //             $total['monthly_order'] += $order->total_order;
    //             $total['total_money'] += $order->total_money;
    //             $total['total_shipping_tax'] += $order->total_shipping_tax;
    //             $total['not_ok_total'] += $order->not_ok_total;
    //             $total['user_total'] += $order->user_total;
    //             $total['distinct_buyer_total'] += $order->distinct_buyer_total;
    //         }
    //         if($total['monthly_order'] > 0 && $total['monthly_order'] > 0){
    //             $total['avg'] = round($total['total_money'] / $total['monthly_order'],2);
    //         }
    //     }
    //     $startYear = orderDailyTotalDB::selectRaw('DATE_FORMAT(yyyymmdd,"%Y") as year')->groupBy('year')->orderBy('year','asc')->first()->year;
    //     $finalYear = orderDailyTotalDB::selectRaw('DATE_FORMAT(yyyymmdd,"%Y") as year')->groupBy('year')->orderBy('year','desc')->first()->year;
    //     $sources = SourceDB::orderBy('name','asc')->get();
    //     $compact = array_merge($compact,['menuCode', 'orders', 'total', 'sources', 'startYear', 'finalYear']);
    //     return view('admin.statistics.order_daily_total',compact($compact));
    // }

    // public function shippingMonthlyTotal()
    // {
    //     $menuCode = 'M7S5';
    //     $compact = [];
    //     $total = [];
    //     $orders = [];
    //     $startYear = 2015;
    //     $finalYear = date('Y');
    //     for($y = $startYear; $y<= $finalYear; $y++){
    //         for($i=1; $i<=12; $i++){
    //             $i <= 9 ? $m = '0'.$i : $m = $i;
    //             $tmp = $y.'-'.$m;
    //             $yyyymm[] = $tmp;
    //             if($tmp == date('Y').'-'.date('m')){
    //                 break 2;
    //             }
    //         }
    //     }
    //     rsort($yyyymm); //反排
    //     //將進來的資料作參數轉換及附加到appends及compact中
    //     foreach (request()->all() as $key => $value) {
    //         $$key = $value;
    //         $compact = array_merge($compact, [$key]);
    //     }
    //     $tmps = ShippingMonthlyTotalDB::whereIn('yyyymm',$yyyymm);

    //     if(isset($source) && $source != ''){
    //         $tmps = $tmps->where('source',$source);
    //     }

    //     $tmps = $tmps->select([
    //         '*',
    //         DB::raw('left(yyyymm,4) as year'),
    //     ]);
    //     $tmps = $tmps->orderBy('yyyymm', 'desc')->get();

    //     $tmps = $tmps->groupBy('year')->all();

    //     if($tmps){
    //         for($y = $startYear; $y<= $finalYear; $y++){
    //             $total[$y]['allCount'] = $total[$y]['allMoney'] = $total[$y]['shipping1Count'] = $total[$y]['shipping1Money'] = $total[$y]['shipping2Count'] = $total[$y]['shipping2Money'] = $total[$y]['shipping3Count'] = $total[$y]['shipping3Money'] = $total[$y]['shipping4Count'] = $total[$y]['shipping4Money'] = $total[$y]['shipping5Count'] = $total[$y]['shipping5Money'] = $total[$y]['shipping6Count'] = $total[$y]['shipping6Money'] = 0;
    //             for($m = 1; $m <=12; $m++){
    //                 $m <= 9 ? $m = '0'.$m : '';
    //                 $ym = $y.'-'.$m;
    //                 if($ym > date('Y').'-'.date('m')){
    //                     break 1;
    //                 }
    //                 $orders[$y][$ym]['totalCount'] = $orders[$y][$ym]['totalMoney'] = 0;
    //                 $orders[$y][$ym]['shipping_1_count'] = $orders[$y][$ym]['shipping_1_money'] = $orders[$y][$ym]['shipping_2_count'] = $orders[$y][$ym]['shipping_2_money'] = $orders[$y][$ym]['shipping_3_count'] = $orders[$y][$ym]['shipping_3_money'] = $orders[$y][$ym]['shipping_4_count'] = $orders[$y][$ym]['shipping_4_money'] = $orders[$y][$ym]['shipping_5_count'] = $orders[$y][$ym]['shipping_5_money'] = $orders[$y][$ym]['shipping_6_count'] = $orders[$y][$ym]['shipping_6_money'] = 0;
    //                 foreach($tmps as $year => $values){
    //                     foreach($values as $value){
    //                         if($value->yyyymm == $ym){
    //                             $orders[$y][$ym]['shipping_1_count'] += $value->shipping_1_count;
    //                             $orders[$y][$ym]['shipping_1_money'] += $value->shipping_1_money;
    //                             $orders[$y][$ym]['shipping_2_count'] += $value->shipping_2_count;
    //                             $orders[$y][$ym]['shipping_2_money'] += $value->shipping_2_money;
    //                             $orders[$y][$ym]['shipping_3_count'] += $value->shipping_3_count;
    //                             $orders[$y][$ym]['shipping_3_money'] += $value->shipping_3_money;
    //                             $orders[$y][$ym]['shipping_4_count'] += $value->shipping_4_count;
    //                             $orders[$y][$ym]['shipping_4_money'] += $value->shipping_4_money;
    //                             $orders[$y][$ym]['shipping_5_count'] += $value->shipping_5_count;
    //                             $orders[$y][$ym]['shipping_5_money'] += $value->shipping_5_money;
    //                             $orders[$y][$ym]['shipping_6_count'] += $value->shipping_6_count;
    //                             $orders[$y][$ym]['shipping_6_money'] += $value->shipping_6_money;
    //                         }
    //                     }
    //                     $orders[$y][$ym]['totalCount'] = $orders[$y][$ym]['shipping_1_count']+$orders[$y][$ym]['shipping_2_count']+$orders[$y][$ym]['shipping_3_count']+$orders[$y][$ym]['shipping_4_count']+$orders[$y][$ym]['shipping_5_count']+$orders[$y][$ym]['shipping_6_count'];
    //                     $orders[$y][$ym]['totalMoney'] = $orders[$y][$ym]['shipping_1_money']+$orders[$y][$ym]['shipping_2_money']+$orders[$y][$ym]['shipping_3_money']+$orders[$y][$ym]['shipping_4_money']+$orders[$y][$ym]['shipping_5_money']+$orders[$y][$ym]['shipping_6_money'];
    //                 }
    //                 $total[$y]['shipping1Count'] += $orders[$y][$ym]['shipping_1_count'];
    //                 $total[$y]['shipping1Money'] += $orders[$y][$ym]['shipping_1_money'];
    //                 $total[$y]['shipping2Count'] += $orders[$y][$ym]['shipping_2_count'];
    //                 $total[$y]['shipping2Money'] += $orders[$y][$ym]['shipping_2_money'];
    //                 $total[$y]['shipping3Count'] += $orders[$y][$ym]['shipping_3_count'];
    //                 $total[$y]['shipping3Money'] += $orders[$y][$ym]['shipping_3_money'];
    //                 $total[$y]['shipping4Count'] += $orders[$y][$ym]['shipping_4_count'];
    //                 $total[$y]['shipping4Money'] += $orders[$y][$ym]['shipping_4_money'];
    //                 $total[$y]['shipping5Count'] += $orders[$y][$ym]['shipping_5_count'];
    //                 $total[$y]['shipping5Money'] += $orders[$y][$ym]['shipping_5_money'];
    //                 $total[$y]['shipping6Count'] += $orders[$y][$ym]['shipping_6_count'];
    //                 $total[$y]['shipping6Money'] += $orders[$y][$ym]['shipping_6_money'];
    //                 krsort($orders[$y]);
    //             }
    //             $total[$y]['allCount'] = $total[$y]['shipping1Count']+$total[$y]['shipping2Count']+$total[$y]['shipping3Count']+$total[$y]['shipping4Count']+$total[$y]['shipping5Count']+$total[$y]['shipping6Count'];
    //             $total[$y]['allMoney'] = $total[$y]['shipping1Money']+$total[$y]['shipping2Money']+$total[$y]['shipping3Money']+$total[$y]['shipping4Money']+$total[$y]['shipping5Money']+$total[$y]['shipping6Money'];
    //         }
    //         krsort($orders);
    //     }
    //     $sources = SourceDB::orderBy('name','asc')->get();
    //     $compact = array_merge($compact,['menuCode', 'orders', 'sources', 'total']);
    //     return view('admin.statistics.shipping_monthly_total',compact($compact));
    // }

    // public function orderMonthlyTotal()
    // {
    //     $menuCode = 'M7S3';
    //     $compact = [];
    //     $total = [];
    //     $startYear = 2016;
    //     $finalYear = date('Y');
    //     for($y = $startYear; $y<= $finalYear; $y++){
    //         for($i=1; $i<=12; $i++){
    //             $i <= 9 ? $m = '0'.$i : $m = $i;
    //             $tmp = $y.'-'.$m;
    //             $yyyymm[] = $tmp;
    //             if($tmp == date('Y').'-'.date('m')){
    //                 break 2;
    //             }
    //         }
    //     }
    //     rsort($yyyymm); //反排
    //     //將進來的資料作參數轉換及附加到appends及compact中
    //     foreach (request()->all() as $key => $value) {
    //         $$key = $value;
    //         $compact = array_merge($compact, [$key]);
    //     }

    //     if (!empty($source)) {
    //         $source = explode(',', $source);
    //         for ($i=0;$i<count($source);$i++) {
    //             $source[$i] == 'all' ? $source[$i] = '' : ''; //將all去除為空
    //         }

    //         $tmps = OrderMonthlyTotalDB::whereIn('yyyymm',$yyyymm);
    //         $tmps = $tmps->whereIn('source',$source);
    //         $tmps = $tmps->select([
    //             '*',
    //             DB::raw('left(yyyymm,4) as year'),
    //         ]);
    //         $tmps = $tmps->orderBy('yyyymm', 'desc')->get();

    //         for($y = $startYear; $y<= $finalYear; $y++){
    //             for($m = 1; $m <=12; $m++){
    //                 $m <= 9 ? $m = '0'.$m : '';
    //                 $ym = $y.'-'.$m;
    //                 if($ym > date('Y').'-'.date('m')){
    //                     break 2;
    //                 }
    //                 for($i=0;$i<count($source);$i++){
    //                     $order = 0;
    //                     $money = 0;
    //                     foreach ($tmps as $tmp) {
    //                         if($tmp->yyyymm == $ym && $tmp->source == $source[$i]){
    //                             $order = $tmp->pay_orders;
    //                             $money = $tmp->pay_money_total;
    //                             break;
    //                         }
    //                     }
    //                     $source[$i] == '' ? $s = 'all' : $s = $source[$i];
    //                     $orders[$y][$ym][$s]['orders'] = $order;
    //                     $orders[$y][$ym][$s]['money'] = $money;
    //                     $total[$i][$y]['orders'][] = $order;
    //                     $total[$i][$y]['money'][] = $money;
    //                 }
    //                 krsort($orders[$y]);
    //             }
    //         }
    //         krsort($orders);
    //         //將all還原並計算資料
    //         for ($i=0;$i<count($source);$i++) {
    //             $source[$i] == '' ? $source[$i] = 'all' : '';
    //             for($y = $startYear; $y<= $finalYear; $y++){
    //                 $total[$i][$y]['orders'] = collect($total[$i][$y]['orders'])->sum();
    //                 $total[$i][$y]['money'] = collect($total[$i][$y]['money'])->sum();
    //             }
    //         }
    //         // dd($total);
    //     }else{

    //         $orders = OrderMonthlyTotalDB::whereIn('yyyymm',$yyyymm)
    //             ->select([
    //                 DB::raw('left(yyyymm,4) as year'),
    //                 'yyyymm',
    //                 DB::raw("SUM(pay_orders) as pay_orders"),
    //                 DB::raw("SUM(pay_money_total) as pay_money_total"),
    //                 DB::raw("SUM(ffeight_tariff_total) as ffeight_tariff_total"),
    //                 DB::raw("SUM(no_pay_orders) as no_pay_orders"),
    //                 DB::raw("truncate(SUM(pay_money_total) / SUM(pay_orders), 2) as avg_orders_money"),
    //                 'registered_num',
    //                 DB::raw("truncate(SUM(pay_money_total) / SUM(pay_orders),2) as avg"),
    //                 DB::raw("SUM(no_repeat_consumption) as no_repeat_consumption"),
    //             ])->groupBy('yyyymm')->orderBy('yyyymm', 'desc')->get()->groupBy('year')->all();
    //         foreach ($orders as $year => $values) {
    //             $pay_orders = $pay_money_total = $avg_orders_money = $ffeight_tariff_total = $no_pay_orders = $registered_num = $no_repeat_consumption = 0;
    //             foreach ($values as $order) {
    //                 $pay_orders += $order->pay_orders;
    //                 $pay_money_total += $order->pay_money_total;
    //                 $ffeight_tariff_total += $order->ffeight_tariff_total;
    //                 $no_pay_orders += $order->no_pay_orders;
    //                 $registered_num += $order->registered_num;
    //                 $no_repeat_consumption += $order->no_repeat_consumption;
    //             }
    //             if($pay_orders > 0 && $pay_money_total > 0){
    //                 $avg_orders_money = round($pay_money_total / $pay_orders,2);
    //             }
    //             $total[$year] = ['text' => $year.' 年統計', 'pay_orders' => $pay_orders, 'pay_money_total' => $pay_money_total, 'avg_orders_money' => $avg_orders_money, 'ffeight_tariff_total' => $ffeight_tariff_total, 'no_pay_orders' => $no_pay_orders, 'registered_num' => $registered_num, 'no_repeat_consumption' => $no_repeat_consumption];
    //         }
    //     }
    //     $sources = SourceDB::orderBy('name','asc')->get();
    //     $compact = array_merge($compact,['menuCode', 'orders', 'sources', 'total']);
    //     return view('admin.statistics.order_monthly_total',compact($compact));
    // }
}
