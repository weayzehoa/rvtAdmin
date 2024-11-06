<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DigiwinOrderImport;
use App\Imports\MomoOrderImport;
use App\Imports\YiruiOrderImport;
use App\Imports\OrderShippingNumberImport;
use App\Imports\OrderShipmentImport;

use App\Models\Country as CountryDB;
use App\Models\Order as OrderDB;
use App\Models\MomoOrder as MomoOrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\OrderShipping as OrderShippingDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\DigiwinImportOrder as DigiwinImportOrderDB;
use App\Models\DigiwinPayment as DigiwinPaymentDB;
use App\Models\SystemSetting as SystemSettingDB;
use App\Models\ReceiverBaseSetting as ReceiverBaseSettingDB;
use App\Models\YiruiOrder as YiruiOrderDB;
use DB;

use App\Jobs\AdminOrderStatusJob;

class AdminImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $param;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($param)
    {
        $param['created_at'] = date('Y-m-d H:i:s');
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
        if($param['cate'] == 'orders'){
            $orderTypes = $this->param['imports'];
            if(in_array($param['type'], $orderTypes)){
                $file = $param->file('filename');
                $uploadedFileMimeType = $file->getMimeType();
                $mimes = array('application/excel','application/vnd.ms-excel','application/vnd.msexcel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/CDFV2','application/octet-stream');
                if($param['type'] == '宜睿匯入'){ //宜睿的檔案沒有副檔名的文字檔
                    $this->yiruiOrder($file);
                }
                if(in_array($uploadedFileMimeType, $mimes)){
                    if($param['type'] == '鼎新訂單匯入'){
                        Excel::import(new DigiwinOrderImport($param), $file);
                        $this->digiwinOrder();
                    }
                    if($param['type'] == 'momo匯入'){
                        Excel::import(new MomoOrderImport($param), $file);
                        $this->momoOrder();
                    }
                    if($param['type'] == '物流單號匯入'){
                        $result = Excel::toArray(new OrderShippingNumberImport, $file);
                        $this->shippingNumber($result[0]); //0代表第一個sheet
                    }
                    if($param['type'] == '批次轉出貨匯入'){
                        $result = Excel::toArray(new OrderShipmentImport, $file);
                        $this->orderShipment($result[0]); //0代表第一個sheet
                    }
                }
            }
        }
    }

    protected function momoOrder()
    {
        $createdTime = $this->param['created_at'];
        $exchangeRate = SystemSettingDB::first()->exchange_rate_RMB;
        //將資料重新撈出
        $rows = MomoOrderDB::where('created_at',$createdTime)
            ->whereNotIn('order_number',OrderDB::whereNotNull('partner_order_number')->where('user_id',2020)->select('partner_order_number')->groupBy('partner_order_number')->get())
            ->select([
                'order_number',
                'colF',
                'colG',
                'colH',
                'colM',
                'colN',
                'colO',
                'colT',
                'colU',
                DB::raw("unix_timestamp(colM) as ctime"),
            ])->orderBy('order_number','asc')->orderBy('colN','asc')->get();

        if(!empty($rows)){
            //整理資料
            $userId = 47253;
            $userMobile = '+886906486688';
            $buyerEmail = $userEmail = 'icarry4tw@gmail.com';
            $payMethod = $userName = 'momo';
            $partnerId = 'momo';
            $invoiceType = 2;
            $invoiceSubType = 2;
            $printFlag = 'N';
            $status = 1;
            $originCountry = '台灣';
            $shipTo = '台灣';
            $from = 1;
            $to = 1;
            $receiverAddress = null;
            $shippingMethod = 6;
            $adminMemo = null;

            foreach($rows as $row){ //依據訂單號碼將items集中成一個陣列
                if(isset($orders[$row->order_number."-".$row->colN][$row->colO])){
                    $row->colT += $orders[$row->order_number."-".$row->colN][$row->colO]["colT"];
                    $orders[$row->order_number."-".$row->colN][$row->colO] = $row;
                }else{
                    $orders[$row->order_number."-".$row->colN][$row->colO] = $row;
                }
            }
            //所有訂單
            if(!empty($orders)){
                foreach($orders as $key => $rows){
                    $randStart = intval(substr($row->colO, -2));
                    if($randStart < 10 || $randStart > 98){
                        $randStart = rand(10,90);
                    }
                    $orderNumber = date("ymdHis",$row->ctime).rand(10,99);//12碼+2
                    $receiverZipCode = substr($row->colH,0,3);
                    $receiverAddress = "台灣 ".substr($row->colH,3).", {$receiverZipCode}";
                    $payTime = $updateTime = $createTime = $row->colM;
                    $receiverName = $row->colG;
                    $partnerOrderNumber = $row->order_number;
                    $userMemo = "momo訂單編號: {$row->order_number}　MOMO預計出貨日：{$row->colN}";

                    for($d=1;$d<20;$d++){//20天內必有可出
                        $bookShippingDate=date("Y-m-d",strtotime("-{$d} day",strtotime($row->colN)));
                        $bookShippingN=date("N",strtotime("-{$d} day",strtotime($row->colN)));
                        $isOut = ReceiverBaseSettingDB::where([['select_date',$bookShippingDate],['is_ok',1]])->first();
                        if(!empty($isOut)){//可出
                            break;
                        }elseif(empty($isOut) && $bookShippingN < 6){//預設可出
                            break;
                        }else{
                            continue;
                        }
                    }
                    $orderItems = [];
                    $amount = $discount = $spendPoint = $shippingFee = $parcelTax = 0;
                    foreach($rows as $sku => $row){ //訂單中所有item
                        $quantity = $row->colT;
                        $price = $row->colU;
                        $product = ProductModelDB::join('products','products.id','product_models.product_id')
                        ->join('vendors','vendors.id','products.vendor_id')
                        ->join('product_unit_names','product_unit_names.id','products.unit_name_id')
                        ->where('sku',$sku)
                        ->select([
                            'product_models.id as product_model_id',
                            'products.id as product_id',
                            'products.name as product_name',
                            'products.price',
                            'products.gross_weight',
                            'products.net_weight',
                            'products.is_tax_free',
                            'products.service_fee_percent as product_service_fee_percent',
                            'vendors.id as vendor_id',
                            'vendors.name as vendor_name',
                            'vendors.service_fee as vendor_service_fee',
                            'vendors.shipping_verdor_percent',
                            'product_unit_names.name as unit_name',
                        ])->withTrashed()->first();
                        if (!empty($product)) {
                            $adminMemo="原價：{$product->price}";
                            $vendorServiceFee = $this->serviceFee($product->vendor_service_fee);
                            foreach ($vendorServiceFee as $sf_key=>$sf_val) {
                                if ($sf_val->name=="iCarry") {
                                    $vendorServiceFeePercent=$sf_val->percent;
                                    break;
                                }
                            }
                            $amount += $price * $quantity;
                            $orderItems[] = [
                                'product_id' => $product->product_id,
                                'product_model_id' => $product->product_model_id,
                                'vendor_id' => $product->vendor_id,
                                'vendor_name' => $product->vendor_name,
                                'sku' => $sku,
                                'product_name' => $product->product_name,
                                'unit_name' => $product->unit_name,
                                'price' => $price,
                                'gross_weight' => $product->gross_weight,
                                'net_weight' => $product->gross_weight,
                                'quantity' => $quantity,
                                'vendor_service_fee_percent' => $vendorServiceFeePercent,
                                'shipping_verdor_percent' => $product->shipping_verdor_percent,
                                'product_service_fee_percent' => $product->product_service_fee_percent,
                                'admin_memo' => $adminMemo,
                                'is_tax_free' => $product->is_tax_fee,
                            ];
                        }
                    }
                    // 儲存資料
                    $order = OrderDB::create([
                        'order_number' => $orderNumber,
                        'user_id' => $userId,
                        'origin_country' => $originCountry,
                        'ship_to' => $shipTo,
                        'from' => $from,
                        'to' => $to,
                        'book_shipping_date' => $bookShippingDate,
                        'receiver_name' => $receiverName,
                        'receiver_address' => $receiverAddress,
                        'shipping_method' => $shippingMethod,
                        'invoice_type' => $invoiceType,
                        'invoice_sub_type' => $invoiceSubType,
                        'spend_point' => $spendPoint,
                        'amount' => $amount,
                        'shipping_fee' => $shippingFee,
                        'parcel_tax' => $parcelTax,
                        'pay_method' => $payMethod,
                        'exchange_rate' => $exchangeRate,
                        'discount' => $discount,
                        'user_memo' => $userMemo,
                        'partner_order_number' => $partnerOrderNumber,
                        'pay_time' => $payTime,
                        'buyer_email' => $buyerEmail,
                        'print_flag' => $printFlag,
                        'create_type' => $partnerId,
                        'status' => $status,
                        'created_at' => $createTime,
                        'updated_at' => $updateTime,
                    ]);
                    if(!empty($orderItems)){
                        foreach($orderItems as $item){
                            $item['order_id'] = $order->id;
                            OrderItemDB::create($item);
                        }
                    }
                }
            }
        }
    }

    protected function digiwinOrder()
    {
        $createdTime = $this->param['created_at'];
        $exchangeRate = SystemSettingDB::first()->exchange_rate_RMB;
        //將資料重新撈出
        $rows = DigiwinImportOrderDB::where('created_at',$createdTime)
            ->whereNotIn('order_number',OrderDB::whereNotNull('partner_order_number')->where('user_id',2020)->select('partner_order_number')->groupBy('partner_order_number')->get())
            ->select([
                '*',
                DB::raw("unix_timestamp(CONCAT(LEFT(colA,4),'-',SUBSTR(colA,5,2),'-',SUBSTR(colA,7,2),' ',SUBSTR(colA,9,2),':',SUBSTR(colA,11,2),':',RIGHT(colA,2))) as ctime"),
                DB::raw("CONCAT(LEFT(colB,4),'-',SUBSTR(colB,5,2),'-',SUBSTR(colB,7,2),' ',SUBSTR(colB,9,2),':',SUBSTR(colB,11,2),':',RIGHT(colB,2)) as pay_time"),
                DB::raw("CONCAT(LEFT(colB,4),'-',SUBSTR(colB,5,2),'-',SUBSTR(colB,7,2),' ',SUBSTR(colB,9,2),':',SUBSTR(colB,11,2),':',RIGHT(colB,2)) as order_create_time"),
                'pay_method' => DigiwinPaymentDB::whereColumn('digiwin_payments.customer_no','digiwin_import_orders.colC')->select('customer_name')->limit(1),
            ])->orderBy('order_number','asc')->get();
        if(!empty($rows)){
            //整理資料
            $userId = 2020;
            $userMobile = '+886906486688';
            $buyerEmail = $userEmail = 'icarry4tw@gmail.com';
            $userName = '鼎新';
            $partnerId = '鼎新';
            $invoiceType = 2;
            $invoiceSubType = 2;
            $printFlag = 'N';
            $status = 1;
            foreach($rows as $row){ //依據訂單號碼將items集中成一個陣列
                $orders[$row->order_number][] = $row;
            }
            $originCountry = '台灣';
            $shipTo = null;
            $from = 0;
            $to = 0;
            $receiverAddress = null;
            $receiverKeyword = null;
            $receiverKeyTime = null;
            $shippingMethod = null;
            $adminMemo = null;
            //所有訂單
            if(!empty($orders)){
                foreach($orders as $key => $rows){
                    $orderItems = [];
                    $amount = $discount = $spendPoint = $shippingFee = $parcelTax = 0;
                    foreach($rows as $row){ //訂單中所有item
                        $partnerId = $payMethod = $userName = $this->customerNo($row->colC,$row->pay_method);
                        if($userName == 'admin'){
                            $payMethod = $row->pay_method;
                            $partnerId = null;
                        }
                        $status = 1;
                        if(!empty($row->colQ)){
                            if($row->colQ === '0' || $row->colQ === 0){
                                $status = 0;
                            }
                        }
                        $receiverEmail = $buyerEmail = $row->colR;
                        $orderNumber = date('ymdHis',$row->ctime).rand(10,99); //12碼+2
                        if($row->colK == '寄送當地' || $row->colK == '寄送台灣'){
                            $shippingMethod = 6;
                            $originCountry = '台灣';
                            $shipTo = '台灣';
                            $from = CountryDB::where('name',$originCountry)->select('id')->first()->id;
                            $to = CountryDB::where('name',$shipTo)->select('id')->first()->id;
                            $receiverAddress = '台灣 '.$row->colD;
                            $receiverKeyTime = str_replace('/','-',$row->colJ.' '.$row->colG);
                            $receiverKeyword = null;
                        }elseif($row->colK == '寄送海外'){
                            $shippingMethod = 4;
                            $originCountry = '台灣';
                            $shipTo = '國外';
                            $from = CountryDB::where('name',$originCountry)->select('id')->first()->id;
                            $to = 30;
                            $receiverAddress = $row->colD;
                            $receiverKeyTime = null;
                            $receiverKeyword = null;
                        }elseif($row->colK == '機場提貨'){
                            $shippingMethod = 1;
                            $originCountry = '台灣';
                            $shipTo = '台灣';
                            $from = CountryDB::where('name',$originCountry)->select('id')->first()->id;
                            $to = CountryDB::where('name',$shipTo)->select('id')->first()->id;
                            $receiverAddress = $row->colD;
                            $receiverKeyTime = str_replace('/','-',$row->colJ.' '.$row->colG);
                            $receiverKeyword = $row->colF;
                        }elseif($row->colK == '旅店提貨'){
                            $shippingMethod = 2;
                            $originCountry = '台灣';
                            $shipTo = '台灣';
                            $from = CountryDB::where('name',$originCountry)->select('id')->first()->id;
                            $to = CountryDB::where('name',$shipTo)->select('id')->first()->id;
                            $receiverAddress = $row->colD;
                            $receiverKeyTime = str_replace('/','-',$row->colJ.' '.$row->colG);
                            $tmp=explode('-',$row->colD);
                            $receiverKeyword = $tmp[0];
                        }

                        $updateTime = $createTime = $row->order_create_time;
                        $payTime = $row->pay_time;
                        $receiverName = $row->colI;
                        $receiverTel = $row->colL;

                        if(stristr($row->order_number,'iCarry')){
                            $partnerOrderNumber = $row->order_number;
                            $userMemo = $row->colE;
                        }else{
                            $partnerOrderNumber = $row->order_number;
                            $userMemo = "({$userName}訂單編號: {$row->order_number})，{$row->colE}";
                        }
                        if(!empty($row->colP)){
                            $bookShippingDate = substr($row->colP,0,4)."-".substr($row->colP,4,2)."-".substr($row->colP,6,2);
                        }else{
                            $bookShippingDate = null;
                        }

                        //檢查日期格式
                        if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $receiverKeyTime)){
                            $receiverKeyTime = null;
                        }
                        //檢查日期格式
                        if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $bookShippingDate)){
                            $bookShippingDate = null;
                        }
                        //檢查日期格式
                        if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $payTime)){
                            $payTime = null;
                        }

                        $quantity = $row->colN;
                        $price = $row->colO;
                        $sku = $row->colM;

                        if($sku == '999000' || $sku == '999001' || $sku == '901001' || $sku == '901002'){
                            if($sku == '999000'){
                                $discount = $price;
                            }elseif($sku == '999001'){
                                $spendPoint = $price;
                            }elseif($sku == '901001'){
                                $shippingFee = $price;
                            }elseif($sku == '901002'){
                                $parcelTax = $price;
                            }
                        }else{
                            $product = ProductModelDB::join('products','products.id','product_models.product_id')
                                ->join('vendors','vendors.id','products.vendor_id')
                                ->join('product_unit_names','product_unit_names.id','products.unit_name_id')
                                ->where('sku',$sku)
                                ->select([
                                    'product_models.id as product_model_id',
                                    'products.id as product_id',
                                    'products.name as product_name',
                                    'products.price',
                                    'products.gross_weight',
                                    'products.net_weight',
                                    'products.is_tax_free',
                                    'products.service_fee_percent as product_service_fee_percent',
                                    'vendors.id as vendor_id',
                                    'vendors.name as vendor_name',
                                    'vendors.service_fee as vendor_service_fee',
                                    'vendors.shipping_verdor_percent',
                                    'product_unit_names.name as unit_name',
                                ])->withTrashed()->first();
                            if(!empty($product)){
                                $adminMemo="原價：{$product->price}";
                                $vendorServiceFee = $this->serviceFee($product->vendor_service_fee);
                                foreach($vendorServiceFee as $sf_key=>$sf_val){
                                    if($sf_val->name=="iCarry"){
                                        $vendorServiceFeePercent=$sf_val->percent;
                                        break;
                                    }
                                }
                                $amount += $product->price * $quantity;
                                $orderItems[] = [
                                    'product_id' => $product->product_id,
                                    'product_model_id' => $product->product_model_id,
                                    'vendor_id' => $product->vendor_id,
                                    'vendor_name' => $product->vendor_name,
                                    'sku' => $sku,
                                    'product_name' => $product->product_name,
                                    'unit_name' => $product->unit_name,
                                    'price' => $product->price,
                                    'gross_weight' => $product->gross_weight,
                                    'net_weight' => $product->gross_weight,
                                    'quantity' => $quantity,
                                    'vendor_service_fee_percent' => $vendorServiceFeePercent,
                                    'shipping_verdor_percent' => $product->shipping_verdor_percent,
                                    'product_service_fee_percent' => $product->product_service_fee_percent,
                                    'admin_memo' => $adminMemo,
                                    'is_tax_free' => $product->is_tax_fee,
                                ];
                            }
                        }
                    }
                    // 儲存資料
                    $order = OrderDB::create([
                        'order_number' => $orderNumber,
                        'user_id' => $userId,
                        'origin_country' => $originCountry,
                        'ship_to' => $shipTo,
                        'from' => $from,
                        'to' => $to,
                        'book_shipping_date' => $bookShippingDate,
                        'receiver_name' => $receiverName,
                        'receiver_tel' => $receiverTel,
                        'receiver_email' => $receiverEmail,
                        'receiver_address' => $receiverAddress,
                        'receiver_keyword' => $receiverKeyword,
                        'receiver_key_time' => $receiverKeyTime,
                        'shipping_method' => $shippingMethod,
                        'invoice_type' => $invoiceType,
                        'invoice_sub_type' => $invoiceSubType,
                        'spend_point' => $spendPoint,
                        'amount' => $amount,
                        'shipping_fee' => $shippingFee,
                        'parcel_tax' => $parcelTax,
                        'pay_method' => $payMethod,
                        'exchange_rate' => $exchangeRate,
                        'discount' => $discount,
                        'user_memo' => $userMemo,
                        'partner_order_number' => $partnerOrderNumber,
                        'pay_time' => $payTime,
                        'buyer_email' => $buyerEmail,
                        'print_flag' => $printFlag,
                        'create_type' => $partnerId,
                        'status' => $status,
                    ]);
                    if(!empty($orderItems)){
                        foreach($orderItems as $item){
                            $item['order_id'] = $order->id;
                            OrderItemDB::create($item);
                        }
                    }
                }
            }
        }
    }

    protected function yiruiOrder($file)
    {
        //宜睿匯入檔案為文字檔案, 直接使用 file_get_contents 抓出資料, 一筆訂單一個檔案
        $rowData = file_get_contents($file);
        $rowData = 'bbc73d5b-4f06-443c-b079-6ebd51e9aa1c,2021111701624528,,海邊走走花生愛餡蛋捲單盒即享券,EC00295011134,1,520.0000,520.0000,郭華萍,苗栗縣,357,通東里福德路11巷3號,0938712221,,show11077@yahoo.com.tw,,';
        if($rowData){
            $content = explode(',',$rowData);
            if(!empty($content) && is_array($content)){
                $userId=54961;
                $userMobile='+886906486688';
                $userEmail='icarry@icarry.me';
                $shippingMethod = 5; //寄送台灣
                $createType = 'yirui'; //建立方式
                $payMethod = $userName= '宜睿';
                $shippingMemo = '';
                $shippingNumber = '';
                $invoiceType = 2;
                $invoiceSubType=2;
                $printFlag = 'N';
                $carrierType = '';
                $amount = $price = $discount = $shippingFee = $parcelTax = $spendPoint = $exchange_rate = 0;
                $status = 1;
                $originCountry = '台灣';
                $shipTo = '台灣';
                $from = 1;
                $to = 1;
                $buyerEmail = $userEmail;
                $orderNumber = date("ymdHis").rand(10,99); //12碼+2
                $partnerOrderNumber = $content[1];
                $sku = $content["4"];//貨號
                $quantity = $content["5"];//商品數量
                $price = (int)$content["6"];//商品單價 強轉int
                $amount = (int)$content["7"];//訂單總價 強轉int
                $receiverName = $content["8"];//收件人姓名
                $receiverAddress = "{$content["9"]} {$content["10"]} {$content["11"]}";//收件人地址 分別對應城市+郵遞碼+收件人地址
                $receiverTel = empty($content["13"])?$content["12"]:$content["13"];//如果沒有收件人{13}就拿{12}來用
                $receiverTel = '+886'.$this->bigintval($receiverTel);//台灣的電話號碼
                $receiverEmail = $content["14"];//收件人email
                $receiverKeyTime = null;//提貨日期
                $receiverKeyword = null;
                $bookShippingDate = null;
                $exchangeRate = null;
                $partnerOrderNumber = 'a12345678900';
                if(!empty($content["15"])){//提貨日期是yyyyMMdd 要分割成yyyy-MM-dd 00:00:00 這邊直接加入''因為有機會出現NULL(NULL是不能加''的關係)
                    $receiverKeyTime =  substr($content["15"],0,4).'-'.substr($content["15"],4,2).'-'.substr($content["15"],6,2).' 00:00:00';
                    $receiverKeyTime = "'{$receiverKeyTime}'";
                }
                strstr($receiverKeyTime,'NULL') ? $receiverKeyTime = null : '';
                $userMemo = "宜睿唯一碼:{$content["0"]},宜睿訂編:{$content["1"]},提貨日期:{$content["15"]},訂單備註:{$content["16"]}";
                $payTime = substr($content["1"],0,4).'-'.substr($content["1"],4,2).'-'.substr($content["1"],6,2).' 00:00:00';//時間
                $chk = OrderDB::where('partner_order_number',$partnerOrderNumber)
                    ->select([
                        'id',
                        DB::raw("count(id) as order_count"),
                        'order_item_count' => OrderItemDB::whereColumn('order_id','orders.id')->select([DB::raw("count(id) as count")])->limit(1),
                    ])->first();
                if($chk->order_count == 0){ //訂單不存在建立新的訂單
                    // 儲存訂單資料
                    $order = OrderDB::create([
                        'order_number' => $orderNumber,
                        'user_id' => $userId,
                        'origin_country' => $originCountry,
                        'ship_to' => $shipTo,
                        'from' => $from,
                        'to' => $to,
                        'book_shipping_date' => $bookShippingDate,
                        'receiver_name' => $receiverName,
                        'receiver_tel' => $receiverTel,
                        'receiver_email' => $receiverEmail,
                        'receiver_address' => $receiverAddress,
                        'receiver_keyword' => $receiverKeyword,
                        'receiver_key_time' => $receiverKeyTime,
                        'shipping_method' => $shippingMethod,
                        'invoice_type' => $invoiceType,
                        'invoice_sub_type' => $invoiceSubType,
                        'spend_point' => $spendPoint,
                        'amount' => $amount,
                        'shipping_fee' => $shippingFee,
                        'parcel_tax' => $parcelTax,
                        'pay_method' => $payMethod,
                        'exchange_rate' => $exchangeRate,
                        'discount' => $discount,
                        'user_memo' => $userMemo,
                        'partner_order_number' => $partnerOrderNumber,
                        'pay_time' => $payTime,
                        'buyer_email' => $buyerEmail,
                        'print_flag' => $printFlag,
                        'create_type' => $createType,
                        'status' => $status,
                    ]);
                    $orderId = $order->id;
                }elseif($chk->order_item_count == 0){ //訂單存在, 補item資料
                    $orderId = $chk->id;
                }
                $product = ProductModelDB::join('products','products.id','product_models.product_id')
                ->join('vendors','vendors.id','products.vendor_id')
                ->join('product_unit_names','product_unit_names.id','products.unit_name_id')
                ->where('sku',$sku)
                ->select([
                    'product_models.id as product_model_id',
                    'products.id as product_id',
                    'products.name as product_name',
                    'products.price',
                    'products.gross_weight',
                    'products.net_weight',
                    'products.is_tax_free',
                    'products.service_fee_percent as product_service_fee_percent',
                    'vendors.id as vendor_id',
                    'vendors.name as vendor_name',
                    'vendors.service_fee as vendor_service_fee',
                    'vendors.shipping_verdor_percent',
                    'product_unit_names.name as unit_name',
                ])->withTrashed()->first();
                if (!empty($product)) {
                    $adminMemo="原價：{$product->price}";
                    $vendorServiceFee = $this->serviceFee($product->vendor_service_fee);
                    foreach ($vendorServiceFee as $sf_key=>$sf_val) {
                        if ($sf_val->name=="iCarry") {
                            $vendorServiceFeePercent=$sf_val->percent;
                            break;
                        }
                    }
                    $orderItem = [
                        'order_id' => $orderId,
                        'product_id' => $product->product_id,
                        'product_model_id' => $product->product_model_id,
                        'vendor_id' => $product->vendor_id,
                        'vendor_name' => $product->vendor_name,
                        'sku' => $sku,
                        'product_name' => $product->product_name,
                        'unit_name' => $product->unit_name,
                        'price' => $price,
                        'gross_weight' => $product->gross_weight,
                        'net_weight' => $product->gross_weight,
                        'quantity' => $quantity,
                        'vendor_service_fee_percent' => $vendorServiceFeePercent,
                        'shipping_verdor_percent' => $product->shipping_verdor_percent,
                        'product_service_fee_percent' => $product->product_service_fee_percent,
                        'admin_memo' => $adminMemo,
                        'is_tax_free' => $product->is_tax_fee,
                    ];
                    OrderItemDB::create($orderItem);
                }
            }
        }
    }

    protected function shippingNumber($rows)
    {
        $createTime = date('Y/m/d H:i:s');
        for($i=0;$i<count($rows);$i++){
            $orderNumber = trim($rows[$i][1]);
            $shippingVendor = trim($rows[$i][3]);
            $shippingNumber = trim($rows[$i][2]);
            if(!empty($orderNumber)){ //匯入的資料表有可能空資料, 需先判斷訂單編號欄位是否有資料
                $order = OrderDB::where('order_number',$orderNumber)->first();
                if(!empty($order)){
                    $chkOrderShippings = OrderShippingDB::where('order_id',$order->id)->get();
                    if(count($chkOrderShippings) == 0 && empty($order->shipping_number)){
                        $shippingMemo = '[{"create_time":"'.$createTime.'","express_way":"'.$shippingVendor.'","express_no":"'.$shippingNumber.'"}]';
                        $order->update([
                            'shipping_number' => $shippingNumber,
                            'shipping_memo' => $shippingMemo,
                            'shipping_time' => $createTime,
                        ]);
                        OrderShippingDB::create([
                            'order_id' => $order->id,
                            'express_way' => $shippingVendor,
                            'express_no' => $shippingNumber,
                        ]);
                    }
                }
            }
        }
    }

    protected function orderShipment($rows)
    {
        $createTime = date('Y/m/d H:i:s');
        $param = [];
        for($i=0;$i<count($rows);$i++){
            $error = 0;
            $orderNumber = trim($rows[$i][1]);
            $shippingVendor = trim($rows[$i][3]);
            $shippingNumber = trim($rows[$i][2]);
            $shippingMemo = '[{"create_time":"'.$createTime.'","express_way":"'.$shippingVendor.'","express_no":"'.$shippingNumber.'"}]';
            if(!empty($orderNumber)){ //匯入的資料表有可能空資料, 需先判斷訂單編號欄位是否有資料
                $order = OrderDB::where('order_number',$orderNumber)->first();
                if(!empty($order)){ //訂單存在才做
                    $chkOrderShippings = OrderShippingDB::where('order_id',$order->id)->first();
                    if(!empty($chkOrderShippings) && empty($order->shipping_number)){ //匯入的資料有可能尚未填入物流單資料, 所以需要新增物流單資料
                        $order->update([
                            'shipping_number' => $shippingNumber,
                            'shipping_memo' => $shippingMemo,
                            'shipping_time' => $createTime,
                        ]);
                        OrderShippingDB::create([
                            'order_id' => $order->id,
                            'express_way' => $shippingVendor,
                            'express_no' => $shippingNumber,
                        ]);
                    }else{ //物流單資料存在, 比對與現有資料
                        if($order->shipping_number != $shippingNumber || $chkOrderShippings->express_way != $shippingVendor || $chkOrderShippings->express_no != $shippingNumber){
                            $error = 1;
                        }
                    }
                    //比對確認無誤, 將訂單轉狀態3並收集訂單ID與舊狀態資料
                    if($error == 0){
                        if($order->status < 3 && $order->status >= 1){ //訂單狀態有付款1且小於3才處理
                            $param['ids'][] = $order->id;
                            $param['oldStatus'][] = $order->status;
                            $order->update([
                                'status' => 3,
                                'shipping_time' => $createTime,
                            ]);
                        }
                    }
                }
            }
        }

        if(!empty($param)){
            //訂單狀態變更處理Job
            $param['return'] = false; //true 返回訊息 false 不返回
            // $result = AdminOrderStatusJob::dispatch($param); //放入隊列
            $result = AdminOrderStatusJob::dispatchNow($param); //馬上執行
        }
    }

    protected function customerNo($n,$payMethod){
        switch($n){
            case '001':return 'admin';break;
            case '002':return 'admin';break;
            case '003':return 'admin';break;
            case '004':return 'admin';break;
            case '005':return 'admin';break;
            case '006':return 'admin';break;
            case '007':return 'admin';break;
            case '008':return 'admin';break;
            case '009':return 'admin';break;
            case '037':return 'admin';break;
            case '012':return '客路';break;
            case '018':return 'hutchgo';break;
            case '023':return '生活市集';break;
            case '027':return 'myhuo';break;
            case '021':return '松果';break;
            case '028':return '17life';break;
            default:return $payMethod;break;
        }
    }

    /*
        整理Servce_fee資料
        1. 檢驗是否存在
        2. 檢驗是否為陣列
        3. 轉換percent空值為0
    */
    protected function serviceFee($input = ''){
        if($input == ''){
            $serviceFees = json_decode('[{"name":"天虹","percent":0},{"name":"閃店","percent":0},{"name":"iCarry","percent":0},{"name":"現場提貨","percent":0}]');
        }elseif(is_array($input)){
            for($i=0;$i<count($input['name']);$i++){
                $serviceFees[$i]['name'] = $input['name'][$i];
                $serviceFees[$i]['percent'] = $input['percent'][$i];
            }
            $serviceFees = json_encode($serviceFees);
        }else{
            $serviceFees = json_decode(str_replace('"percent":}','"percent":0}',$input));
        }
        return $serviceFees;
    }

    protected function bigintval($value) {
        $value = trim($value);
        if (ctype_digit($value)) {
              if(substr( $value , 0 , 1 )=="0"){
                return substr( $value , 1 );
              }else{
                  return $value;
              }
        }
        $value = preg_replace("/[^0-9](.*)$/", '', $value);
        if (ctype_digit($value)) {
          if(substr($value,0,1)=="0"){
              $value=substr($value,1);
          }
          return $value;
        }
        return 0;
      }
}
