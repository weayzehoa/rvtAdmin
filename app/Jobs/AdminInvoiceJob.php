<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Order as OrderDB;
use App\Models\Pay2go as Pay2goDB;
use App\Models\SystemSetting as SystemSettingDB;
use App\Models\Spgateway as SpgatewayDB;

class AdminInvoiceJob implements ShouldQueue
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
        $systemSetting = SystemSettingDB::first();
        $ids = [];
        //發票開立供應商參數不存在，使用系統預設
        isset($param['supplier']) ? $supplier = strtolower($param['supplier']) : $supplier = strtolower($systemSetting->invoice_supplier);
        isset($param['id']) ? is_array($param['id']) ? $this->ids = $ids = $param['id'] : $this->ids = $ids = [$param['id']] : '';
        isset($param['return']) ? $returnFlag = $param['return'] : $returnFlag = false;
        isset($param['shopee']) ? $this->shopee = 1 : $this->shopee = null;
        isset($param['reason']) ? $reason = $param['reason'] : $reason = [];
        if(count($ids) > 0 && isset($param['type'])){
            if($supplier == 'ezpay'){
                $param['type'] == 'create' ? $result = $this->ezpayCreate($ids,'create') : '';
                $param['type'] == 'cancel' ? $result = $this->ezpayCancel($ids,'cancel',$reason) : '';
                if($returnFlag){
                    return $result;
                }
            }
        }else{
            return null;
        }
    }

    public function ezpayCreate($ids,$type)
    {
        if($type == 'create' && count($ids) > 0){
            $count = [];
            foreach($ids as $id){
                //找出訂單相關資料
                $order = OrderDB::with('user','items','spgateway')->findOrFail($id);
                if ($order->is_invoice != 1 && ($order->create_type != 'klook' && $order->create_type != 'ctrip')) {
                    //購買者信箱如果找不到改收貨者再找不到改成 icarry4tw@gmail.com
                    if (empty($order->buyer_email) || !stristr($order->buyer_email, '@') || substr($order->buyer_email, -1)=='@') {
                        $order->buyer_email = $order->receiver_email;
                        $order->buyer_email == '' ? $order->buyer_email = 'icarry4tw@gmail.com' : '';
                    }

                    //invoice_title與invoice_number相反時(抬頭與統編)
                    if (is_numeric($order->invoice_title) && !is_numeric($order->invoice_number)) {
                        $tmp = $order->invoice_title;
                        $order->invoice_title=$order->invoice_number;
                        $order->invoice_number=$tmp;
                    }

                    //購買者名稱最長30字限制
                    empty($order->buyer_name) ? $order->buyer_name = $order->user->name : $order->buyer_name = $order->user->id;
                    $order->buyer_name = mb_substr($order->buyer_name, 0, 30, 'utf-8');

                    //處理載具號碼
                    if ($order->carrier_num) {
                        $order->carrier_num = str_replace('／', '/', $order->carrier_num);
                        substr($order->carrier_num, 0, 1) != '/' ? '/'.$order->carrier_num : '';
                    }

                    //處理商品資料
                    foreach ($order->items as $item) {
                        $item->product_name = str_replace([
                            ' ','','|','\t','【即日起預購至12/18止，12/19依序出貨】',
                            '【01/26依序出貨，暫不寄送中國大陸】','【01/26依序出貨】',
                            '【新年預購】','收藏天地-台湾文創禮品館-'
                        ], ['','','','','','','','',''], $item->product_name); //去除不要的字
                        $productName[] = mb_substr($item->product_name, 0, 120, 'utf-8'); //最多120個字
                        $productUnitName[] = $item->unit_name;
                        $productPrice[] = $item->price;
                        $productQuantity[] = $item->quantity;
                        $productSubtotal[] = $item->quantity * $item->price;
                    }

                    //使用購物金
                    if ($order->spend_point > 0) {
                        $productName[] ='購物金折抵';
                        $productQuantity[] = 1;
                        $productUnitName[] = 'pcs';
                        $productPrice[] = -$order->spend_point;
                        $productSubtotal[] = -$order->spend_point;
                    }

                    //折扣
                    if ($order->discount > 0) {
                        $productName[] ='活動折抵';
                        $productQuantity[] = 1;
                        $productUnitName[] = 'pcs';
                        $productPrice[] = -$order->discount;
                        $productSubtotal[] = -$order->discount;
                    }elseif ($order->discount < 0) {
                        $productName[] ='活動折抵';
                        $productQuantity[] = 1;
                        $productUnitName[] = 'pcs';
                        $productPrice[] = $order->discount;
                        $productSubtotal[] = $order->discount;
                    }

                    //跨境稅
                    if ($order->parcel_tax > 0) {
                        $productName[] ='跨境稅';
                        $productQuantity[] = 1;
                        $productUnitName[] = 'pcs';
                        $productPrice[] = -$order->parcel_tax;
                        $productSubtotal[] = -$order->parcel_tax;
                    }

                    //運費
                    if ($order->shipping_fee > 0) {
                        $productName[] ='運費';
                        $productQuantity[] = 1;
                        $productUnitName[] = 'pcs';
                        $productPrice[] = -$order->shipping_fee;
                        $productSubtotal[] = -$order->shipping_fee;
                    }

                    //ezPay參數
                    $pay2go['HashKey'] = env('ezPay_HashKey');                  //HashKey
                    $pay2go['HashIV'] = env('ezPay_HashIV');                    //HashIV
                    $pay2go['MerchantID'] = env('ezPay_MerchantID');            //MerchantID
                    $pay2go['API'] = env('ezPay_ISSUE_URL');                    //API URL
                    $pay2go['RespondType'] = 'JSON';                            //回傳格式
                    $pay2go['Version'] = '1.4';                                 //API版本
                    $pay2go['TimeStamp'] = time();                              //時間序
                    $pay2go['Status'] = 1;                                      //1=即時開立發票
                    $pay2go['TaxType'] = 1;                                     //稅別
                    $pay2go['TaxRate'] = 5;                                     //稅率 5%
                    $pay2go['MerchantOrderNo'] = $order->order_number;          //商店訂單編號
                    $pay2go['BuyerName'] = $order->buyer_name;                  //買受人名稱
                    $pay2go['BuyerEmail'] = $order->buyer_email;                //買受人電子信箱
                    $pay2go['BuyerAddress'] = $order->invoice_address;          //買受人地址 非必填
                    $pay2go['Category'] = 'B2C';                                //B2B=買受人為營業人(有統編)。三聯時B2B
                    $pay2go['BuyerUBN'] = '';                                   //買受人統一編號 B2B時須填寫
                    $pay2go['PrintFlag'] = $order->print_flag;                  //索取紙本發票
                    $pay2go['LoveCode'] = $order->love_code;                    //愛心碼 (當 Category=B2C 時，才適用此參數)
                    $pay2go['CarrierType'] = $order->carrier_type;              //Category=B2C 時，才適用此參數
                    $pay2go['CarrierNum'] = rawurlencode($order->carrier_num);  //1.若 CarrierType 參數有提供數值時，則此參數為必填。
                    $pay2go['ItemName'] = join('|', $productName);               //商品名稱。多項商品時，商品名稱以 | 分隔。例：ItemName=”商品一|商品二” [店家-商品名稱]
                    $pay2go['ItemCount'] = join('|', $productQuantity);          //商品數量。1.純數字。2.多項商品時，商品數量以 |分隔。例：ItemCount =”1|2”
                    $pay2go['ItemUnit'] = join('|', $productUnitName);           //商品單位。1.內容如：個、件、本、張…..。2.多項商品時，商品單位以 | 分隔。例：ItemUnit =”個|本”
                    $pay2go['ItemPrice'] = join('|', $productPrice);             //多項商品時，商品單價以 | 分隔。例：ItemPrice =”200|100”，Category=B2B 時，此參數金額為未稅金額。 [商品單價1000元此處填寫為1000/1.05]，Category=B2C 時，此參數金額為含稅金額。 [商品單價1000元此處填寫為1000]
                    $pay2go['ItemAmt'] = join('|', $productSubtotal);            //多項商品時，商品小計以 | 分隔。例：ItemAmt =”200|200”

                    //發票類型
                    if ( $order->invoice_type !=3 ) {
                        $pay2go['CarrierType'] == '' && $pay2go['LoveCode']=='' ? $pay2go['PrintFlag']='Y' : $pay2go['PrintFlag']='N';

                        if ($pay2go['BuyerUBN'] == '') {
                            unset($pay2go['BuyerUBN']);
                        }

                        if ($pay2go['LoveCode'] == '') {
                            unset($pay2go['LoveCode']);
                        } else {
                            unset($pay2go['CarrierType']);
                            unset($pay2go['CarrierNum']);
                        }
                    } else {                                                    //三聯式
                        $pay2go['Category'] = 'B2B';                            //改變類別為 B2B
                        $pay2go['BuyerName'] = $order->invoice_title;           //買受人名稱置換成抬頭
                        $pay2go['BuyerUBN'] = $order->invoice_number;           //買受人統編
                        $pay2go['PrintFlag']='Y';                               //B2B 類別的發票，只能選擇索取發票
                        unset($pay2go['LoveCode']);                             //清空載具類別
                        unset($pay2go['CarrierType']);                          //清空載具號碼
                        unset($pay2go['CarrierNum']);                           //清空愛心碼
                        $tmp='';
                        foreach ($productPrice as $gpp) {
                            $tmp.='|'.round($gpp/1.05, 2);
                        }
                        $pay2go['ItemPrice'] = substr($tmp, 1);                  //Category=B2B 時，此參數金額為未稅金額。 [商品單價1000元此處填寫為1000/1.05]
                        $tmp='';
                        foreach ($productSubtotal as $gpp) {
                            $tmp.='|'.round($gpp/1.05, 2);
                        }
                        $pay2go['ItemAmt'] = substr($tmp, 1);                    //Category=B2B 時，此參數金額為未稅金額。
                    }

                    //發票金額 [訂單1000元此處填寫為1000]
                    $pay2go['TotalAmt'] = ($order->amount + $order->shipping_fee + $order->parcel_tax - $order->spend_point - $order->discount);
                    $pay2go['Amt'] = round($pay2go['TotalAmt'] / 1.05, 0);       //銷售額合計 [訂單1000元此處填寫為1000/1.05]
                    $pay2go['TaxAmt'] = $pay2go['TotalAmt'] - $pay2go['Amt'];   //稅額 [訂單1000元此處填寫為1000-(1000/1.05)]

                    $pay2go['Comment'] = "訂單號碼：$order->order_number";
                    if (strstr($order->pay_method, '智付通')) {
                        $spgatewayResult = $order->spgateway()->orderBy('created_at', 'desc')->select('result_json->Result')->get()->first();
                        foreach (json_decode($spgatewayResult, true) as $tmp) {
                            $spResult = $tmp;
                        }
                        $sp = json_decode($spResult, true);
                        $pay2go['TransNum'] = $sp['TradeNo'];//智付寶平台交易序號
                        if (strstr($order->pay_method, '信用卡')) {
                            $Card4No = $sp['Card4No'];
                            $pay2go['Comment']="訂單號碼： $order->order_number ，信用卡末四碼： $Card4No ";
                        }
                    }

                    //當訂單為以下情況【全部符合】時，2021/4/1 起改開零稅率發票
                    $yyyymmdd=intval(date('Ymd'));
                    if ($yyyymmdd>=20210401) {
                        if ($order->from == 1 && $order->to != 1 && $pay2go['Category']=='B2C') {
                            if ($pay2go['PrintFlag'] == 'Y' || $pay2go['LoveCode'] != '') {
                                if ($order->create_type == 'shopee' && strstr($order->user_memo, '(新加坡)')) {
                                    $pay2go['TaxType'] = 2;
                                    $pay2go['TaxRate'] = 0;
                                    $pay2go['CustomsClearance'] = '';
                                    $pay2go['Amt'] = $pay2go['TotalAmt'];
                                    $pay2go['TaxAmt'] = 0;
                                    $pay2go['CustomsClearance'] = 2;
                                } elseif ($order->create_type == 'app' || $order->create_type == 'kiosk' || $order->create_type == 'admin' || $order->create_type == 'web' || $order->create_type == 'Amazon' || $order->create_type == 'vendor') {
                                    $pay2go['TaxType'] = 2;
                                    $pay2go['TaxRate'] = 0;
                                    $pay2go['CustomsClearance'] = '';
                                    $pay2go['Amt'] = $pay2go['TotalAmt'];
                                    $pay2go['TaxAmt'] = 0;
                                    $pay2go['CustomsClearance'] = 2;
                                }
                            }
                        }
                    }

                    if (!empty($this->shopee) && $this->shopee == 1) {
                        $pay2go['Category'] = 'B2B';                            //改變類別為 B2B
                        $pay2go['BuyerName']='樂購商城有限公司';                  //買受人名稱
                        $pay2go['BuyerUBN']='52945710';
                        $pay2go['BuyerAddress']='台北市信義區菸廠路88號9樓';       //買受人地址 非必填
                        $pay2go['BuyerEmail']='joyce.hsu@shopee.com';            //買受人電子信箱
                        $pay2go["Comment"]=$pay2go["Comment"]." 蝦皮訂單編號：".$order->partner_order_number;
                    }

                    $result = $this->ezpayPost($pay2go);
                    $pay2goInfo = json_decode($result['info'],true);
                    if(!empty($pay2goInfo['Result'])){
                        $pay2goResult = json_decode($pay2goInfo['Result'],true);
                    }

                    $message = $pay2goInfo['Status'].','.$pay2goInfo['Message'];

                    Pay2goDB::create([
                        'order_number' => $order->order_number,
                        'post_json' => json_encode($pay2go, JSON_UNESCAPED_UNICODE),
                        'get_json' => $message,
                    ]);

                    if(!empty($pay2goResult['InvoiceNumber'])){
                        $count['success'][] = $order->order_numbear;
                        $order->update([
                            'is_invoice' => 1,
                            'is_invoice_no' =>$pay2goResult['InvoiceNumber'],
                            'invoice_time' => GETDATE(),
                            'invoice_memo' => $message,
                        ]);
                    }else{
                        if ($message == 'INV10002,手機條碼載具格式錯誤' || $message == 'INV10002,自然人憑證載具格式錯誤') {
                            $count['fail'][] = $id;
                            $order->update([
                                'invoice_memo' => $message,
                                'carrier_type' => '',
                                'carrier_num' => '',
                            ]);
                        }else{
                            $count['fail'][] = $id;
                            $order->update(['invoice_memo' => $message]);
                        }
                    }
                }
            }
            return $count;
        }else{
            return null;
        }
    }

    public function ezpayCancel($ids,$type,$reason)
    {
        if ($type == 'cancel' && count($ids) > 0) {
            $count = [];
            if(count($ids) == count($reason)){
                for($i=0;$i<count($ids);$i++){
                    $order = OrderDB::findOrFail($ids[$i]);
                    $pay2go=[
                        'HashKey'=>env('ezPay_HashKey'),
                        'HashIV'=>env('ezPay_HashIV'),
                        'MerchantID'=>env('ezPay_MerchantID'),
                        'RespondType'=>'JSON',
                        'Version'=>'1.0',
                        'TimeStamp'=>time(),
                        'InvoiceNumber'=>$order->is_invoice_no, //發票號碼
                        'InvalidReason'=> mb_substr($reason[$i],0,12),
                        'API'=> env('ezPay_INVALID_URL'),
                    ];
                    $result = $this->ezpayPost($pay2go);
                    $short=array('InvoiceNumber'=>$order->is_invoice_no,'InvalidReason'=>mb_substr($reason[$i],0,12));
                    $result['info'] = preg_replace('/^\xef\xbb\xbf/', '', $result['info']);
                    $info=json_decode($result['info'],true);

                    Pay2goDB::create([
                        'order_number' => $order->order_number,
                        'post_json' => json_encode($short),
                        'get_json' => json_encode($result),
                    ]);

                    if($info['Status']=='SUCCESS' || $info['Status']=='LIB10005'){
                        $count['success'][] = $order->order_numbear;
                        $order->update([
                            'is_invoice' => 2,
                            'is_invoice_cancel' => 1,
                            'invoice_memo' => '發票號碼：'.$order->is_invoice_no.'已作廢成功',
                        ]);
                    }else{
                        $count['fail'][] = $order->order_numbear;
                        $order->update([
                            'invoice_memo' => '作廢失敗',
                        ]);
                    }
                }
            }
            return $count;
        }else{
            return null;
        }
    }

    public function ezpayPost($postDataArray)
    {
        $key = env('ezPay_HashKey');                        //HashKey
        $iv = env('ezPay_HashIV');                          //HashIV
        $MerchantID = env('ezPay_MerchantID');              //MerchantID
        $url = $postDataArray['API'];                       //連接網址
        unset($postDataArray['HashIV']);                    //不放入postData
        unset($postDataArray['MerchantID']);                //不放入postData
        unset($postDataArray['HashKey']);                   //不放入postData
        unset($postDataArray['API']);                       //不放入postData
        $postDataStr = http_build_query($postDataArray);    //轉成字串排列

        if (phpversion() > 7) {
            //php 7 以上版本加密
            $postData = trim(bin2hex(openssl_encrypt($this->addpadding($postDataStr),'AES-256-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv)));
        } else {
            //php 7 之前版本加密
            $postData = trim(bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,$this->addpadding($postDataStr), MCRYPT_MODE_CBC, $iv)));
        }

        $transactionArray = array( //送出欄位
            'MerchantID_' => $MerchantID,
            'PostData_' => $postData
        );
        $transactionStr = http_build_query($transactionArray);
        return $info = $this->curl($url, $transactionStr);
        /*
            Array ( [url] => https://inv.pay2go.com/API/invoice_issue [parameter] => 亂碼 [status] =>200 [error] => 0 [result] =>{'Status':'SUCCESS','Message':'\u96fb\u5b50\u767c\u7968\u958b\u7acb\u6210\u529f','Result':'{\'CheckCode'=>'C4156CA208897278C84D929DE48F4A2BCD1FF3ED4B97D09A14E2E2143E3EFD2E','MerchantID'=>'3622183','MerchantOrderNo'=>'201409170000001','InvoiceNumber'=>'UY25000014','TotalAmt\':500,\'InvoiceTransNo'=>'14061313541640927','RandomNum'=>'0142','CreateTime'=>'2014-06-13 13:54:16\'}'} )
        */
    }

    function addPadding($string, $blocksize = 32) {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }

    function curl($url = '', $parameter = '') {
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'Google Bot',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_POST => '1',
            CURLOPT_POSTFIELDS => $parameter
        );
        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $retcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_errno($ch);
        curl_close($ch);
        $info = array(
            'url' => $url,
            'sent_parameter' => $parameter,
            'http_status' => $retcode,
            'curl_error_no' => $error,
            'info' => $result
        );
        return $info;
    }
}
