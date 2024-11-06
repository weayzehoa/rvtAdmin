<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Country as CountryDB;
use App\Models\User as UserDB;
use App\Models\UserAddress as UserAddressDB;
use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\OrderAsiamiles as OrderAsiamilesDB;
use App\Models\ShopcomOrder as ShopcomOrderDB;
use App\Models\TradevanOrder as TradevanOrderDB;
use DB;

trait OrderExportFunctionTrait
{
    protected function getOrderData($param)
    {
        if($param['type'] == 'Return'){
            $orders = OrderDB::with('user','shippingMethod','items','items.model');
        }elseif($param['type'] == 'PurchaseCall'){ //訂單狀態大於0的才能建立採購單(有付錢的)
            $orders = OrderDB::with('user','shippingMethod','items')->where('status','>',0);
        }else{
            $orders = OrderDB::with('user','shippingMethod','items');
        }
        if(isset($param['id'])){
            $orders = $orders->whereIn('id',$param['id']);
        }elseif(isset($param['con'])){
            //將進來的資料作參數轉換
            foreach ($param['con'] as $key => $value) {
                $$key = $value;
            }

            //狀態預設為1,2,3,4
            !empty($status) ? $orders = $orders->whereIn('status',explode(',',$status)) : $orders = $orders->whereIn('status',[1,2,3,4]);

            //查詢參數
            isset($booking) && $booking ? $orders = $orders->where('book_shipping_date', $booking) : '';
            isset($keytime) && $keytime ? $orders = $orders->where('receiver_key_time', 'like', "%$keytime%") : '';
            isset($order_number) && $order_number ? $orders = $orders->where('order_number', 'like', "%$order_number%") : '';
            isset($partner_order_number) && $partner_order_number ? $orders = $orders->where('partner_order_number', 'like', "%$partner_order_number%") : '';
            isset($shipping_number) && $shipping_number ? $orders = $orders->where('shipping_number', 'like', "%$shipping_number%") : '';
            isset($pay_time) && $pay_time ? $orders = $orders->where('pay_time', '>=', $pay_time) : '';
            isset($pay_time_end) && $pay_time_end ? $orders = $orders->where('pay_time', '<=', $pay_time_end) : '';
            isset($user_id) && $user_id ? $orders = $orders->where('user_id',$user_id) : '';
            isset($buyer_name) && $buyer_name ? $orders = $orders->where('buyer_name','like', "%$buyer_name%") : '';
            isset($buyer_phone) && $buyer_phone ? $orders = $orders->whereIn('user_id', UserDB::where('mobile','like',"%$buyer_phone%")->orWhere(DB::raw("CONCAT(nation,mobile)"),'like',"%$buyer_phone%")->select('id')->groupBy('id')->get()) : '';
            isset($receiver_name) && $receiver_name ? $orders = $orders->where('receiver_name','like', $receiver_name) : '';
            isset($receiver_tel) && $receiver_tel ? $orders = $orders->where('receiver_tel','like', "%$receiver_tel%") : '';
            isset($receiver_address) && $receiver_address ? $orders = $orders->where('receiver_address','like', "%$receiver_address%") : '';
            isset($created_at) && $created_at ? $orders = $orders->where('created_at', '>=', $created_at) : '';
            isset($created_at_end) && $created_at_end ? $orders = $orders->where('created_at', '<=', $created_at_end) : '';
            isset($shipping_time) && $shipping_time ? $orders = $orders->where('shipping_time', '>=', $shipping_time) : '';
            isset($shipping_time_end) && $shipping_time_end ? $orders = $orders->where('shipping_time', '<=', $shipping_time_end) : '';
            isset($invoice_time) && $invoice_time ? $orders = $orders->where('invoice_time', '>=', $invoice_time) : '';
            isset($invoice_time_end) && $invoice_time_end ? $orders = $orders->where('invoice_time', '<=', $invoice_time_end) : '';
            isset($domain) && $domain ? $orders = $orders->where('domain',$domain) : '';
            isset($promotion_code) && $promotion_code ? $orders = $orders->where('promotion_code',$promotion_code) : '';
            !empty($spend_point) ? strtoupper($spend_point) == 'X' ? $orders = $orders->where('spend_point','<=', 0) : $orders = $orders->where('spend_point','>=', 1) : '';
            !empty($is_discount) ? strtoupper($is_discount) == 'X' ? $orders = $orders->where('discount','=', 0) : $orders = $orders->where('discount','!=', 0) : '';
            !empty($pay_method) && $pay_method !='全部' ? $orders = $orders->whereIn('pay_method', explode(',', $pay_method)) : '';
            isset($shipping_method) && $shipping_method ? $orders = $orders->whereIn('shipping_method', explode(',', $shipping_method)) : '';
            isset($origin_country) && $origin_country ? $orders = $orders->whereIn('origin_country', explode(',', $origin_country)) : '';
            isset($vendor_name) && $vendor_name ? $orders = $orders->whereIn('id', OrderItemDB::where('vendor_name','like',"%$vendor_name%")->select('order_id')->groupBy('order_id')->get()) : '';
            isset($product_name) && $product_name ? $orders = $orders->whereIn('id', OrderItemDB::where('product_name','like',"%$product_name%")->select('order_id')->groupBy('order_id')->get()) : '';
            !empty($is_asiamiles) ? $is_asiamiles == 1 ? $orders = $orders->whereIn('id', OrderAsiamilesDB::select('order_id')->groupBy('order_id')->get()) : $orders = $orders->whereNotIn('id', OrderAsiamilesDB::select('order_id')->groupBy('order_id')->get()) : '';
            !empty($is_shopcom) ? $is_shopcom == 1 ? $orders = $orders->whereIn('id', ShopcomOrderDB::select('order_id')->groupBy('order_id')->get()) : $orders = $orders->whereNotIn('id', ShopcomOrderDB::select('order_id')->groupBy('order_id')->get()) : '';

            if(isset($shipping_vendor_name) && $shipping_vendor_name){
                if($shipping_vendor_name == '未分類'){
                    $orders = $orders->where(function ($query) {
                        $query->whereNull('shipping_memo')->orWhere('shipping_memo','')
                        ->orWhere('shipping_memo','[]');
                    });
                }elseif($shipping_vendor_name == '含多筆運單之訂單'){
                    $orders = $orders->where('shipping_memo','like',"%express_way%express_way%");
                }else{
                    $orders = $orders->where('shipping_memo','like', "%$shipping_vendor_name%");
                }
            };

            if (isset($keyword) && $keyword) {
                $userIds = UserDB:: where('id', $keyword)
                    ->orwhere('name', $keyword)
                    ->orwhere('mobile', 'like', "%$keyword%")->select('id')->distinct()->get();
                $userIds == '' || $userIds == null ? $userIds = [] : '';
                $orders = $orders->where(function ($query) use ($keyword, $userIds) {
                    $query->where('orders.order_number', 'like', "%$keyword%")
                    ->orWhere('receiver_name', $keyword)
                    ->orWhere('receiver_phone_number', 'like', "%$keyword%")
                    ->orWhere('receiver_tel', 'like', "%$keyword%")
                    ->orWhereIn('user_id', $userIds);
                });
            }

            isset($all_is_call) ?  $all_is_call == 'ALL' ? $is_call = 'ALL' : '' : '';
            if (!empty($is_call)) {
                if (strtoupper($is_call) == 'ALL') {
                    $orders = $orders->where('is_call', '!=', null);
                } elseif (strtoupper($is_call) == 'X') {
                    $orders = $orders->whereNull('is_call');
                } else {
                    $orders = $orders->where('is_call', $is_call);
                }
            }
            isset($all_is_print) ?  $all_is_print == 'ALL' ? $is_print = 'ALL' : '' : '';
            if (!empty($is_print)) {
                if (strtoupper($is_print) == 'ALL') {
                    $orders = $orders->where(function ($query) {
                        $query->whereNotNull('is_print')
                        ->where('is_print', '!=', '0');
                    });
                } elseif (strtoupper($is_print) == 'X') {
                    $orders = $orders->where(function ($query) {
                        $query->whereNull('is_print')
                        ->orWhere('is_print', '0');
                    });
                } else {
                    $orders = $orders->where('is_print', $is_print);
                }
            }

            isset($all_item_is_call) ?  $all_item_is_call == 'ALL' ? $item_is_call = 'ALL' : '' : '';
            if (!empty($item_is_call)) {
                if (strtoupper($item_is_call) == 'ALL') {
                    $orderItemOrderIds = OrderItemDB::where('is_call','!=',null);
                } elseif (strtoupper($item_is_call) == 'X') {
                    $orderItemOrderIds = OrderItemDB::whereNull('is_call');
                } else {
                    $orderItemOrderIds = OrderItemDB::where('is_call',$item_is_call);
                }
                $orderItemOrderIds = $orderItemOrderIds->select('order_id')->groupBy('order_id')->get();
                $orders = $orders->whereIn('id', $orderItemOrderIds);
            }

            if(isset($is_memo) && $is_memo == 1){
                $orders = $orders->where(function ($query) {
                    $query->whereNotNull('admin_memo')->orWhereNotNull('user_memo');
                });
            }

            if(isset($memo) && $memo){
                $orders = $orders->where(function ($query) use ($memo) {
                    $query->where('admin_memo','like',"%$memo%")->orWhere('user_memo','like',"%$memo%");
                });
            }

            if(isset($channel_order) && $channel_order){
                if($channel_order=="iCarry"){
                    $orders = $orders->where(function ($query) use ($channel_order) {
                        $query->where('domain','icarry.me')->orWhere('domain','m.icarry.me')->orWhere('domain','www.icarry.me')->orWhere('domain','mobile.icarry.me');
                    })->where([
                        ['user_memo','not like','%支付寶小程序訂單%'],
                        ['user_memo','not like','%buyand%'],
                        ['admin_memo','not like','%支付寶小程序訂單%'],
                        ['admin_memo','not like','%buyandship%'],
                    ])->whereNotIn('id',ShopcomOrderDB::select('order_id')->groupBy('order_id')->get())
                    ->whereNotIn('id',TradevanOrderDB::select('order_id')->groupBy('order_id')->get());
                }else if($channel_order=="alipay_applet"){
                    $orders = $orders->where(function ($query) use ($channel_order) {
                        $query->where('user_memo','like','支付寶小程序訂單')->orWhere('admin_memo','like','%支付寶小程序訂單%');
                    });
                }else if($channel_order=="klook"){
                    $orders = $orders->where('pay_method','客路');
                }else if($channel_order=="shopee_tw"){
                    $orders = $orders->where('user_memo','like','%蝦皮訂單：(台灣)%');
                }else if($channel_order=="shopee_sg"){
                    $orders = $orders->where('user_memo','like','%蝦皮訂單：(新加坡)%');
                }else if($channel_order=="shopee_my"){
                    $orders = $orders->where('user_memo','like','%蝦皮訂單：(馬來西亞)%');
                }else if($channel_order=="is_shop_com"){
                    $orders = $orders->whereIn('id',ShopcomOrderDB::select('order_id')->groupBy('order_id')->get());
                }else if($channel_order=="is_tradevan"){
                    $orders = $orders->whereIn('id',TradevanOrderDB::select('order_id')->groupBy('order_id')->get());
                }else if($channel_order=="Ctrip"){
                    $orders = $orders->where('pay_method','Ctrip');
                }else if($channel_order=="go2tw"){
                    $orders = $orders->where('domain','like','%go2tw%');
                }else if($channel_order=="KKday"){
                    $orders = $orders->where('pay_method','KKday');
                }else if($channel_order=="buyandship"){
                    $orders = $orders->where('user_memo','like','%buyandship%');
                }else if($channel_order=="Jetfi"){
                    $orders = $orders->where('domain','jetfi.icarry.me');
                }else if($channel_order=="yirui"){
                    $orders = $orders->where('pay_method','宜睿');
                }
            }
            if(isset($book_shipping_date_not_fill) && $book_shipping_date_not_fill == 1){
                $book_shipping_date = '';
                $book_shipping_date_end = '';
                $orders = $orders->whereNull('book_shipping_date');
            }

            !empty($book_shipping_date) ? $orders = $orders->where('book_shipping_date', '>=', $book_shipping_date) : '';
            !empty($book_shipping_date_end) ? $orders = $orders->where('book_shipping_date', '<=', $book_shipping_date_end) : '';

            !empty($receiver_key_time) ? $orders = $orders->where('receiver_key_time', '>=', $receiver_key_time) : '';
            !empty($receiver_key_time_end) ? $orders = $orders->where('receiver_key_time', '<=', $receiver_key_time_end) : '';
        }
        if($param['type'] == 'SFSpeedType' || $param['type'] == 'GoodMaji' || $param['type'] == 'SFWarehousing' || $param['type'] == 'Digiwin' || $param['type'] == 'Asiamiles'){
            $orders = $orders->select('id')->orderBy('created_at','desc')->get()->pluck('id')->all();
        }elseif($param['type'] == 'SF2'){ //只取訂單號碼及ID
            $orders = $orders->select(['id','order_number'])->orderBy('created_at','desc')->get();
        }elseif($param['type'] == 'DHL'){
            $orders = $orders->select([
                'orders.*',
                'user_name' => UserDB::whereColumn('users.id','orders.user_id')->select('name')->limit(1),
                'receiver_country' => CountryDB::whereColumn('countries.id','orders.to')
                                    ->select('lang')->limit(1),
                'zip_code' => UserAddressDB::whereColumn([['user_addresses.user_id','orders.user_id'],['user_addresses.name','orders.receiver_name'],['user_addresses.phone','orders.receiver_tel'],['user_addresses.email','orders.receiver_email']])
                            ->select('zip_code')->limit(1),
            ])->orderBy('created_at','desc')->get();
        }else{
            $orders = $orders->select([
                'orders.*',
                'user_name' => UserDB::whereColumn('users.id','orders.user_id')->select('name')->limit(1),
            ]);
            $orders = $orders->orderBy('created_at','desc')->get();
        }
        return $orders;
    }

    protected function statusText($str){
        switch($str){
            case -1:return "後台取消訂單";break;
            case 0:return "尚未付款";break;
            case 1:return "已付款待出貨";break;
            case 2:return "訂單集貨中";break;
            case 3:return "訂單已出貨";break;
            case 4:return "訂單已完成";break;
        }
    }

    protected function checkNation($address){
        $blocks=[
            "中國","中国","北京","天津","上海","重慶","河北","山西","蒙古","遼寧","吉林",
            "黑龍江","江蘇","浙江","安徽","福建","江西","山東","河南","湖北","湖南","廣東",
            "廣西","海南","四川","貴州","雲南","陝西","甘肅","青海","西藏","寧夏","新疆",
            "重庆","辽宁","黑龙江","江苏","山东","广东","广西","贵州","云南","陕西","甘肃",
            "宁夏","台灣","澳門","新加坡","馬來西亞","香港","台湾","澳门","马来西亚",
            "HONG KONG","MACAU","Singapore","MALAYSIA"
        ];
        foreach($blocks as $b){
            if(stristr($address,$b)){
                return $b;
                break;
            }
        }
        return "台灣";
    }

    protected function country($receiver_address,$shipping_method,$receiver_tel){
        if(strstr($receiver_address,"中國")){
            return "中國";
        }else if(strstr($receiver_address,"香港") || strstr($receiver_address,"HK")){
            return "香港";
        }else if(strstr($receiver_address,"新加坡") || strstr($receiver_address,"SG")){
            return "新加坡";
        }else if(strstr($receiver_address,"馬來西亞")){
            return "馬來西亞";
        }else if(strstr($receiver_address,"台灣") || strstr($receiver_address,"7-11")){
            return "馬來西亞"; //其實是台灣公司叫的
        }
        if($shipping_method == "寄送台灣" || $shipping_method == "現場提貨"){
            return "馬來西亞"; //其實是台灣公司叫的
        }
        $receiver_tel = str_replace("+","",$receiver_tel);//先去掉+
        $receiver_tel = str_replace("-","",$receiver_tel);//先去掉-
        $receiver_tel_tmp = substr($receiver_tel,0,2);//判斷是否韓國(據說韓國人很多)
        if($receiver_tel_tmp == "82"){
            return "韓國";
        }else if($receiver_tel_tmp == "81"){
            return "日本";
        }else if($receiver_tel_tmp == "84"){
            return "越南";
        }else if($receiver_tel_tmp == "60"){
            return "馬來西亞";
        }else if($receiver_tel_tmp == "66"){
            return "泰國";
        }else if($receiver_tel_tmp == "62"){
            return "印尼";
        }else if($receiver_tel_tmp == "63"){
            return "菲律賓";
        }else if($receiver_tel_tmp == "91"){
            return "印度";
        }else if($receiver_tel_tmp == "86"){
            return "中國";
        }
        $receiver_tel_tmp = substr($receiver_tel,0,3);//判斷是否韓國(據說韓國人很多)
        if($receiver_tel_tmp == "852"){
            return "香港";
        }else if($receiver_tel_tmp == "853"){
            return "澳門";
        }else if($receiver_tel_tmp == "886"){
            return "馬來西亞"; //其實是台灣公司叫的
        }
        return "馬來西亞";//未知 直接丟馬來西亞
    }
    protected function phoneChange($phone)
    {
        //好巴這邊為何不用 str_replace 來直接取代掉886 因為你不知道是頭吃886
        //還是手機號碼吃886為了保險就只檢查表頭3碼再取表頭三碼以外的  這樣做法比較好
        //這邊由於格式超級不固定... 要去掉+ 886但有時候 886後面還有空白也要去掉
        $receiver_tel  = str_replace('+','',$phone);//先去掉+
        $receiver_tel_lengh = strlen($receiver_tel);//先取得長度(擷取使用)用這是保險
        $receiver_tel_tmp = '';
        $str_tmp = $receiver_tel_lengh-3;//台灣是三碼
            $receiver_tel_tmp = substr($receiver_tel,0,"-{$str_tmp}");
            if($receiver_tel_tmp=='886'){//就是香港
                $receiver_tel = substr($receiver_tel,3);//擷取完畢
            }
        $receiver_tel  = str_replace(' ','',$receiver_tel);//去除有空格的(有些+XXX 的問題)
        return $receiver_tel;
    }

    protected function receiverCountry($address,$memo){
        if(strstr($memo,'蝦皮訂單：(新加坡)')){
            return 'SG';
        }else if(strstr($address,'日本') || strstr($address,'JP')){
            return 'JP';
        }else if(strstr($address,'馬來西亞') || strstr($address,'MY')){
            return 'MY';
        }else if(strstr($address,'新加坡') || strstr($address,'SG')){
            return 'SG';
        }else{
            return '';
        }
    }

    protected function serverCode($address,$memo){
        if(strstr($memo,'蝦皮訂單：(新加坡)')){
            return 'SGETKSG';
        }else if(strstr($address,'日本') || strstr($address,'JP')){
            return 'JPEMSEP';
        }else if(strstr($address,'馬來西亞') || strstr($address,'MY')){
            return 'MYETKMY';
        }else if(strstr($address,'新加坡') || strstr($address,'SG')){
            return 'SGETKSG';
        }else{
            return '';
        }
    }

    protected function getDayWithWeek($str){
        $d=strtotime($str);
        $w=date("w",$d);
        $ary=explode(" ","日 一 二 三 四 五 六");
        $str=date("n/j",$d)."(".$ary[$w].")";
        return $str;
    }

    protected function checkNation2($str){
        $blocks = [
            '中國',
            '中国',
            '台灣',
            '澳門',
            '新加坡',
            '馬來西亞',
            '香港',
            '台湾',
            '澳门',
            '马来西亚',
            'HONG KONG',
            'MACAU',
            'Singapore',
            'MALAYSI',
        ];
        foreach($blocks as $b){
            if(stristr($str,$b)){
                return $b;
                break;
            }
        }
        return '台灣';
    }
}
