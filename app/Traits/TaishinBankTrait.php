<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\TashinCreditcard as TashinCreditcardDB;
use Curl;

trait TaishinBankTrait
{
    public function __construct()
    {
    }

    public function taishinPay($method, $orderNumber, $amount)
    {
        $this->payMethod = ['台新銀聯卡' => 'union'];
        $this->method = $method;
        $this->orderNumber = $orderNumber;
        $this->amount = intval($amount);
        $this->payStatus = 0;
        $this->paymentType = $this->payMethod[$this->method];

        if ($this->paymentType == 'union') {
            return $this->taishinUnionPay();
        }
        return null;
    }

    public function taishinUnionPay()
    {
        env('APP_ENV') == 'production' ? $api = env('TASHIN_UNION_URL') : $api = env('TASHIN_UNION_TEST_URL');
        env('APP_ENV') == 'production' ? $MerchantID = env('TASHIN_UNION_MerchantID') : $MerchantID = env('TASHIN_UNION_TEST_MerchantID');
        $TerminalID = env('TASHIN_UNION_TerminalID');
        $apiAuth = $api.'cupauth.ashx';
        $apiCancel = $api.'othetr.ashx';
        $amount = $this->amount;
        $post = [
            'sender'=>'rest',
            'ver'=>'1.2.0',
            'mid'=>'{$MerchantID}',
            's_mid'=>'',
            'tid'=>'{$TerminalID}',
            'pay_type'=>2,
            'tx_type'=>1,
            'params'=>[
               'order_no'=>$this->orderNumber,
               'amt'=> "".($this->amount * 100)."",
               'cur'=>'NTD',
               'order_desc'=>'iCarry',
               'layout'=>'1',
               'capt_flag'=>'0',
               'result_flag'=>'1',
               'post_back_url'=> env('TASHIN_UNION_POST_BACK_URL'),
               'result_url'=> env('TASHIN_UNION_RESULT_BACK_URL'),
            ],
        ];
        $postJson=json_encode($post,JSON_UNESCAPED_UNICODE);
        $tsc = TashinCreditcardDB::create([
            'type' => '銀聯卡',
            'order_number' => $this->orderNumber,
            'amount' => $this->amount,
            'post_json' => $postJson,
        ]);
        $result = $this->taishinUnionPayPost($apiAuth,$postJson);
        $ret=json_decode($result,true);
        if($ret["params"]["ret_code"]=="00"){//成功
            $return['data'] = $ret["params"]["hpp_url"];
        }else{//失敗$ret["params"]["ret_msg"]
            $return['message'] = '付款失敗';
            $tsc = $tsc->update([
                'get_json' => $result,
                'pay_status' => 0,
            ]);
        }
        $return['type'] = 'url';
        return $return;
    }

    public function taishinUnionPayPost()
    {
        $ch=curl_init();
        if(strstr($PostURL,'https://')){//SSL POST
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Content-Type:text/html'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array('charset:utf-8'));
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Accept:text/html'));
        curl_setopt($ch,CURLOPT_URL, $PostURL); // 設定所要傳送網址
        curl_setopt($ch,CURLOPT_HEADER, false); // 不顯示網頁
        curl_setopt($ch,CURLOPT_POST,1); // 開啟回傳
        curl_setopt($ch,CURLOPT_POSTFIELDS,$json); // 將post資料塞入
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
        $GetPost=curl_exec($ch); // 執行網頁
        curl_close($ch); // 關閉網頁
        return $GetPost;
    }
}
