<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Spgateway as SpgatewayDB;
use Curl;

trait NewebPayTrait
{
    public function __construct()
    {
    }

    public function newebPay($method, $orderNumber, $amount, $email, $memo = '')
    {
        $this->payMethod = ['智付通信用卡' => 'CREDIT', '智付通ATM' => 'VACC', '智付通CVS' => 'CVS', '智付通銀聯卡' => 'UNIONPAY'];
        $this->method = $method;
        $this->orderNumber = $orderNumber;
        $this->amount = $amount;
        $this->email = $email;
        $this->memo = $memo;
        $this->payStatus = 0;
        $this->paymentType = $this->payMethod[$this->method];
        $array = [
            'MerchantOrderNo' => $orderNumber,  //商店訂單編號V
            'Amt' => $amount,                   //訂單金額V
            'OrderComment' => $memo,            //商店備註V
            'Email' => $email,                  //付款人電子信箱 = 於交易完成或付款完成時，通知付款人使用。
            $this->paymentType => 1,
        ];
        $this->spgateway = array_merge($this->newebSetting(), $array);
        $this->postJson = json_encode($this->spgateway,true);
        $log = $this->newebLog();
        $form = $this->newebMakeForm();
        return $form;
    }

    //線上授權取消
    public function newebpayCreditCancel($orderNumber)
    {
        env('APP_ENV') != 'production' ? $key = env('NEWEBPAY_TEST_HASH_KEY') : $key = env('NEWEBPAY_HASH_KEY');
        env('APP_ENV') != 'production' ? $iv = env('NEWEBPAY_TEST_HASH_IV') : $iv = env('NEWEBPAY_HASH_IV');
        env('APP_ENV') != 'production' ? $MerchantID = env('NEWEBPAY_TEST_MERCHANT_ID') : $MerchantID = env('NEWEBPAY_MERCHANT_ID');
        env('APP_ENV') != 'production' ? $url = env('NEWEBPAY_TEST_CREDITCARD_CANCEL_URL') : $url = env('NEWEBPAY_CREDITCARD_CANCEL_URL');
        $spgateway = SpgatewayDB::where('order_number',$orderNumber)->first();
        if(!empty($spgateway) && $spgateway->PaymentType == 'CREDIT'){
            $array = [
                "RespondType"=>"JSON",
                "Version"=>"1.0",
                "Amt"=>$spgateway->amount,
                "MerchantOrderNo"=>$orderNumber,
                "IndexType"=>1, //1 表示使用商店訂單編號，2 表示使用藍新金流交易單號
                "TimeStamp"=>time(),
            ];
            $arrayToStr = http_build_query($array); //轉成字串排列
            $addPadding = $this->newebAddPadding($arrayToStr);
            $postData  = $this->newebEncrypt($array); //加密
            $data = [ //送出欄位
                "MerchantID_" => $MerchantID,
                "PostData_" => $postData
            ];
            $dataToStr = http_build_query($data);
            $resultJson = Curl::to($url)->withData( $dataToStr )->post();
            $result = json_decode($resultJson, true);
            if($result['Status'] == 'SUCCESS'){
                $memo = '取消訂單信用卡授權';
                $log = $this->newebLog($orderNumber,-3,json_encode($array,true),$resultJson,$memo,$type = 'update');
                return true;  //這邊返回 true 確認已被智付通取消
            }
        }
        return false; //這邊返回 false 表示無該訂單或智付通取消失敗
    }

    private function newebLog($orderNumber = null, $payStatus = null, $getJson = null, $resultJson = null, $memo = null ,$type = null)
    {
        if(!empty($type) && $type =='update'){
            $spgateway = SpgatewayDB::where('order_number',$orderNumber)->first();
            if(!empty($spgateway)){
                $spgateway = $spgateway->update([
                    'pay_status' => $payStatus,
                    'get_json' => $getJson,
                    'result_json' => $resultJson,
                    'memo' => $memo,
                ]);
                return $spgateway;
            }
        }else{
            $data = ['order_number' => $this->orderNumber, 'amount' => $this->amount, 'pay_status' => $this->payStatus, 'PaymentType' => $this->paymentType, 'memo' => $this->memo, 'post_json' => !empty($this->postJson) ? $this->postJson : null, 'get_json' => !empty($this->getJson) ? $this->getJson : null, 'result_json' => !empty($this->resultJson) ? $this->resultJson : null];
            $log = SpgatewayDB::create($data);
            return $log;
        }
    }

    private function newebMakeForm()
    {
        env('APP_ENV') != 'production' ? $apiUrl = env('NEWEBPAY_TEST_API_URL') : $apiUrl = env('NEWEBPAY_API_URL');
        $tradeInfo = $this->newebEncrypt($this->spgateway);
        $tradeSha = "HashKey=".env('NEWEBPAY_HASH_KEY')."&$tradeInfo&HashIV=".env('NEWEBPAY_HASH_IV');
        $tradeSha = strtoupper(hash("sha256", $tradeSha));
        $form = '<html><meta charset="UTF-8"><head></head><body><form id="spgateway_form" name="form1" method="post" action="'.$apiUrl.'">';
        $form .= '<input type="hidden" name="MerchantID" value="'.env('NEWEBPAY_MERCHANT_ID').'">';
        $form .= '<input type="hidden" name="TradeInfo" value="'.$tradeInfo.'">';
        $form .= '<input type="hidden" name="TradeSha" value="'.$tradeSha.'">';
        $form .= '<input type="hidden" name="Version" value="'.env('NEWEBPAY_VERSION').'">';
        $form .= '</form><script>document.getElementById("spgateway_form").submit();</script></body></html>';
        return $form;
    }

    private function newebSetting()
    {
        return [
            'TimeStamp' => time(),
            'Version' => env('NEWEBPAY_VERSION'),
            'RespondType' => 'JSON',
            'LangType' => 'zh-tw',//英文en
            "ExpireDate"=>date('Ymd', time()+3600),
            'ReturnURL' => env('NEWEBPAY_RETURN_URL'),//支付完成 返回商店網址
            'NotifyURL' => env('NEWEBPAY_NOTIFY_URL'),//支付通知網址
            'CustomerURL' => env('NEWEBPAY_CUSTOMER_URL'),//商店取號網址
            'ClientBackURL' => '',//支付取消 返回商店網址 V
            'TokenTerm' => '',//會員編號 = 可對應付款人之資料，用於綁定付款人與信用卡卡號時使用
            'MerchantID' => env('NEWEBPAY_MERCHANT_ID'),
            'ItemDesc' => 'iCarry我來寄 訂單',//商品資訊V
        ];
    }

    //官方說明文件提供 加密
    private function newebEncrypt($parameter = "")
    {
        env('APP_ENV') != 'production' ? $key = env('NEWEBPAY_TEST_HASH_KEY') : $key = env('NEWEBPAY_HASH_KEY');
        env('APP_ENV') != 'production' ? $iv = env('NEWEBPAY_TEST_HASH_IV') : $iv = env('NEWEBPAY_HASH_IV');
        $returnStr = '';
        if (!empty($parameter)) {
            //將參數經過 URL ENCODED QUERY STRING
            $returnStr = http_build_query($parameter);
        }
        return trim(bin2hex(openssl_encrypt($this->newebAddPadding($returnStr), 'aes-256-cbc', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $iv)));
    }

    //官方說明文件提供 加密副程式
    private function newebAddPadding($string, $blocksize = 32)
    {
        $len = strlen($string);
        $pad = $blocksize - ($len % $blocksize);
        $string .= str_repeat(chr($pad), $pad);
        return $string;
    }

    //官方說明文件提供 解密
    private function newebDecrypt($parameter = "")
    {
        env('APP_ENV') != 'production' ? $key = env('NEWEBPAY_TEST_HASH_KEY') : $key = env('NEWEBPAY_HASH_KEY');
        env('APP_ENV') != 'production' ? $iv = env('NEWEBPAY_TEST_HASH_IV') : $iv = env('NEWEBPAY_HASH_IV');
        return $this->newebStripPadding(openssl_decrypt(
            hex2bin($parameter),
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING,
            $iv
        ));
    }

    //官方說明文件提供 加密副程式
    private function newebStripPadding($string)
    {
        $slast = ord(substr($string, -1));
        $slastc = chr($slast);
        $pcheck = substr($string, -$slast);
        if (preg_match("/$slastc{" . $slast . "}/", $string)) {
            $string = substr($string, 0, strlen($string) - $slast);
            return $string;
        } else {
            return false;
        }
    }
    private function newebPayCode($s)
    {
        switch($s){
            case 'MPG01001': return '會員參數 不可空白/設定錯誤'; break;
            case 'MPG01002': return '時間戳記不可空白'; break;
            case 'MPG01005': return 'TokenTerm 不可空白/設定錯誤'; break;
            case 'MPG01008': return '分期參數設定錯誤'; break;
            case 'MPG01009': return '商店代號不可空白'; break;
            case 'MPG01010': return '程式版本設定錯誤'; break;
            case 'MPG01011': return '回傳規格設定錯誤'; break;
            case 'MPG01012': return '商店訂單編號不可空白/設定錯誤'; break;
            case 'MPG01013': return '付款人電子信箱設定錯誤'; break;
            case 'MPG01014': return '網址設定錯誤'; break;
            case 'MPG01015': return '訂單金額不可空白/設定錯誤'; break;
            case 'MPG01016': return '檢查碼不可空白'; break;
            case 'MPG01017': return '商品資訊不可空白'; break;
            case 'MPG01018': return '繳費有效期限設定錯誤'; break;
            case 'MPG02001': return '檢查碼錯誤'; break;
            case 'MPG02002': return '查無商店開啟任何金流服務'; break;
            case 'MPG02003': return '支付方式未啟用，請洽客服中心'; break;
            case 'MPG02004': return '送出後檢查，超過交易限制秒數'; break;
            case 'MPG02005': return '送出後檢查，驗證資料錯誤'; break;
            case 'MPG02006': return '系統發生異常，請洽客服中心'; break;
            case 'MPG03001': return 'FormPost 加密失敗'; break;
            case 'MPG03002': return '拒絕交易 IP'; break;
            case 'MPG03003': return 'IP 交易次數限制 N 分鐘內不可交易達 M 次'; break;
            case 'MPG03004': return '商店狀態已被暫停或是關閉，無法進行交易'; break;
            case 'MPG03007': return '查無此商店代號'; break;
            case 'MPG03008': return '已存在相同的商店訂單編號'; break;
            case 'MPG03009': return '交易失敗'; break;
            default : return '未知的錯誤(如您使用銀聯卡，可於五到十分鐘後至歷史訂單確認是否支付成功)'; break;
        }
    }
}
