<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\OrderLog as OrderLogDB;
use App\Models\OrderAsiamiles as OrderAsiamileDB;
use App\Models\OrderPromotion as OrderPromotionDB;
use App\Models\OrderVendorShipping as OrderVendorShippingDB;
use App\Models\OrderShipping as OrderShippingDB;
use App\Models\Promotion as PromotionDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\PayMethod as PayMethodDB;
use App\Models\ShopcomOrder as ShopcomOrderDB;
use App\Models\TradevanOrder as TradevanOrderDB;
use App\Models\ShopeeOrder as ShopeeOrderDB;
use App\Models\Country as CountryDB;
use App\Models\Pay2go as Pay2goDB;
use App\Models\Spgateway as SpgatewayDB;
use App\Models\OrderDailyTotal as OrderDailyTotalDB;
use App\Models\OrderMonthlyTotal as OrderMonthlyTotalDB;
use App\Models\ShippingMonthlyTotal as ShippingMonthlyTotalDB;
use App\Models\Alipay as AlipayDB;
use App\Models\Source as SourceDB;
use App\Models\User as UserDB;
use App\Models\OrderStatistic as OrderStatisticDB;
use App\Models\ShipmentLog as ShipmentLogDB;
use App\Models\SmsSchedule as SmsScheduleDB;
use App\Models\TashinCreditcard as TashinCreditcardDB;
use DB;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // if (env('DB_MIGRATE_PAY_METHODS')) {
        //     $data = [
        //         ['name' => '信用卡', 'name_en' => 'Credit Card', 'value' => '智付通信用卡', 'type' => '信用卡', 'is_on' => 1, 'sort' => 1],
        //         ['name' => 'ATM轉帳(限台灣地區)', 'name_en' => 'ATM Transfer (Taiwan Only)', 'value' => '智付通ATM', 'type' => 'ATM', 'is_on' => 1, 'sort' => 2],
        //         ['name' => '超商代碼付款(限台灣地區)', 'name_en' => 'CVS Pin Code (Taiwan Only)', 'value' => '智付通CVS', 'type' => 'CVS', 'is_on' => 1, 'sort' => 3],
        //         ['name' => '支付寶', 'name_en' => 'Alipay', 'value' => '玉山支付寶', 'type' => '支付寶', 'is_on' => 1, 'sort' => 4],
        //         // ['name' => '玉山行動銀行', 'name_en' => 'E.Sun Mobile Banking', 'value' => '玉山行動銀行', 'type' => '行動銀行', 'is_on' => 0, 'sort' => 5],
        //         ['name' => '銀聯卡', 'name_en' => 'Union Pay', 'value' => '台新銀聯卡', 'type' => '銀聯卡', 'is_on' => 1, 'sort' => 6],
        //         // ['name' => '台灣Pay', 'name_en' => 'Taiwan Pay', 'value' => '台灣Pay', 'type' => '行動銀行', 'is_on' => 0, 'sort' => 7],
        //     ];
        //     for($i=0;$i<count($data);$i++){
        //         PayMethodDB::create($data[$i]);
        //     }
        // }

        if (env('DB_MIGRATE_PAY_METHODS')) {
            // Pay Method 遷移
            $data = [];
            $oldPayMethods = DB::connection('icarryOld')->table('pay_method')->orderBy('id','asc')->get();
            $x = 1;
            foreach ($oldPayMethods as $oldPayMethod) {
                $typeArray = ['信用卡','支付寶','銀聯卡','ATM','CVS','行動銀行'];
                $type = '其它';
                // $array = ['智付通信用卡' => '信用卡', '玉山信用卡' => '綁定信用卡', '玉山行動銀行' => '玉山行動銀行', '台灣pay' => '台灣Pay', '智付通ATM' => 'ATM轉帳(限台灣地區)','智付通CVS' => '超商代碼付款(限台灣地區)','台新銀聯卡' => '銀聯卡','玉山支付寶' => '支付寶'];
                $array = ['智付通信用卡' => '信用卡', '智付通ATM' => 'ATM轉帳(限台灣地區)','智付通CVS' => '超商代碼付款(限台灣地區)','台新銀聯卡' => '銀聯卡','玉山支付寶' => '支付寶'];
                $enArray = ['智付通信用卡' => 'Credit Card', '玉山信用卡' => 'Bind Credit Card', '玉山行動銀行' => 'E.Sun Mobile Banking', '台灣pay' => 'Taiwan Pay', '智付通ATM' => 'ATM Transfer (Taiwan Only)','智付通CVS' => 'CVS Pin Code (Taiwan Only)','台新銀聯卡' => 'Union Pay','玉山支付寶' => 'Alipay'];
                for($i=0;$i<count($typeArray);$i++){
                    if(strstr($oldPayMethod->name,$typeArray[$i])){
                        $type = $typeArray[$i];
                    }
                }
                $oldPayMethod->name == '台灣pay' ? $type = '行動銀行': '';
                $type == '其它' ? $sort = 9999 + $x : $sort = $x;
                if(in_array($oldPayMethod->name, array_keys($array))){
                    $name = $array[$oldPayMethod->name];
                    $nameEn = $enArray[$oldPayMethod->name];
                    $value = $oldPayMethod->name;
                    $isOn = 1;
                }else{
                    $nameEn = $value = $name = $oldPayMethod->name;
                    $isOn = 0;
                }
                $data[] = [
                    // 'old_erp_id' => $oldPayMethod->id,
                    'name' => $name,
                    'name_en' => $nameEn,
                    'value' => $value,
                    'type' => $type,
                    'is_on' => $isOn,
                    'sort' => $sort,
                ];
                $x++;
            }
            $chunks = array_chunk($data, 5000);
            foreach($chunks as $chunk){
                PayMethodDB::insert($chunk);
            }
        }

        if (env('DB_MIGRATE_ORDERS')) {
            //Order 遷移
            $data = [];
            $countries = CountryDB::all();
            $oldMessages = DB::connection('icarryOld')->table('vendor_message')->get();
            $subQuery = DB::connection('icarryOld')->table('orders');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(800, function ($oldOrders) use($countries,$oldMessages) {
                $data = [];
                foreach($oldOrders as $oldOrder){
                    $oldOrder->is_print == null ? $oldOrder->is_print = 0 : '';
                    $oldOrder->is_del == null ? $oldOrder->is_del = 0 : '';
                    $oldOrder->is_invoice == null ? $oldOrder->is_invoice = 0 : '';
                    $oldOrder->invoice_type == null ? $oldOrder->invoice_type = 0 : '';
                    $oldOrder->invoice_sub_type == null ? $oldOrder->invoice_sub_type = 0 : '';
                    $oldOrder->receiver_key_time == '0000-00-00 00:00:00' ? $oldOrder->receiver_key_time = null : '';
                    $oldOrder->pay_time == '0000-00-00 00:00:00' ? $oldOrder->pay_time = null : '';
                    $oldOrder->shipping_time == '0000-00-00 00:00:00' ? $oldOrder->shipping_time = null : '';
                    $oldOrder->create_time == '0000-00-00 00:00:00' ? $oldOrder->create_time = null : '';
                    $oldOrder->update_time == '0000-00-00 00:00:00' ? $oldOrder->update_time = null : '';
                    $oldOrder->invoice_time == '0000-00-00 00:00:00' ? $oldOrder->invoice_time = null : '';
                    $oldOrder->invoice_time == '0000-00-00 00:00:00' ? $oldOrder->invoice_time = null : '';
                    $oldOrder->book_shipping_date == '0000-00-00' ? $oldOrder->book_shipping_date = null : '';
                    $oldOrder->promotion_code == '' ? $oldOrder->promotion_code = null : '';
                    $oldOrder->buyer_name == 'null' ? $oldOrder->buyer_name = null : '';
                    $oldOrder->shipping_memo = str_replace(array('	','[]'),array('',''), $oldOrder->shipping_memo);

                    $partnerCountry = '';
                    if ($oldOrder->create_type == 'shopee' && $oldOrder->partner_order_number) {
                        $ordersn = $oldOrder->partner_order_number;
                        $tmp = DB::connection('icarryOld')->table('shopee_orders')->where('ordersn', $ordersn)->first();
                        if (!empty($tmp)) {
                            $partnerCountry = $tmp->country;
                        } else {
                            if (strpos($oldOrder->user_memo, '台灣') !== false ) {
                                $partnerCountry = 'TW';
                            } elseif (strpos($oldOrder->user_memo, '新加坡') !== false) {
                                $partnerCountry = 'SG';
                            } elseif (strpos($oldOrder->user_memo, '馬來西亞') !== false) {
                                $partnerCountry = 'MY';
                            } else {
                                $partnerCountry = '';
                            }
                        }
                        $partnerCountry == 'SG' ? $oldOrder->ship_to = '新加坡' : '';
                        $partnerCountry == 'MY' ? $oldOrder->ship_to = '馬來西亞' : '';
                        $partnerCountry == 'TW' ? $oldOrder->ship_to = '台灣' : '';
                    }

                    //國家代號對應
                    $oldOrder->origin_country = str_replace(array('台澎金马关税区','日本(台灣發貨)'), array('台灣','台灣'), $oldOrder->origin_country);
                    $oldOrder->ship_to = str_replace(array('南韓','泰國-曼谷'), array('韓國','泰國'), $oldOrder->ship_to);
                    $oldOrder->receiver_address = str_replace(array('南韓','泰國-曼谷'), array('韓國','泰國'), $oldOrder->receiver_address);

                    $tmp = [];
                    $tmp = CountryDB::where('name', 'like', "%$oldOrder->origin_country%")->select('id')->first();
                    !empty($tmp) ? $from = $tmp->id : $from = 0;

                    //找出國家名稱
                    $oldCountry = explode(' ', $oldOrder->receiver_address)[0];
                    $tmp2 = CountryDB::where('name', 'like', "%$oldCountry%")->first();
                    isset($tmp2) ? $to = $tmp2->id : $to = 0;

                    if ($to == 0) {
                        if ($oldOrder->create_type == '17life') { //全部台灣
                            $to = 1;
                            $oldOrder->ship_to == '' ? $oldOrder->ship_to = '台灣' : '';
                        } elseif ($oldOrder->create_type == 'alipay') { //全部台灣
                            $to = 1;
                            $oldOrder->ship_to == '' ? $oldOrder->ship_to = '台灣' : '';
                        } elseif ($oldOrder->create_type == '天虹') { //全部中國
                            $to = 2;
                            $oldOrder->ship_to == '' ? $oldOrder->ship_to = '中國' : '';
                        } elseif ($oldOrder->create_type == 'app') { //大部分都是中國 香港 台灣 可以用shipto直接判斷
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 0;
                        } elseif ($oldOrder->create_type == 'Asiamiles' || $oldOrder->create_type == 'asiamiles') { //香港與台灣 用shipto->剩下都是香港
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 3;
                        } elseif ($oldOrder->create_type == 'Ctrip') { //全部台灣
                            $to = 1;
                            $oldOrder->ship_to == '' ? $oldOrder->ship_to = '台灣' : '';
                        } elseif ($oldOrder->create_type == 'ezfly') { //全部台灣
                            $to = 1;
                            $oldOrder->ship_to == '' ? $oldOrder->ship_to = '台灣' : '';
                        } elseif ($oldOrder->create_type == 'hutchgo') { //全部是香港
                            $to = 3;
                            $oldOrder->ship_to == '' ? $oldOrder->ship_to = '香港' : '';
                        } elseif ($oldOrder->create_type == 'iii') { //全部是台灣
                            $to = 1;
                            $oldOrder->ship_to == '' ? $oldOrder->ship_to = '台灣' : '';
                        } elseif ($oldOrder->create_type == 'kiosk') { //用shipto區分
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 0;
                        } elseif ($oldOrder->create_type == 'klook' || $oldOrder->create_type == '客路') { //用shipto區分
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 1;
                        } elseif ($oldOrder->create_type == 'KKday') { //用shipto區分
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 1;
                        } elseif ($oldOrder->create_type == 'momo') { //用shipto區分
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 1;
                        } elseif ($oldOrder->create_type == 'vendor') { //用shipto區分
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 0;
                        } elseif ($oldOrder->create_type == '生活市集') { //用shipto區分
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 0;
                        } elseif ($oldOrder->create_type == 'shopee') { //用shipto區分 剩下的用關鍵字
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 0;
                            //如果還是0使用關鍵字查詢
                            if ($to==0) {
                                if($partnerCountry == 'SG'){ //新加坡
                                    $to = 7;
                                } elseif ($partnerCountry == 'MY') { //馬來西亞
                                    $to = 8;
                                } elseif ($partnerCountry == 'TW') { //台灣
                                    $to = 1;
                                } else{
                                    $to = 0;
                                }
                            }
                        } elseif ($oldOrder->create_type == 'web') { //用shipto區分
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 0;
                        } elseif ($oldOrder->create_type == 'yirui') { //全部台灣
                            $to = 1;
                        } elseif ($oldOrder->create_type == '其他商城') { //用shipto區分
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 0;
                        } elseif ($oldOrder->create_type == 'admin') { //用shipto區分
                            $tmp3 = CountryDB::where('name', 'like', "%$oldOrder->ship_to%")->first();
                            isset($tmp3) ? $to = $tmp3->id : $to = 0;
                        } elseif ($oldOrder->create_type == '' || $oldOrder->ship_to == '台灣') {
                            $to = 1;
                        }
                    }

                    //修正ship_to欄位資料
                    if ($to != 0) {
                        foreach ($countries as $country) {
                            $country->id == $to ? $oldOrder->ship_to = $country->name : '';
                        }
                    }

                    $oldOrder->create_type == '' ? $oldOrder->create_type = 'web' : '';
                    empty($oldOrder->receiver_birthday) ? $oldOrder->receiver_birthday = null : '';

                    //分拆蝦皮訂單
                    if($oldOrder->create_type == 'shopee'){
                        if(strpos($oldOrder->user_memo,'蝦皮訂單：(台灣)') !== false){
                            $createType = 'shopee_tw';
                        }elseif(strpos($oldOrder->user_memo,'蝦皮訂單：(新加坡)') !== false){
                            $createType = 'shopee_sg';
                        }elseif(strpos($oldOrder->user_memo,'蝦皮訂單：(馬來西亞)') !== false){
                            $createType = 'shopee_my';
                        }else{
                            $createType = 'shopee_tw';
                        }
                    }else{
                        $createType = $oldOrder->create_type;
                    }

                    $vendorMemo = null;
                    foreach ($oldMessages as $oldMessage) {
                        $oldMessage->order_id == $oldOrder->id ? $vendorMemo = $oldMessage->message : '';
                    }

                    //排除 user_id = 0 與 shipping_method = 0 的資料
                    if($oldOrder->user_id != 0){
                        if($oldOrder->shipping_method != 0){
                            $data[] = [
                                'id' => $oldOrder->id,
                                'order_number' => $oldOrder->order_number,
                                'user_id' => $oldOrder->user_id,
                                'origin_country' => $oldOrder->origin_country,
                                'from' => $from,
                                'ship_to' => $oldOrder->ship_to,
                                'to' => $to,
                                'book_shipping_date' => $oldOrder->book_shipping_date,
                                'receiver_name' => $oldOrder->receiver_name,
                                'receiver_id_card' => $oldOrder->receiver_id_card,
                                'receiver_nation_number' => $oldOrder->receiver_nation_number,
                                'receiver_phone_number' => $oldOrder->receiver_phone_number,
                                'receiver_tel' => $oldOrder->receiver_tel,
                                'receiver_email' => $oldOrder->receiver_email,
                                'receiver_address' => $oldOrder->receiver_address,
                                'receiver_birthday' => $oldOrder->receiver_birthday,
                                'receiver_province' => $oldOrder->receiver_province,
                                'receiver_city' => $oldOrder->receiver_city,
                                'receiver_area' => $oldOrder->receiver_area,
                                'receiver_zip_code' => $oldOrder->receiver_zip_code,
                                'receiver_keyword' => $oldOrder->receiver_keyword,
                                'receiver_key_time' => $oldOrder->receiver_key_time,
                                'shipping_method' => $oldOrder->shipping_method,
                                'invoice_time' => $oldOrder->invoice_time,
                                'invoice_type' => $oldOrder->invoice_type,
                                'invoice_sub_type' => $oldOrder->invoice_sub_type,
                                'invoice_number' => $oldOrder->invoice_number,
                                'invoice_title' => $oldOrder->invoice_title,
                                'invoice_address' => $oldOrder->invoice_address,
                                'spend_point' => abs($oldOrder->spend_point),
                                'amount' => $oldOrder->amount,
                                'shipping_fee' => $oldOrder->shipping_fee,
                                'parcel_tax' => $oldOrder->parcel_tax,
                                'pay_method' => $oldOrder->pay_method,
                                'get_point' => $oldOrder->get_point,
                                'exchange_rate' => $oldOrder->exchange_rate,
                                'shipping_number' => $oldOrder->shipping_number,
                                'shipping_memo' => $oldOrder->shipping_memo,
                                'promotion_code' => $oldOrder->promotion_code,
                                'discount' => $oldOrder->discount,
                                'admin_memo' => $oldOrder->admin_memo,
                                'user_memo' => $oldOrder->user_memo,
                                'vendor_memo' => $vendorMemo,
                                'partner_order_number' => $oldOrder->partner_order_number,
                                'partner_country' => $partnerCountry,
                                'pay_time' => $oldOrder->pay_time,
                                'buyer_name' => $oldOrder->buyer_name,
                                'buyer_email' => $oldOrder->buyer_email,
                                'buyer_id_card' => $oldOrder->new_receiver_id_card,
                                'carrier_type' => $oldOrder->carrier_type,
                                'carrier_num' => $oldOrder->carrier_num,
                                'love_code' => $oldOrder->love_code,
                                'print_flag' => $oldOrder->print_flag,
                                'shipping_time' => $oldOrder->shipping_time,
                                'buy_memo' => $oldOrder->buy_memo,
                                'billOfLoading_memo' => $oldOrder->billOfLoading_memo,
                                'special_memo' => $oldOrder->special_memo,
                                'new_shipping_memo' => $oldOrder->new_shipping_memo,
                                'tax_refund' => $oldOrder->tax_refund,
                                'domain' => $oldOrder->domain,
                                'create_type' => $createType,
                                'create_id' => $oldOrder->create_id,
                                'is_invoice_no' => $oldOrder->is_invoice_no,
                                'china_id_img1' => $oldOrder->china_id_img1,
                                'china_id_img2' => $oldOrder->china_id_img2,
                                'is_del' => $oldOrder->is_del,
                                'is_call' => $oldOrder->is_call,
                                'is_print' => $oldOrder->is_print,
                                'is_invoice' => $oldOrder->is_invoice,
                                'status' => $oldOrder->status,
                                'created_at' => $oldOrder->create_time,
                                'updated_at' => $oldOrder->update_time,
                            ];
                        }
                    }
                    if (env('DB_MIGRATE_ORDER_SHIPPINGS')) {
                        //物流資料遷移
                        $data2 = [];
                        if ($oldOrder->shipping_memo) {
                            $shippings = json_decode(str_replace('	', '', $oldOrder->shipping_memo));
                            foreach ($shippings as $shipping) {
                                $shipping->create_time == '1970/01/01 08:00:00' ? $shipping->create_time = null : '';
                                $data2[] = [
                                    'order_id' => $oldOrder->id,
                                    'express_way' => $shipping->express_way,
                                    'express_no' => $shipping->express_no,
                                    'created_at' => $shipping->create_time,
                                ];
                            }
                        }
                        OrderShippingDB::insert($data2);
                    }
                }
                OrderDB::insert($data);
            });

            OrderDB::select(['id','is_del'])->chunk(10000, function($orders)
            {
                foreach($orders as $order){
                    $order->is_del == 1 ? $order->delete() : '';
                }
            });

            echo "Order 遷移完成\n";
        }

        if (env('DB_MIGRATE_ORDER_ITEMS')) {
            //Order Item 資料移轉
            $subQuery = DB::connection('icarryOld')->table('order_item');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(2000, function ($oldOrderItems) {
                $data = [];
                foreach ($oldOrderItems as $oldOrderItem) {
                    $tmp = [];
                    if ($oldOrderItem->order_id && $oldOrderItem->order_id > 0 && $oldOrderItem->product_model_id > 0) {
                        $tmp = ProductModelDB::join('products', 'products.id', 'product_models.product_id')
                        ->join('product_unit_names', 'product_unit_names.id', 'products.unit_name_id')
                        ->join('vendors', 'vendors.id', 'products.vendor_id')
                        ->where('product_models.id', $oldOrderItem->product_model_id)
                        ->select(
                            'product_models.sku as sku',
                            'product_models.product_id as product_id',
                            'vendors.id as vendor_id',
                            'vendors.name as vendor_name',
                            'product_unit_names.name as unit_name',
                            'products.name as product_name',
                            'products.model_type as model_type',
                        )->withTrashed()->first();
                        if (!empty($tmp)) {
                            $sku = $tmp->sku;
                            $productId = $tmp->product_id;
                            $vendorId = $tmp->vendor_id;
                            $vendorName = $tmp->vendor_name;
                            $unitName = $tmp->unit_name;
                            $productName = $tmp->product_name;
                        } else {
                            $sku = null;
                            $productId = 0;
                            $vendorId = 0;
                            $vendorName = null;
                            $unitName = null;
                            $productName = null;
                        }
                        $oldOrderItem->product_name == '' || $oldOrderItem->product_name == null ? $oldOrderItem->product_name = $productName : '';
                        if ($vendorId > 0) {
                            $data[] = [
                                'order_id' => $oldOrderItem->order_id,
                                'product_model_id' => $oldOrderItem->product_model_id,
                                'product_id' => $productId,
                                'vendor_id' => $vendorId,
                                'vendor_name' => $vendorName,
                                'sku' => $sku,
                                'price' => $oldOrderItem->price,
                                'unit_name' => $unitName,
                                'gross_weight' => $oldOrderItem->gross_weight,
                                'net_weight' => $oldOrderItem->net_weight,
                                'quantity' => $oldOrderItem->quantity,
                                'vendor_service_fee_percent' => $oldOrderItem->vendor_service_fee_percent,
                                'shipping_verdor_percent' => $oldOrderItem->shipping_verdor_percent,
                                'product_service_fee_percent' => $oldOrderItem->product_service_fee_percent,
                                'admin_memo' => $oldOrderItem->admin_memo,
                                'promotion_id' => $oldOrderItem->promotion_ids,
                                'product_name' => $oldOrderItem->product_name,
                                'is_tax_free' => $oldOrderItem->is_tax_free,
                                'is_del' => $oldOrderItem->is_del,
                                'is_call' => $oldOrderItem->is_call,
                                'created_at' => $oldOrderItem->create_time,
                            ];
                        }
                    }
                }
                orderItemDB::insert($data);
            });

            OrderItemDB::select(['id','is_del'])->chunk(10000, function($orderItems)
            {
                foreach($orderItems as $orderItem){
                    $orderItem->is_del == 1 ? $orderItem->delete() : '';
                }
            });
            echo "Order Item 遷移完成\n";
        }

        if (env('DB_MIGRATE_ORDER_ASIAMILES')) {
            //Order Asiamiles 資料移轉
            $subQuery = DB::connection('icarryOld')->table('order_asiamiles');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.order_id','asc')->chunk(10000, function ($oldOrderAsiamiles) {
                $data = [];
                foreach ($oldOrderAsiamiles as $oldOrderAsiamile) {
                    $data[] = [
                        'order_id' => $oldOrderAsiamile->order_id,
                        'asiamiles_account' => $oldOrderAsiamile->asiamiles_account,
                        'asiamiles_name' => $oldOrderAsiamile->asiamiles_name,
                        'asiamiles_last_name' => $oldOrderAsiamile->asiamiles_last_name,
                    ];
                }
                OrderAsiamileDB::insert($data);
            });
            echo "Order Asiamiles 遷移完成\n";
        }

        if (env('DB_MIGRATE_ORDER_PROMOTIONS')) {
            //Order with Promotion 資料移轉
            $subQuery = DB::connection('icarryOld')->table('order_with_promotion');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.order_id','asc')->chunk(10000, function ($oldOrderPromotions) {
                $data = [];
                foreach ($oldOrderPromotions as $oldOrderPromotion) {
                    $data[] = [
                        'order_id' => $oldOrderPromotion->order_id,
                        'promotion_ids' => $oldOrderPromotion->promotion_ids,
                    ];
                }
                OrderPromotionDB::insert($data);
            });
            echo "Order with Promotion 遷移完成\n";
        }

        if (env('DB_MIGRATE_ORDER_VENDOR_SHIPPINGS')) {
            //Order with Vendor 資料移轉
            $subQuery = DB::connection('icarryOld')->table('order_with_vendor');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(10000, function ($oldOrderVendors) {
                $data = [];
                foreach ($oldOrderVendors as $oldOrderVendor) {
                    if ($oldOrderVendor->vendor_id) {
                        $data[] = [
                            'order_id' => $oldOrderVendor->order_id,
                            'vendor_id' => $oldOrderVendor->vendor_id,
                            'express_way' => $oldOrderVendor->express_way,
                            'express_no' => $this->makeSemiangle($oldOrderVendor->express_no),
                            'created_at' => $oldOrderVendor->create_time,
                        ];
                    }
                }
                OrderVendorShippingDB::insert($data);
            });
            echo "Order with Vendor 遷移完成\n";
        }

        if (env('DB_MIGRATE_PROMOTIONS')) {
            //Promotion 資料移轉
            $subQuery = DB::connection('icarryOld')->table('promotion');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(3000, function ($oldPromotions) {
                $data = [];
                foreach ($oldPromotions as $oldPromotion) {
                    $data[] = [
                        'name' => $oldPromotion->name,
                        'name_en' => $oldPromotion->name_en,
                        'name_jp' => $oldPromotion->name_jp,
                        'name_kr' => $oldPromotion->name_kr,
                        'name_th' => $oldPromotion->name_th,
                        'intro' => $oldPromotion->intro,
                        'intro_en' => $oldPromotion->intro_en,
                        'intro_jp' => $oldPromotion->intro_jp,
                        'intro_kr' => $oldPromotion->intro_kr,
                        'intro_th' => $oldPromotion->intro_th,
                        'url' => $oldPromotion->url,
                        'start_time' => $oldPromotion->start_time,
                        'end_time' => $oldPromotion->end_time,
                        'discount_type' => $oldPromotion->discount_type,
                        'select_products' => $oldPromotion->select_products,
                        'logo' => $oldPromotion->logo,
                        'cover' => $oldPromotion->cover,
                        'sort' => $oldPromotion->sort_id,
                        'is_on' => $oldPromotion->is_on,
                        'created_at' => $oldPromotion->create_time,
                    ];
                }
                PromotionDB::insert($data);
            });
            echo "Promotion 遷移完成\n";
        }

        if (env('DB_MIGRATE_ORDER_LOGS')) {
            // Log Order 資料遷移
            $subQuery = DB::connection('icarryOld')->table('log_order');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(10000, function ($oldOrderLogs) {
                $data = [];
                foreach ($oldOrderLogs as $oldOrderLog) {
                    if ($oldOrderLog->order_id != 0 || $oldOrderLog->order_id != '' || $oldOrderLog->order_id != null) {
                        $oldOrderLog->editor == 0 || $oldOrderLog->editor > 43 ? $oldOrderLog->editor = 1 : '';
                        $data[] = [
                            'order_id' => $oldOrderLog->order_id,
                            'column_name' => $oldOrderLog->column_name,
                            'log' => $oldOrderLog->log,
                            'admin_id' => $oldOrderLog->editor,
                            'created_at' => $oldOrderLog->create_time,
                        ];
                    }
                }
                OrderLogDB::insert($data);
            });
            echo "Log Order 遷移完成\n";
        }

        if (env('DB_MIGRATE_SHOPCOM_ORDERS')) {
            // Shopcom Order 資料遷移
            $subQuery = DB::connection('icarryOld')->table('shopcom_orders');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(10000, function ($oldShopcomOrders) {
                $data = [];
                foreach ($oldShopcomOrders as $oldShopcomOrder) {
                    if ($oldShopcomOrder->order_id != 0 || $oldShopcomOrder->order_id != '' || $oldShopcomOrder->order_id != null) {
                        $data[] = [
                            'order_id' => $oldShopcomOrder->order_id,
                            'RID' => $oldShopcomOrder->RID,
                            'Click_ID' => $oldShopcomOrder->Click_ID,
                            'created_at' => $oldShopcomOrder->create_time,
                        ];
                    }
                }
                ShopcomOrderDB::insert($data);
            });
            echo "Shopcom Order 遷移完成\n";
        }

        if (env('DB_MIGRATE_TRADEVAN_ORDERS')) {
            // Tradevan Order 資料遷移
            $subQuery = DB::connection('icarryOld')->table('tradevan_orders');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(10000, function ($oldTradevanOrders) {
                $data = [];
                foreach ($oldTradevanOrders as $oldTradevanOrder) {
                    if ($oldTradevanOrder->order_id != 0 || $oldTradevanOrder->order_id != '' || $oldTradevanOrder->order_id != null) {
                        $data[] = [
                            'order_id' => $oldTradevanOrder->order_id,
                            'RID' => $oldTradevanOrder->RID,
                            'Click_ID' => $oldTradevanOrder->Click_ID,
                            'created_at' => $oldTradevanOrder->create_time,
                        ];
                    }
                }
                TradevanOrderDB::insert($data);
            });

            echo "Tradevan Order 遷移完成\n";
        }

        if (env('DB_MIGRATE_SHOPEE_ORDERS')) {
            // Shopee Order 資料遷移
            $subQuery = DB::connection('icarryOld')->table('shopee_orders');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.ordersn','asc')->chunk(5000, function ($oldShopeeOrders) {
                $data = [];
                foreach ($oldShopeeOrders as $oldShopeeOrder) {
                    if ($oldShopeeOrder->ordersn != 0 || $oldShopeeOrder->ordersn != '' || $oldShopeeOrder->ordersn != null) {
                        $oldShopeeOrder->update_time == '0000-00-00 00:00:00' ? $oldShopeeOrder->update_time = null : '';
                        strstr($oldShopeeOrder->update_time,'2016-00') ? $oldShopeeOrder->update_time = null : '';
                        $oldShopeeOrder->update_time == null ? $updateTime = 0 : $updateTime = strtotime($oldShopeeOrder->update_time);
                        $data[] = [
                            'ordersn' => $oldShopeeOrder->ordersn,
                            'order_status' => $oldShopeeOrder->order_status,
                            'detail' => $oldShopeeOrder->detail,
                            'country' => $oldShopeeOrder->country,
                            'memo' => $oldShopeeOrder->memo,
                            'update_time' => $updateTime,
                            'updated_at' => $oldShopeeOrder->update_time,
                        ];
                    }
                }
                ShopeeOrderDB::insert($data);
            });
            echo "Shopee Order 遷移完成\n";
        }

        if (env('DB_MIGRATE_PAY2GO')) {
            // Pay2go 資料遷移
            $subQuery = DB::connection('icarryOld')->table('pay2go');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(5000, function ($oldPay2gos) {
                $data = [];
                foreach ($oldPay2gos as $oldPay2go) {
                    $data[] = [
                        'order_number' => $oldPay2go->order_number,
                        'post_json' => $oldPay2go->post_json,
                        'get_json' => $oldPay2go->get_json,
                        'created_at' => $oldPay2go->create_time,
                    ];
                }
                Pay2goDB::insert($data);
            });
            echo "Pay2go 遷移完成\n";
        }

        if (env('DB_MIGRATE_SPGATEWAY')) {
            // SPGATEWAY 資料遷移
            $subQuery = DB::connection('icarryOld')->table('spgateway');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(5000, function ($oldSpgateways) {
                $data = [];
                foreach ($oldSpgateways as $oldSpgateway) {
                    // $oldSpgateway->result_json == '' ? $oldSpgateway->result_json = null : '';
                    $oldSpgateway->result_json == '' ? $oldSpgateway->result_json = null : $oldSpgateway->result_json = str_replace('@{','{',$oldSpgateway->result_json);
                    if($oldSpgateway->order_number){
                        $data[] = [
                            'order_number' => $oldSpgateway->order_number,
                            'pay_status' => $oldSpgateway->pay_status,
                            'PaymentType' => $oldSpgateway->PaymentType,
                            'memo' => $oldSpgateway->memo,
                            'post_json' => $oldSpgateway->post_json,
                            'get_json' => $oldSpgateway->get_json,
                            'result_json' => $oldSpgateway->result_json,
                            'created_at' => $oldSpgateway->create_time,
                            'updated_at' => $oldSpgateway->update_time,
                        ];
                    }
                }
                SpgatewayDB::insert($data);
            });
            echo "SPGATEWAY 遷移完成\n";
        }

        if (env('DB_MIGRATE_SOURCES')) {
            // 渠道清單 資料建立
            $sources = OrderDB::select('create_type as source')->groupBy('source')->get()->pluck('source')->all();
            $sources = array_filter($sources);
            sort($sources);
            $sourceNames = ['17life' => '17Life', 'admin' => '後台匯入', 'alipay' => '支付寶小程序', 'Amazon' => 'Amazon', 'app' => 'App', 'asiamiles' => '亞洲萬里通', 'Ctrip' => '攜程網', 'ezfly' => '易飛網', 'hutchgo' => 'Hutchgo', 'iii' => '資策會', 'invade' => 'Invade', 'kiosk' => 'kiosk', 'KKday' => 'KKday', 'klook' => 'KLook', 'momo' => 'MOMO', 'myhuo' => '買貨網', 'oneshop' => 'OneShop', 'shopee_my' => '蝦皮 馬來西亞', 'shopee_sg' => '蝦皮 新加坡', 'shopee_tw' => '蝦皮 台灣', 'union' => 'union', 'vendor' => '商家', 'web' => 'Web', 'Yahoo購物中心' => 'Yahoo購物中心', 'yirui' => '宜睿', '中保' => '中保', '其他' => '其它', '其他商城' => '其他商城', '客路' => '客路', '松果' => '松果', '生活市集' => '生活市集', '福委會' => '福委會', '統一百華' => '統一百華', '聯強' => '聯強', '鼎新' => '鼎新', ];
            for($i=0;$i<count($sources);$i++){
                $name = ucfirst($sources[$i]);
                foreach($sourceNames as $key => $value){
                    if($sources[$i] == $key){
                        $name = $value;
                        break;
                    }
                }
                $data[] = [
                    'source' => $sources[$i],
                    'name' => $name,
                ];
            }
            SourceDB::insert($data);

            echo "SOURCES 建立完成\n";
        }

        if (env('DB_MIGRATE_ORDER_DAILY_TOTALS')) {
            // ORDER DAILY TOTAL 資料遷移
            //為了將蝦皮分離出來，故改用新的Order資料表重新建立
            $users = UserDB::where('status',1)->whereBetween('created_at',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $users = $users->select([
                DB::raw("DATE_FORMAT(created_at,'%Y-%m') as yyyymm"),
                DB::raw("DATE_FORMAT(created_at,'%Y-%m-%d') as yyyymmdd"),
                DB::raw("COUNT(id) as user_total"),
            ])->groupBy('yyyymmdd')->orderBy('yyyymmdd','asc')->get();
            $noPayOrders = OrderDB::where('status',0)->whereBetween('created_at',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $noPayOrders = $noPayOrders->select([
                DB::raw("DATE_FORMAT(created_at,'%Y-%m') as yyyymm"),
                DB::raw("DATE_FORMAT(created_at,'%Y-%m-%d') as yyyymmdd"),
                DB::raw("COUNT(id) as not_ok_total"),
                'create_type as source',
            ])->groupBy('yyyymmdd','source')->orderBy('yyyymmdd','asc')->get();
            $orders = OrderDB::whereBetween('pay_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $orders = $orders->select([
                DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
                DB::raw("DATE_FORMAT(pay_time,'%Y-%m-%d') as yyyymmdd"),
                DB::raw("COUNT(id) as total_order"),
                DB::raw("SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as total_money"),
                DB::raw("SUM(shipping_fee+parcel_tax) as total_shipping_tax"),
                DB::raw("SUM((CASE WHEN status = 0 THEN 1 ELSE 0 END)) as not_ok_total"),
                DB::raw("truncate(SUM(amount+shipping_fee+parcel_tax-discount-spend_point) / COUNT(id),2) as avg"),
                DB::raw("COUNT(distinct(user_id)) as distinct_buyer_total"),
                'create_type as source',
            ])->groupBy('yyyymmdd','create_type')->orderBy('yyyymmdd','asc');
            $orders = $orders->chunk(1000, function ($oldOrders) use ($users,$noPayOrders) {
                $data = [];
                foreach ($oldOrders as $oldOrder) {
                    $timezone = 'Asia/Taipei';
                    foreach ($users as $user) {
                        if($user->yyyymmdd == $oldOrder->yyyymmdd){
                            $userTotal = $user->user_total;
                            break;
                        }
                    }
                    foreach ($noPayOrders as $noPayOrder) {
                        $notOkTotal = 0;
                        if($noPayOrder->yyyymmdd == $oldOrder->yyyymmdd && $noPayOrder->source == $oldOrder->source){
                            $notOkTotal = $noPayOrder->not_ok_total;
                            break;
                        }
                    }
                    $data[] = [
                        'yyyymm' => $oldOrder->yyyymm,
                        'yyyymmdd' => $oldOrder->yyyymmdd,
                        'total_order' => $oldOrder->total_order,
                        'total_money' => $oldOrder->total_money,
                        'total_shipping_tax' => $oldOrder->total_shipping_tax,
                        'not_ok_total' => $notOkTotal,
                        'distinct_buyer_total' => $oldOrder->distinct_buyer_total,
                        'avg' => $oldOrder->avg,
                        'user_total' => $userTotal,
                        'source' => $oldOrder->source,
                        'created_at' => Carbon::createFromDate(substr($oldOrder->yyyymmdd,0,4), substr($oldOrder->yyyymmdd,5,2), substr($oldOrder->yyyymmdd,8,2), $timezone)->addDay(),
                    ];
                }
                OrderDailyTotalDB::insert($data);
            });
            echo "ORDER DAILY TOTAL 重建完成\n";
        }

        if (env('DB_MIGRATE_ORDER_MONTHLY_TOTALS')) {
            // ORDER MONTHLY TOTAL 資料遷移
            //為了將蝦皮分離出來，故改用新的Order資料表重新建立
            $orders = OrderDB::whereBetween('pay_time',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $orders = $orders->select([
                DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
                DB::raw("COUNT(id) as total_order"),
                DB::raw("SUM(amount+shipping_fee+parcel_tax-discount-spend_point) as total_money"),
                DB::raw("SUM(shipping_fee+parcel_tax) as total_shipping_tax"),
                DB::raw("truncate(SUM(amount+shipping_fee+parcel_tax-discount-spend_point) / COUNT(id),2) as avg"),
                DB::raw("COUNT(distinct(user_id)) as distinct_buyer_total"),
                'create_type as source',
            ])->groupBy('yyyymm','source')->orderBy('yyyymm','asc')->get();
            $noPayOrders = OrderDB::where('status',0)->whereBetween('created_at',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $noPayOrders = $noPayOrders->select([
                DB::raw("DATE_FORMAT(created_at,'%Y-%m') as yyyymm"),
                DB::raw("COUNT(id) as not_ok_total"),
                'create_type as source',
            ])->groupBy('yyyymm','source')->orderBy('yyyymm','asc')->get();
            $users = UserDB::where('status',1)->whereBetween('created_at',['2015-01-01 00:00:00',date('Y-m-d H:i:s')]);
            $users = $users->select([
                DB::raw("DATE_FORMAT(created_at,'%Y-%m') as yyyymm"),
                DB::raw("COUNT(id) as user_total"),
            ])->groupBy('yyyymm')->orderBy('yyyymm','asc')->get();
            $data = [];
            foreach ($orders as $order) {
                $timezone = 'Asia/Taipei';
                foreach ($users as $user) {
                    $userTotal = 0;
                    if($user->yyyymm == $order->yyyymm){
                        $userTotal = $user->user_total;
                        break;
                    }
                }
                foreach ($noPayOrders as $noPayOrder) {
                    $notOkTotal = 0;
                    if($noPayOrder->yyyymm == $order->yyyymm && $noPayOrder->source == $order->source){
                        $notOkTotal = $noPayOrder->not_ok_total;
                        break;
                    }
                }
                $data[] = [
                    'yyyymm' => $order->yyyymm,
                    'pay_orders' => $order->total_order,
                    'pay_money_total' => $order->total_money,
                    'ffeight_tariff_total' => $order->total_shipping_tax,
                    'no_pay_orders' => $notOkTotal,
                    'no_repeat_consumption' => $order->distinct_buyer_total,
                    'avg_orders_money' => $order->avg,
                    'registered_num' => $userTotal,
                    'source' => $order->source,
                ];
            }
            OrderMonthlyTotalDB::insert($data);
            echo "ORDER MONTHLY TOTAL 重建完成\n";
        }

        if (env('DB_MIGRATE_SHIPPING_MONTHLY_TOTALS')) {
            // SHIPPING MONTHLY TOTAL 資料遷移
            //為了將蝦皮分離出來，故改用新的Order資料表重新建立
            $sources = OrderDB::select('create_type as source')->groupBy('source')->get()->pluck('source')->all();
            $sources = array_filter($sources);
            sort($sources);

            $startYear = 2015;
            $finalYear = date('Y');
            for($y = $startYear; $y<= $finalYear; $y++){
                for($m=1; $m<=12; $m++){
                    $m <= 9 ? $m = '0'.$m : '';
                    $tmp = $y.'-'.$m;
                    $ym = $y.'-'.$m;

                    $tmps = OrderDB::where('status','>=',1)->whereBetween('pay_time',[$ym.'-01 00:00:00',$ym.'-31 23:59:59'])->select([
                        DB::raw("DATE_FORMAT(pay_time,'%Y-%m') as yyyymm"),
                        'create_type',
                        'shipping_method',
                        DB::raw("(CASE WHEN amount+shipping_fee+parcel_tax-discount-spend_point > 0 THEN amount+shipping_fee+parcel_tax-discount-spend_point ELSE 0 END) as money"),
                    ])->get()->groupBy('create_type')->all();

                    if($tmps){
                        for ($i=0;$i<count($sources);$i++) {
                            $shipping1Count = $shipping1Money = $shipping2Count = $shipping2Money = $shipping3Count = $shipping3Money = $shipping4Count = $shipping4Money = $shipping5Count = $shipping5Money = $shipping6Count = $shipping6Money = 0;
                            foreach ($tmps as $source => $values) {
                                if($source == $sources[$i]){
                                    foreach($values as $value){
                                        for($c=1; $c<=6; $c++){
                                            if($value->yyyymm == $ym && $value->shipping_method == $c){
                                                ${'shipping'.$c.'Money'} += $value->money;
                                                ${'shipping'.$c.'Count'}++;
                                            }
                                        }
                                    }
                                }
                            }
                            if($shipping1Count > 0 || $shipping2Count > 0 || $shipping3Count > 0 || $shipping4Count > 0 || $shipping5Count > 0 || $shipping6Count > 0){
                                ShippingMonthlyTotalDB::create([
                                    'yyyymm' => $ym,
                                    'source' => $sources[$i],
                                    'shipping_1_count' => $shipping1Count,
                                    'shipping_1_money' => $shipping1Money,
                                    'shipping_2_count' => $shipping2Count,
                                    'shipping_2_money' => $shipping2Money,
                                    'shipping_3_count' => $shipping3Count,
                                    'shipping_3_money' => $shipping3Money,
                                    'shipping_4_count' => $shipping4Count,
                                    'shipping_4_money' => $shipping4Money,
                                    'shipping_5_count' => $shipping5Count,
                                    'shipping_5_money' => $shipping5Money,
                                    'shipping_6_count' => $shipping6Count,
                                    'shipping_6_money' => $shipping6Money,
                                ]);
                            }
                        }
                    }
                }
            }
            echo "SHIPPING MONTHLY TOTAL 遷移完成\n";
        }
        if (env('DB_MIGRATE_SHIPMENT_LOGS')) {
            //Shipment Log 資料移轉
            $subQuery = DB::connection('icarryOld')->table('shipment_log');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(5000, function ($oldShipments) {
                $data = [];
                foreach ($oldShipments as $oldShipment) {
                    if ($oldShipment->order_id) {
                        $data[] = [
                            'order_id' => $oldShipment->order_id,
                            'user_id' => $oldShipment->user_id,
                            'shipping_method' => $oldShipment->shipping_method,
                            'order_number' => $oldShipment->order_number,
                            'send' => $oldShipment->send,
                            'created_at' => $oldShipment->create_time,
                        ];
                    }
                }
                ShipmentLogDB::insert($data);
            });
            echo "Shipment Log 遷移完成\n";
        }
        if (env('DB_MIGRATE_SMS_SCHEDULES')) {
            //SMS SCHUDLES 資料移轉
            $subQuery = DB::connection('icarryOld')->table('sms_schedule');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(5000, function ($oldSMS) {
                $data = [];
                foreach ($oldSMS as $sms) {
                    $data[] = [
                        'order_id' => $sms->order_id,
                        'user_id' => $sms->user_id,
                        'mobile' => $sms->mobile,
                        'message' => $sms->message,
                        'sms_vendor' => $sms->vendor,
                        'is_send' => $sms->is_send,
                        'created_at' => $sms->create_time,
                    ];
                }
                SmsScheduleDB::insert($data);
            });
            echo "SMS Schedule 遷移完成\n";
        }
        if (env('DB_SEED_ALIPAYS')) {
            //ALIPAYS 資料移轉
            $subQuery = DB::connection('icarryOld')->table('alipay');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(5000, function ($oldAlipays) {
                $data = [];
                foreach ($oldAlipays as $alipay) {
                    if(!empty($alipay->order_number)){
                        $data[] = [
                            'order_number' => $alipay->order_number,
                            'amount' => $alipay->amount,
                            'pay_status' => $alipay->pay_status,
                            'payment_number' => $alipay->payment_number,
                            'rmb_fee' => $alipay->rmb_fee,
                            'currency' => $alipay->currency,
                            'gateway' => $alipay->gateway,
                            'wallet' => $alipay->wallet,
                            'post_json' => $alipay->post_json,
                            'get_json' => $alipay->get_json,
                            'created_at' => $alipay->create_time,
                            'updated_at' => $alipay->update_time,
                        ];

                    }
                }
                AlipayDB::insert($data);
            });
            echo "Alipay 遷移完成\n";
        }
        if (env('DB_SEED_TASHIN_CREDITCARDS')) {
            //TSC CREDIT CARD 資料移轉
            $subQuery = DB::connection('icarryOld')->table('tsc_credit_card');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(5000, function ($oldTSC) {
                $data = [];
                foreach ($oldTSC as $tsc) {
                    if(!empty($tsc->order_number)){
                        $data[] = [
                            'type' => $tsc->type,
                            'order_number' => $tsc->order_number,
                            'amount' => $tsc->amount,
                            'pay_status' => $tsc->pay_status,
                            'post_json' => $tsc->post_json,
                            'get_json' => $tsc->get_json,
                            'created_at' => $tsc->create_time,
                            'updated_at' => $tsc->update_time,
                        ];
                    }
                }
                TashinCreditcardDB::insert($data);
            });
            echo "TSC CREDIT CARD 遷移完成\n";
        }
    }

    /**
    * 將一個字串中含有全形的數字字元、字母、空格或'% -()'字元轉換為相應半形字元
    *
    * @access public
    * @param string $str 待轉換字串
    *
    * @return string $str 處理後字串
    */
    public function makeSemiangle($str)
    {
        $arr = array(
            '０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
            '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
            'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
            'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
            'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
            'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
            'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
            'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
            'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
            'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
            'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
            'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
            'ｙ' => 'y', 'ｚ' => 'z'
        );
        return strtr($str, $arr);
    }

}
