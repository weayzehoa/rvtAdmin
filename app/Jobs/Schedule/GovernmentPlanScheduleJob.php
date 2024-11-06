<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TmpMachineList as TmpMachineListDB;
use App\Models\TmpMposRecord as TmpMposRecordDB;
use DB;


class GovernmentPlanScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * 政府計畫排程
     * @return void
     */
    public function handle()
    {
        //商店ID群
        $storeIds = [32,31,28,4,5,27,30,6,25,26,132,320,255,354,327,221,110,104,44,334,202,166,127,36169,130,200,231,311,252,213,234,72,264,147,123,154,50,101,37,78,81,351,60,315,306,323,164,96,15210,119,117,102,41,308,139,302,356,76,286,357,173,191,326,314,261,75,355,220,142,70,232,204,21188,256,236,329,65,362,217,162,212,206,71,158,258,310,144,346,284,95,209,167,58,282,368,89,28247,193,157,342,131,194,116,126,98,156,203,364,105,365,91,177,39,303,280,125,347,276,136,201,33318,86,88,51,238,124,352,307,246,92,341,128,313,185,83,189,259,168,283,245,207,358,163,348,309,679,224,179,134,273,367,290,187,80,340,97,250,172,108,143,198,361,225,305,349,118,262,279,271,5223,287,371,266,82,214,257,332,47,293,137,345,253,113,339,148,182,316,180,74,40,235,146,216,6197,328,218,145,325,52,165,171,100,335,152,244,292,120,73,227,55,288,254,150,90,299,263,249,9161,324,114,69,155,353,61,295,278,190,343,111,141,87,184,133,199,57,112,298,84,370,38,195,135,3536,222,106,233,285,67,237,350,239,49,196,229,174,301,248,243,63,319,176,122,330,43,183,129,56,34109,85,149,160,369,54,275,175,181,159,151,42,269,62,317,272,267,270,336,230,363,211,99,240,31205,294,170,331,260,140,53,46,228,186,296,242,291,219,115,277,251,208,77,297,322,192,48,321,28107,304,94,103,338,34,300,138,178,241,121,360,265,35,64,268,45,226,274];
        //付款方式代號
        $payMethods = ['2003','2015','2010','4003','4007','4010'];
        $startDay = mktime(10, 0, 0, date("m"), date("d"), date("Y"));
        $imax=rand(24,36);
        $records = TmpMposRecordDB::whereRaw("DATE_FORMAT(created_at,'%Y-%m-%d')='".date("Y-m-d",$startDay)."'")->get();
        //如果今天沒紀錄, 生成紀錄
        if(count($records) <= 0){
            for ($i= 1; $i<=$imax;$i++) {
                //產生訂單資料
                $storeId = $storeIds[array_rand($storeIds,1)];
                $timestamp = $startDay + rand(0,43200);
                $orderTime = date("Y-m-d H:i:s",$timestamp);
                $orderNumber = date("ymdHis",$timestamp) . rand(0,9);
                $amount = rand(800,1500);
                $payMethod = $payMethods[array_rand($payMethods,1)];
                $data = [
                    'tmp_machine_list_id' => $storeId,
                    'order_number' => $orderNumber,
                    'order_time' => $orderTime,
                    'shipping_method' => 3,
                    'shipping_time' => '',
                    'pay_method' => $payMethod,
                    'pay_time' => $orderTime,
                    'skey' => '',
                    'amount' => $amount,
                    'boxes' => 1,
                    'nation' => '+886',
                    'mobile' => 0,
                    'birthday' => '',
                    'response' => '',
                    'status' => 1,
                    'refund_amount' => 0,
                    'shipping_number' => null,
                    'device_order_number' => null,
                    'is_close' => 0,
                    'close_response' => '',
                    'close_time' => null,
                    'free_shipping' => 0,
                    'base_shipping_fee' => 0,
                    'each_box_shipping_fee' => 0,
                    'payment_percent' => '0.00',
                    'refund_response' => '',
                    'cancel_response' => '',
                    'is_print' => null,
                    'book_shipping_date' => null,
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ];
                TmpMposRecordDB::insert($data);
            }
        }
        //取得授權 u1jX7IluO9c6bwdMcQ8dTk9SSWU4whwPVphxjrDq4A37NRuCp5otee7y3uc6bKjF (隨機變化)
        $auth = $this->auth();
        //第一段資料
        $datalist = $this->tradCount1();
        $tmp=[];
        foreach($datalist->toArray() as $k => $v){
            $tmp[] = $v;
            if(($k+1) % 500 == 0){
                $this->send1($auth,json_encode($tmp,JSON_UNESCAPED_UNICODE));
                $tmp=[];
            }
        }
        if(!empty($tmp)){
            $this->send1($auth,json_encode($tmp,JSON_UNESCAPED_UNICODE));
        }
        //第二段資料
        $datalist = $this->tradCount2();
        $tmp = [];
        foreach($datalist->toArray() as $k => $v){
            $tmp[] = $v;
            if(($k+1) % 500 == 0){
                $this->send2($auth,json_encode($tmp,JSON_UNESCAPED_UNICODE));
                $tmp= [];
            }
        }
        if (!empty($tmp)) {
            $this->send2($auth, json_encode($tmp, JSON_UNESCAPED_UNICODE));
        }
    }

    private function auth()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.payapi.org.tw/api/users/authorize",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\"email\":\"icarry@icarry.me\", \"secret\":\"VspvrlQ4uZa4/07MrgFRo68d3hOsn1eLgRw+fr0AOsY=\"}",
          CURLOPT_HTTPHEADER => array(
            "Accept: */*",
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Host: api.payapi.org.tw",
            "cache-control: no-cache"
          ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        $data=json_decode($response,true);
        return $data["id"];
    }

    private function tradCount1()
    {
        $startTime = date("Y-m-d",time()-259200).' 00:00:00';
        $endTime = date("Y-m-d").' 23:59:59';
        $records = TmpMposRecordDB::join('tmp_machine_lists','tmp_machine_lists.id','tmp_mpos_records.tmp_machine_list_id')
        ->where('tmp_mpos_records.status','>',0)->whereBetween('pay_time',[$startTime,$endTime])
        ->select([
            DB::raw("'1M1070066' as ProjectID"),
            DB::raw("'46452701' as TaxID"),
            DB::raw("SUM(1) as tradeCount"),
            DB::raw("SUM(amount) as tradeAmount"),
            DB::raw("IF(pay_method = '0' OR pay_method = '信用卡' OR pay_method = '支付寶' OR pay_method = '銀聯卡' OR pay_method = '電子錢包' OR pay_method = '金融卡','1001',pay_method) as PayDevice"),
            DB::raw("'' as PayDeviceNote"),
            DB::raw("STR_TO_DATE(REPLACE(pay_time,'/','-'),'%Y-%m-%d') as TradeDate"),
            'tmp_machine_lists.city as City',
            'tmp_machine_lists.zip_code as PostCode',
        ])->groupBy('TradeDate','PostCode','PayDevice')
        ->orderBy('TradeDate','asc')
        ->orderBy('PostCode','asc')
        ->orderBy('PayDevice','asc')
        ->get();
        foreach ($records as $record) {
            $record->DataID = str_replace('-','',$record->TradeDate).$record->PostCode.$record->PayDevice;
        }
        return $records;
    }

    private function tradCount2()
    {
        $records = TmpMposRecordDB::join('tmp_machine_lists','tmp_machine_lists.id','tmp_mpos_records.tmp_machine_list_id')
            ->select([
                'tmp_mpos_records.tmp_machine_list_id as StoreId',
                DB::raw("DATE_FORMAT(tmp_machine_lists.created_at,'%Y-%m-%d') AS PaymentOnlineDate"),
                DB::raw("CONCAT(REPLACE(LEFT(tmp_machine_lists.created_at,10),'-',''),LPAD(tmp_mpos_records.tmp_machine_list_id, 3, 0),IF(tmp_mpos_records.pay_method = '0' OR tmp_mpos_records.pay_method = '信用卡'  OR tmp_mpos_records.pay_method = '支付寶' OR tmp_mpos_records.pay_method = '銀聯卡' OR tmp_mpos_records.pay_method = '電子錢包' OR tmp_mpos_records.pay_method = '金融卡','1001',tmp_mpos_records.pay_method)) as DataID"),
                DB::raw("'1M1070066' as ProjectID"),
                DB::raw("'46452701' as TaxID"),
                'BrandName' => DB::connection('icarryTmp')->table('vendor')->whereColumn('tmp.vendor.id','icarrydev.tmp_machine_lists.vendor_id')->select('name')->limit(1),
                'tmp_machine_lists.name as StoreName',
                'tmp_machine_lists.city as City',
                'tmp_machine_lists.zip_code as PostCode',
                'tmp_machine_lists.address as Address',
            ])->groupBy('tmp_mpos_records.pay_method','StoreId')->orderBy('tmp_mpos_records.tmp_machine_list_id','asc')->distinct()->get();
        foreach ($records as $record) {
            $record->StoreId = "C".str_pad($record->StoreId,5,'0',STR_PAD_LEFT);
        }
        return $records;
    }

    private function send1($auth1,$data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.payapi.org.tw/api/DataStorageMappings/293d4810-fa15-11e9-9a26-e994a6110b64/importData",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Authorization: {$auth1}",
            "Accept: */*",
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Host: api.payapi.org.tw",
            "cache-control: no-cache"
          ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
          return $err;
        } else {
          return $response;
        }
    }

    private function send2($auth1,$data){
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.payapi.org.tw/api/DataStorageMappings/87bd4860-f9fa-11e9-89bc-f5fa1286d01d/importData",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => $data,
          CURLOPT_HTTPHEADER => array(
            "Authorization: {$auth1}",
            "Accept: */*",
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Host: api.payapi.org.tw",
            "cache-control: no-cache"
          ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
          return $err;
        } else {
          return $response;
        }
    }
}
