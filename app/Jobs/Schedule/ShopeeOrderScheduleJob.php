<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SystemSetting as SystemSettingDB;
use App\Models\Order as OrderDB;
use App\Models\OrderLog as OrderLogDB;
use App\Models\ShopeeOrder as ShopeeOrderDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\ProductUnitName as ProductUnitNameDB;
use App\Models\Country as CountryDB;
use App\Models\OrderItem as OrderItemDB;
use App\Traits\ShopeeFunctionTrait;
use DB;

class ShopeeOrderScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,ShopeeFunctionTrait;

    protected $countries= ['TW','SG','MY'];
    protected $shippingMethodCountry = ['TW' => 5, 'SG' => 4, 'MY' => 4];
    protected $userMemoPrifix = ['TW' => '台灣', 'SG' => '新加坡', 'MY' => '馬來西亞'];
    protected $shopee = [
        "TW"=> [
            "Partnerid"=>841026,
            "PartnerName"=>"CB-taiwanicarry",
            "Shopid"=>23147634,
            "Username"=>"taiwanicarry.my",
            "Country"=>"TW",
            "SecretKey"=>"b3c45da4ae802691ae353e91a8a52a792a133363cb9e22f3cf911ae3d164ce94"
        ],

        "SG" => [
                "Partnerid"=>10504,
                "PartnerName"=>"CB-taiwanicarry",
                "Shopid"=>19136784,
                "Username"=>"taiwanicarry.sg",
                "Country"=>"SG",
                "SecretKey"=>"85187c5262ea0b7055b88e684723dc7eb15e19de0b67cda79816e05c1e7b9261"
            ],
        "MY" => [
                "Partnerid"=>10504,
                "PartnerName"=>"CB-taiwanicarry",
                "Shopid"=>19143226,
                "Username"=>"taiwanicarry.my",
                "Country"=>"MY",
                "SecretKey"=>"85187c5262ea0b7055b88e684723dc7eb15e19de0b67cda79816e05c1e7b9261"
            ],
        ];


    public function __construct($ktime = null)
    {
        $this->setting = SystemSettingDB::find(1);
        $this->SGD = $this->setting->exchange_rate_SGD; //新加坡幣匯率
        $this->MYR = $this->setting->exchange_rate_MYR; //馬來西亞幣匯率
        //若$ktime存在時, 給每天排程使用
        if(!empty($ktime)){
            $this->createTimeTo = mktime(23, 59, 59, date("m",$ktime), date("d",$ktime), date("Y",$ktime));
        }else{
            $this->createTimeTo = mktime(23, 59, 59, date("m"), date("d"), date("Y"));
        }
        $this->createTimeFrom = 0;
        $this->paginationOffset = 0;
        $this->grossWeightRate = $this->setting->gross_weight_rate;
    }

    /**
     * Execute the job.
     * 每十分鐘及每天讀一次蝦皮API
     * @return void
     */
    public function handle()
    {
        // $ctime = microtime(true); //紀錄開始時間
        foreach ($this->countries as $country) {
            $this->country = $country;
            // echo "讀 $this->country 蝦皮訂單開始<br>";
            //撈shopee_orders原有的單
            $orders = ShopeeOrderDB::where('country',$this->country)->orderBy('update_time','desc')->limit(1000)->get();
            foreach ($orders as $order) {
                $orderNumbers[] = $order->ordersn;
                $shopeeOrders[$order->ordersn] = $order->toArray();
            }
            //讀蝦皮訂單列表
            for ($i=0; $i < 100; $i++) {
                // echo "讀 $this->country 蝦皮訂單迴圈 ".($i+1)." <br>";
                $this->paginationOffset = $i * 50;
                $ordersJson = $this->getOrdersList($this->shopee[$this->country]["Partnerid"],$this->shopee[$this->country]["Shopid"],$this->shopee[$this->country]["SecretKey"],$this->createTimeTo,$this->createTimeFrom,50,$this->paginationOffset);
                $orders=json_decode($ordersJson,true);

                if(!empty($orders['orders'])){
                    foreach($orders["orders"] as $k=>$v){
                        $ordersReady[]=$v["ordersn"];
                    }
                    $ordersDetailsJson=$this->getOrderDetails($ordersReady,$this->shopee[$this->country]["Partnerid"],$this->shopee[$this->country]["Shopid"],$this->shopee[$this->country]["SecretKey"]);
                    $ordersDetails=json_decode($ordersDetailsJson,true);
                }

                if(!empty($ordersDetails["orders"])){
                    foreach ($ordersDetails["orders"] as $k=>$v) {
                        $detail = json_encode($v);
                        $shopeeOrder = ShopeeOrderDB::where('ordersn', $v['ordersn'])->first();
                        $order = OrderDB::where('partner_order_number', $v['ordersn'])->first();
                        if (in_array($v["ordersn"], $orderNumbers)) {
                            if ($v["update_time"] == $shopeeOrders[$v["ordersn"]]["update_time"]) {
                                //更新時間相同不動作
                                if ($v["order_status"]=="COMPLETED") {//訂單完成可能是退貨完成 所以必須再走一次
                                    if (!empty($shopeeOrder)) {
                                        $shopeeOrder = $shopeeOrder->update([
                                            'order_status' => $v["order_status"],
                                            'detail' => $datail,
                                            'memo' => $shopeeOrders[$v['ordersn']]['order_status'].' => '.$v['order_status'],
                                            'update_time' => $v["update_time"],
                                        ]);
                                    }
                                    //蝦皮有tracking_no則改變狀態
                                    if (!empty($v["tracking_no"])) {
                                        !empty($order) && $order->status > 0 && $order->status <= 3 ? $order = $order->update(['status' => 4]) : '';
                                    }
                                } elseif ($v["order_status"]=="READY_TO_SHIP") {//訂單要跑
                                    if (!empty($order)) {
                                        $order->status < 1 ? $order->update(['status' => 1, 'pay_time' => date("Y-m-d H:i:s", $v["update_time"])]) : '';
                                    } else {
                                        !empty($shopeeOrder) ? $shopeeOrder->delete() : '';
                                    }
                                } elseif ($v["order_status"]=="CANCELLED" || $v["order_status"]=="TO_RETURN") {//預定出貨改取消
                                    if ($shopeeOrders[$v["ordersn"]]["order_status"]=="READY_TO_SHIP") {//status=1
                                        !empty($shopeeOrder) ? $shopeeOrder->update(['order_status' => $v['order_status'], 'update_time' => $v['update_time'], 'memo' => $shopeeOrders[$v["ordersn"]]["order_status"].' => '.$v["order_status"]]) : '';
                                        !empty($order) ? $order->update(['status' => -1, 'admin_memo' => '預定出貨但蝦皮改取消']) : '';
                                    } elseif ($shopeeOrders[$v["ordersn"]]["order_status"]=="CANCELLED" || $shopeeOrders[$v["ordersn"]]["order_status"]=="TO_RETURN") {//本來就取消的單
                                        !empty($shopeeOrder) ? $shopeeOrder->update(['order_status' => $v['order_status'], 'update_time' => $v['update_time'], 'memo' => $shopeeOrders[$v["ordersn"]]["order_status"].' => '.$v["order_status"]]) : '';
                                        !empty($order) ? $order->update(['status' => -1, 'admin_memo' => '蝦皮改成訂單錯誤']) : '';
                                    } else {
                                        //蝦皮取消
                                        !empty($order) ? $order->update(['status' => -1, 'admin_memo' => '蝦皮取消']) : '';
                                    }
                                } elseif ($v["order_status"]=="INVALID") {//本來有單變成錯誤
                                    !empty($shopeeOrder) ? $shopeeOrder->update(['order_status' => $v['order_status'], 'update_time' => $v['update_time'], 'memo' => $shopeeOrders[$v["ordersn"]]["order_status"].' => '.$v["order_status"]]) : '';
                                    !empty($order) ? $order->update(['status' => -1, 'admin_memo' => '蝦皮改成訂單錯誤']) : '';
                                }
                            } else {
                                if ($v["order_status"]=="CANCELLED" || $v["order_status"]=="TO_RETURN") {//預定出貨改取消
                                    if ($shopeeOrders[$v["ordersn"]]["order_status"]=="READY_TO_SHIP") {//status=1
                                        !empty($shopeeOrder) ? $shopeeOrder->update(['order_status' => $v['order_status'], 'update_time' => $v['update_time'], 'memo' => $shopeeOrders[$v["ordersn"]]["order_status"].' => '.$v["order_status"]]) : '';
                                        !empty($order) ? $order->update(['status' => -1, 'admin_memo' => '預定出貨但蝦皮改取消']) : '';
                                    } elseif ($shopeeOrders[$v["ordersn"]]["order_status"]=="CANCELLED" || $shopeeOrders[$v["ordersn"]]["order_status"]=="TO_RETURN") {//本來就取消的單
                                        //不做任何事情
                                    } else {
                                        //蝦皮取消
                                        !empty($order) ? $order->update(['status' => -1, 'admin_memo' => '蝦皮取消']) : '';
                                    }
                                } elseif ($v["order_status"]=="INVALID") {//本來有單變成錯誤
                                    !empty($shopeeOrder) ? $shopeeOrder->update(['order_status' => $v['order_status'], 'update_time' => $v['update_time'], 'memo' => $shopeeOrders[$v["ordersn"]]["order_status"].' => '.$v["order_status"]]) : '';
                                    !empty($order) ? $order->update(['status' => -1, 'admin_memo' => '蝦皮改成訂單錯誤']) : '';
                                } elseif ($v["order_status"]=="COMPLETED") {//訂單完成
                                    !empty($shopeeOrder) ? $shopeeOrder->update(['order_status' => $v['order_status'], 'update_time' => $v['update_time'], 'memo' => $shopeeOrders[$v["ordersn"]]["order_status"].' => '.$v["order_status"]]) : '';
                                    !empty($order) ? $order->update(['status' => -1, 'admin_memo' => '蝦皮改成訂單錯誤']) : '';
                                    if (empty($v["tracking_no"])) {
                                        !empty($order) ? $order->update(['status' => -1, 'admin_memo' => '蝦皮退款退貨已完成']) : '';
                                    } else {
                                        !empty($order) && $order->status > 0 && $order->status <= 3 ? $order = $order->update(['status' => 4]) : '';
                                    }
                                } elseif ($v["order_status"]=="READY_TO_SHIP") {//訂單要跑
                                    if (!empty($order)) {
                                        $order->status < 1 ? $order->update(['status' => 1, 'pay_time' => date("Y-m-d H:i:s", $v["update_time"])]) : '';
                                    } else {
                                        !empty($shopeeOrder) ? $shopeeOrder->delete() : '';
                                    }
                                }
                            }
                        } else {
                            if ($v["order_status"]=="UNPAID" || $v["order_status"]=="INVALID") {
                                //這些單不處理
                            } else {
                                //建立新的蝦皮訂單
                                $shopeeOrder = ShopeeOrderDB::create([
                                    'ordersn' => $v['ordersn'],
                                    'order_status' => $v['order_status'],
                                    'update_time' => $v['update_time'],
                                    'detail' => $detail,
                                    'country' => $this->country,
                                ]);
                                //匯率
                                $exchangeRate = ($v["currency"] == "SGD") ? $this->SGD : $this->MYR;
                                $v["currency"]=="TWD" ? $exchangeRate = 1 : '';
                                $data['partner_country'] = $this->country;
                                $data['shipping_fee'] = $shippingFee = round($v["estimated_shipping_fee"] * $exchangeRate);
                                $data['amount'] = $amount = round(($v["total_amount"] - $v["estimated_shipping_fee"]) * $exchangeRate);
                                empty($v["actual_shipping_cost"]) ? $discount = 0 : $discount = round($v["actual_shipping_cost"] * $exchangeRate);
                                $data['discount'] = $discount;
                                //訂單資料
                                $countryName = $this->userMemoPrifix[$this->country];
                                $data['origin_country'] = '台灣';
                                $data['from'] = 1;
                                $data['to'] = CountryDB::where('name',$countryName)->first()->id;
                                $data['ship_to'] = $countryName;
                                $data['user_id'] = $userId=5661;
                                $userMobile = "+886906486688";
                                $userEmail = "icarry4tw@gmail.com";
                                $data['shipping_method'] = $shippingMethod = $this->shippingMethodCountry[$this->country]; //寄送海外or台灣
                                $data['pay_method'] = $userName = "蝦皮";
                                $partnerId = "shopee";
                                $data['create_type'] = 'shopee_'.strtolower($this->country);
                                $data['partner_order_number'] = $partnerOrderNumber = $v["ordersn"];
                                $data['shipping_memo'] = $shippingMemo = null;
                                $data['shipping_number'] = $shippingNumber = null;
                                if ($v["order_status"] == "CANCELLED" || $v["order_status"] == "TO_RETURN") {//改成取消
                                    $data['status'] = $status = -1;
                                } elseif ($v["order_status"] == "READY_TO_SHIP") {//要處理 要把資料接回來
                                    $data['status'] = $status = 1;
                                } elseif ($v["order_status"] == "SHIPPED" || $v["order_status"] == "TO_CONFIRM_RECEIVE") {//拿資料回來準備完成
                                    $data['status'] = $status = 3;
                                    $data['shipping_memo'] = $shippingMemo = '[{"create_time":"'.date("Y/m/d H:i:s", $v["update_time"]).'","express_way":"冠廷物流","express_no":"'.$v["tracking_no"].'"}]';
                                    $data['shipping_number'] = $shippingNumber = $v["tracking_no"];
                                } elseif ($v["order_status"] == "COMPLETED") {
                                    $data['status'] = $status = 4;
                                    $data['shipping_memo'] = $shippingMmemo = '[{"create_time":"'.date("Y/m/d H:i:s", $v["update_time"]).'","express_way":"冠廷物流","express_no":"'.$v["tracking_no"].'"}]';
                                    $data['shipping_number'] = $shippingNumber = $v["tracking_no"];
                                    empty($shippingNumber) ? $data['status'] = $status=-1 : '';
                                }
                            }
                            $data['receiver_name'] = $receiverName = $v["recipient_address"]["name"];
                            $data['receiver_tel'] = $receiverTel = $v["recipient_address"]["phone"];
                            $data['receiver_email'] = $receiverEmail = null;
                            $data['receiver_address'] = $receiverAddress = ($v["recipient_address"]["full_address"] == '請密切注意你的訂單狀態呦！') ? '['.$v["shipping_carrier"].']'.$v["recipient_address"]["full_address"] : $v["recipient_address"]["full_address"];
                            $data['user_memo'] = $userMemo = "蝦皮訂單：({$this->userMemoPrifix[$this->country]}){$v["ordersn"]}。".$v["message_to_seller"];
                            $data['order_number'] =  $orderNumber = date("ymdHis", $v["create_time"]).substr(microtime(true), -2);//12碼+2
                            $data['order_number'] =  $orderNumber = str_replace(".", rand(0, 9), $orderNumber);
                            $data['pay_time'] = $payTime = date("Y-m-d H:i:s", $v["create_time"]);
                            $data['buyer_email'] = $userEmail;
                            $data['invoice_type'] = $invoiceType = 2;
                            $data['invoice_sub_type'] = $invoiceSubType = 2;
                            $data['print_flag'] = $printFlag = 'N';
                            $data['carrier_type'] = $carrierType = null;
                            $data['parcel_tax'] = $parcelTax = 0;
                            $data['spend_point'] = $spendPoint = 0;
                            if (!empty($v["ordersn"])) {
                                $order = OrderDB::where('partner_order_number', 'like', "$partnerOrderNumber%")->orWhere('user_memo', 'like', "%$partnerOrderNumber%")->first();
                                !empty($order) ? $partnerOrderExist = 1 : $partnerOrderExist = null;
                            } else {
                                $partnerOrderExist = 1;
                            }
                            if (empty($partnerOrderExist)) {
                                $data['book_shipping_date'] = $bookShippingDate = $this->shopeeBookShippingDate();
                                $shippingMethod == 4 ? $data['book_shipping_date'] = $bookShippingDate = null : '';
                                //book_shipping_date從2021-05-04改NULL
                                $order = OrderDB::create($data);
                                if (!empty($bookShippingDate)) {
                                    $orderLog = OrderLogDB::create([
                                            'order_id' => $order->id,
                                            'column_name' => 'book_shipping_date',
                                            'log' => $bookShippingDate,
                                            'admin_id' => 0,
                                        ]);
                                }
                                //下面是商品迴圈
                                $totalPrice = 0;
                                if(!empty($v['items'])){
                                    foreach ($v["items"] as $key => $row) {
                                        $row["price"]=round($exchangeRate * $row["variation_discounted_price"]);
                                        $totalPrice += $row["price"] * $row["variation_quantity_purchased"];//因為$v["total_amount"]反應的價格不對
                                        $adminMemo = "原價：".round($exchangeRate * $row["variation_original_price"]);
                                        $itemSku = empty(trim($row["variation_sku"])) ? trim($row["item_sku"]) : trim($row["variation_sku"]);
                                        $item = ProductModelDB::join('products','products.id','product_models.product_id')
                                        ->join('vendors','vendors.id','products.vendor_id')
                                        ->where('product_models.sku',$itemSku)
                                        ->select([
                                            'product_models.id as product_model_id',
                                            'products.id as product_id',
                                            'vendors.id as vendor_id',
                                            'product_models.sku as sku',
                                            'vendors.name as vendor_name',
                                            'products.name as product_name',
                                            'unit_name' => ProductUnitNameDB::whereColumn('products.unit_name_id','product_unit_names.id')->select('name')->limit(1),
                                            DB::raw("(gross_weight * $this->grossWeightRate) as gross_weight"),
                                            DB::raw("(net_weight * $this->grossWeightRate) as net_weight"),
                                            'products.is_tax_free',
                                            'vendors.service_fee as vendor_service_fee',
                                            'products.service_fee_percent as product_service_fee_percent',
                                        ])->first();
                                        if(!empty($item)){
                                            $vendorServiceFee = str_replace('"percent":}', '"percent":0}', $item->vendor_service_fee);
                                            $vendorServiceFee = json_decode($vendorServiceFee);
                                            if ($shippingMethod == 3) {
                                                foreach ($vendorServiceFee as $sf_key=>$sf_val) {
                                                    if ($sf_val->name=="現場提貨") {
                                                        $item->vendor_service_fee_percent=$sf_val->percent;
                                                        break;
                                                    }
                                                }
                                            } else {
                                                foreach ($vendorServiceFee as $sf_key=>$sf_val) {
                                                    if ($sf_val->name=="iCarry") {
                                                        $item->vendor_service_fee_percent=$sf_val->percent;
                                                        break;
                                                    }
                                                }
                                            }
                                            $item->order_id = $order->id;
                                            $item->admin_memo = $adminMemo;
                                            $item->price = $row["price"];
                                            $item->quantity = $row["variation_quantity_purchased"];
                                            $orderItem = OrderItemDB::create($item->toArray());
                                        }
                                    }
                                    //蝦皮的折扣 = ICARRY的商品總價 + ICARRY我來寄的運費 - 蝦皮的總價
                                    $discount = ($totalPrice + $shippingFee) - round($v["total_amount"] * $exchangeRate);
                                    $discount = round($discount);
                                    $order = $order->update(['discount' => $discount, 'amount' => $totalPrice]);
                                }
                            }
                        }
                    }
                }
                if(!empty($orders['more']) && $orders['more'] == true){
                    continue;
                }else{
                    break;
                }
            }
            // echo " $this->country 蝦皮訂單完成<br>";
        }
        // $ctime = microtime(true) - $ctime; //紀錄時間結束
        // dd('蝦皮訂單全部完成，共 '.$ctime.' 秒');
    }
}
