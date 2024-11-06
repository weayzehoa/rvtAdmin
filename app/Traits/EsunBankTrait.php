<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Alipay as AlipayDB;
use Curl;

trait EsunBankTrait
{
    public function __construct()
    {
    }

    public function esunPay($method, $orderNumber, $amount)
    {
        $this->payMethod = ['玉山信用卡' => 'creditCard', '玉山行動銀行' => 'moveBank', '玉山支付寶' => 'alipay'];
        $this->method = $method;
        $this->orderNumber = $orderNumber;
        $this->amount = $amount;
        $this->payStatus = 0;
        $this->paymentType = $this->payMethod[$this->method];

        if ($this->paymentType == 'alipay') {
            return $this->esunAlipay();
        } elseif ($this->paymentType == 'creditCard') { //關
            return '玉山信用卡表單資料';
        } elseif ($this->paymentType == 'moveBank') {
            return '玉山行動銀行表單資料';
        }
    }

    public function esunAlipay()
    {
        $TransactionData = [
            'StoreID' => env('ESUN_ALIPAY_STORE_ID'),
            'TermID' => env('ESUN_ALIPAY_TERM_ID'),
            'Type' => 3,
            'DefaultWallet' => 'alipay',
            // 'OrderNo' => $this->orderNumber,
            'OrderNo' => 'TEST'.time(),
            'OrderCurrency' => 'TWD',
            'OrderAmount' => $this->amount,
            'ExpireDT' => date('Y-m-d 23:59:59'),
            'StoreMemo' => 'iCarry'
        ];
        $para = 'tradeapicreatebankqrcode'.urlencode(json_encode($TransactionData));
        $post = [
            'Type'=>'tradeapi',
            'Action'=>'createbankqrcode',
            'HashDigest'=> hash('sha256', $para.'df59771e864b0953aaccbb8f9bf3a477c80ea9a1fa98ed9e44441f38942bf3e8'),
            'TransactionData'=>urlencode(json_encode($TransactionData))
        ];
        $result = $this->esunAlipayPost(urlencode(json_encode($post)));
        $result = json_decode(urldecode($result),true);
        $data = json_decode(urldecode($result['TransactionData']),true);
        $postJson = json_encode($data);
        $alipay = AlipayDB::create([
            'order_number' => $this->orderNumber,
            'amount' => $this->amount,
            'post_json' => $postJson,
            'currency' => 'TWD',
            'gateway' => '玉山',
        ]);
        //輸出網址
        // "data": {
        //     "Qrcode": "https://qr.esuntrade.com/BOKsvN19HRxaz5B",
        //     "SupportedWallets": "alipay;EsunMobile;taiwanpay;",
        //     "TWPQRcode": "TWQRP%3a%2f%2f%e7%9b%b4%e6%b5%81%e9%9b%bb%e9%80%9a%e8%82%a1%e4%bb%bd%e6%9c%89%e9%99%90%e5%85%ac%e5%8f%b8%2f158%2f01%2fV1%3fD1%3d100000%26D2%3dBOKsvN19HRxaz5B%26D3%3dAWGV7bXAnEF1%26D11%3d00%2c80880846452701AE00TP000001%3b01%2c80880846452701AE00TP000001",
        //     "Status": "S",
        //     "ErrorCode": 0,
        //     "ErrorDesc": null
        //   }
        return $data['Qrcode'];
    }

    public function esunAlipayPost($str){
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://mpayment.esuntrade.com/mPay/GatewayV2/API/V2/xTrade.ashx',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => ($str),//'Type=tradeapi&Action=createbankqrcode',
          CURLOPT_HTTPHEADER => array(
            'cache-control: no-cache',
            'content-type: application/x-www-form-urlencoded',
            'charset: utf-8'
          ),
        ));
        $response = curl_exec($curl);
        return $response;
    }
}
