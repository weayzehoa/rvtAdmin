<?php

namespace App\Jobs\Schedule;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use DB;

class CreateJamieLeeScheduleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $vendors = [
        ['29042296','李亭香食品有限公司','vKuOiIxoFGX2SE5S','10,000','2,000','5,000','2,000','400','100','500'],
        ['29067606','京盛宇現代食茶股份有限公司','kyPbKhCtLckr6Io0','5,000','1,000','2,500','1,000','200','50','250'],
        // ['42341640','橙荳工坊','LEFBzIy0WpyqeDkJ','15,000','10,500','3,000','750','150','150','450'],
        ['42485104','美傘町商號','kTVvZV8Pl4uGk35D','30,000','21,000','6,000','1,500','300','300','900'],
        ['52890497','連沂有限公司','HlRh8nRKqJhAOcFQ','8,000','5,600','1,600','400','0','0','400'],
        ['22870754','喜之坊食品有限公司','SLYwSptKr2eMx7RD','80,000','56,000','16,000','4,000','800','800','2,400'],
        ['25554267','永康芋頭大王冰店','O87msGJJWQcKfbo5','30,000','21,000','6,000','1,500','300','300','900'],
        ['21730881','果實咖啡堂','RsB0cDDfX8ai3gvG','50,000','10,000','25,000','10,000','2,000','500','2,500'],
        ['21730881','果實咖啡堂','RsB0cDDfX8ai3gvG','50,000','10,000','25,000','10,000','2,000','500','2,500'],
        ['42501575','安達窯','yEAPkIzkz0bI1qdH','30,000','6,000','15,000','6,000','1,200','300','1,500'],
        ['50905500','台灣顧氏刺繡有限公司','u1PfF7zv6hXHaemY','20,000','14,000','4,000','1,000','200','200','600'],
        ['42617273','火星基地股份有限公司','HeJ2jbvsfxvDFMxP','20,000','14,000','4,000','1,000','200','200','600'],
        ['26222338','元氣吐司早餐','w62ueClYq7uPLBD6','150,000','105,000','30,000','7,500','1,500','1,500','4,500'],
        // ['42503996','溱溱果汁店','ZdIL4PDLR7xeyPe3','50,000','35,000','10,000','2,500','500','500','1,500'],
        ['31865985','翡翠小舖','3KXEy0wJrdqmXwcR','30,000','21,000','6,000','1,500','300','300','900'],
        ['25655639','爵林商行','emTEM7j5LQ7AdPX4','30,000','21,000','6,000','1,500','300','300','900'],
        ['42738207','爵林堅果坊','WzXlTOiVWMM2HtxQ','20,000','14,000','4,000','1,000','200','200','600'],
        ['78890649','韓濱商行','t8P7aVtkanJAO3vU','10,000','2,000','5,000','2,000','400','100','500'],
        ['75709630','珺岑商行','ZTYv3e2QWnqSefdJ','40,000','8,000','20,000','8,000','1,600','400','2,000'],
        ['50699537','淡水一口酥','i0tlKJKXU46aylO1','1,500','300','750','300','75','0','75'],
        ['85045751','草原風蒙古火鍋','YDbZQPdotctez3mi','30,000','6,000','15,000','6,000','1,200','300','1,500'],
        ['98798873','醍醐大師','8ZNqWoMiVJaCJLAa','30,000','21,000','6,000','1,500','300','300','900'],
        //['42499085','一抹甜商行','M74LXBbGMzEiZ1J2','30,000','6,000','15,000','6,000','1,200','300','1,500'],
        ['02218166','雲峰茶莊','sj4dYCttyrNbUHCM','30,000','21,000','6,000','1,500','300','300','900'],
        ['97455293','聖比德蛋糕有限公司','kdkrn37sxEaJsQU8','30,000','21,000','6,000','1,500','300','300','900'],
        ['26222148','永峰茗茶行','dEWgNCkmE3XaTc7R','30,000','21,000','6,000','1,500','300','300','900'],
        ['42715984','好好食茶有限公司','NvV20RkYlREKrPjL','5,000','1,000','2,500','1,000','200','50','250'],
        //['82920342','宮尛茶鋪','VO3av18Pz3ElJwFy','20,000','14,000','4,000','1,000','200','200','600'],//2020.04.21
        ['42487898','康祥咖啡館','2BQcCciLeYMP7k4E','30,000','6,000','15,000','6,000','1,200','300','1,500'],
        ['24492639','莊子茶葉有限公司','H3ip3AHr6COpjw1E','30,000','21,000','6,000','1,500','300','300','900'],
        ['85104287','餃子樂','sZ4g6YgRWVhVmYvE','30,000','6,000','15,000','6,000','1,200','300','1,500'],
        ['80286718','法恩','LD7IKJ8PiiIwlq5O','20,000','14,000','4,000','1,000','200','200','600'],
        ['25570858','御尚璽','FrRiwyx70hyrFN4G','50,000','35,000','10,000','2,500','500','500','1,500'],
        ['99612657','壹品山西刀削麵小吃店','PieOymvnyp5hLpbd','30,000','21,000','6,000','1,500','300','300','900'],
        //['15972220','布田食品','CHZ3xPn6NUWRYe4W','50,000','35,000','10,000','2,500','500','500','1,500'],2020.04.15
        ['26256060','羊毛與花 Coffee','0gz7847LKjI7l0Nd','200,000','140,000','40,000','10,000','2,000','2,000','6,000'],
        //['24963808','Escapeholics 密室脫逃','VPGVONBeZRbQtKW7','100,000','70,000','20,000','5,000','1,000','1,000','3,000'],
        ['20479439','小珍珠烘焙坊','4lYNALlGP052VMWx','30,000','21,000','6,000','1,500','300','300','900'],
        ['26825957','妮娜夢想城堡','7LALJatWDYwVUe1G','80,000','56,000','16,000','4,000','800','800','2,400'],
        ['28720212','清淨母語','oZqTzr74VephnmmG','150,000','105,000','30,000','7,500','1,500','1,500','4,500'],
        ['27834981','清淨母語','qthFms7lPUwAnUNs','300,000','210,000','60,000','15,000','3,000','3,000','9,000'],
        ['27751102','飛比樂有限公司','yJ6toAarHe17bBX5','20,000','14,000','4,000','1,000','200','200','600'],
        ['24546190','幸福可可','VoOleMlczreLrTTr','20,000','14,000','4,000','1,000','200','200','600'],
        ['97469304','芒果皇帝','xzvc1RpKVnD8ZiMm','150,000','105,000','30,000','7,500','1,500','1,500','4,500'],
        ['14328619','歐嬤烏蘇拉經典德式料理','IBN2y6Wn1Ro5vjSX','100,000','70,000','20,000','5,000','1,000','1,000','3,000'],
        //['24776559','伴百度皮鞋店','on4oifWm7WPIYIeu','30,000','21,000','6,000','1,500','300','300','900'],
        ['42843545','歐嬤德式美食','1DLLJ41HUYcWuo32','50,000','35,000','10,000','2,500','500','500','1,500'],
        ['31949140','La Regina','MGw5f6NEom5CP6sd','30,000','21,000','6,000','1,500','300','300','900'],
        ['10387452','蒔尚藝坊','H2MexN05tUfRqVVL','80,000','56,000','16,000','4,000','800','800','2,400'],
        //['12778167','漫遊台灣','z2cjGcahrSfG2vXX','300,000','210,000','60,000','15,000','3,000','3,000','9,000'],
        ['42435750','羊毛與花 ‧ 溫州','jnPE6rUl8YJv66w4','50,000','35,000','10,000','2,500','500','500','1,500'],
        ['76411812','羊毛與花 ‧ 金華','t28dtOZN34ROWaHJ','20,000','14,000','4,000','1,000','200','200','600'],
        ['24939836','忠孝玉香齋有限公司','Vl6XezGCQgAfZZrc','50,000','35,000','10,000','2,500','500','500','1,500'],
        ['26256673','e-2000','WyqxmHFKxp8SWPWU','30,000','21,000','6,000','1,500','300','300','900'],
        ['42667982','逸杯茶','RusgkUnOhsZupFA1','30,000','21,000','6,000','1,500','300','300','900'],
        ['98912591','臺灣一品刀削麵','c05Ov1B3t3Z1TrIP','30,000','21,000','6,000','1,500','300','300','900'],
        ['24491483','查理布朗烘培','qmGzfg7Jns5ogdOJ','30,000','21,000','6,000','1,500','300','300','900'],
        ['24229429','敉你村舒能鞋','d7ALq4oA54u3fuAl','30,000','21,000','6,000','1,500','300','300','900'],
        ['39812821','雪莉貝爾彩繪冰品專賣店','mYs3mYksQs8jaZl8','30,000','21,000','6,000','1,500','300','300','900'],
        ['85596163','敉你村舒能鞋','jIibrSejAOgnjg47','30,000','21,000','6,000','1,500','300','300','900'],
        ['50763930','樂陽健康足體','3KBYniC1LDxNUcbq','30,000','21,000','6,000','1,500','300','300','900'],
        //['53950177','老山野生茶業有限公司','hYWAjxNepQkvX0Kc','30,000','21,000','6,000','1,500','300','300','900'],
        ['83746906','好眠趣','0Gck38paxyHnrvm1','30,000','21,000','6,000','1,500','300','300','900'],
        ['48694825','琉璃光服飾店','AGZekG1sWbkTtRS2','30,000','21,000','6,000','1,500','300','300','900'],
        ['48754533','孔美子精品店','Y7XqVfq8J0PozNUO','30,000','21,000','6,000','1,500','300','300','900'],
        //['53950177','老山野生茶業有限公司','hYWAjxNepQkvX0Kc','30,000','21,000','6,000','1,500','300','300','900'],
    ];

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
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(0); //資料庫龐大所以設定無限制時間
        $weekOfToday = date('N',time());
        $weekOfToday = 5;
        //每周要上傳的話，以一年52周來算比較準
        if($weekOfToday == 5){ //週五晚上10點運作
            $sixDaysBefore = date('Y-m-d',time()-(6*86400));//六
            $fiveDaysBefore = date('Y-m-d',time()-(5*86400));//日
            $fourDaysBefore = date('Y-m-d',time()-(4*86400));//一
            $threeDaysBefore = date('Y-m-d',time()-(3*86400));//二
            $twoDaysBefore = date('Y-m-d',time()-(2*86400));//三
            $yesterday = date('Y-m-d',time()-(1*86400));//四
            $today = date('Y-m-d',time());//五
            $days =[$sixDaysBefore,$fiveDaysBefore,$fourDaysBefore,$threeDaysBefore,$twoDaysBefore,$yesterday,$today];
            $whatDay = array($sixDaysBefore,$fiveDaysBefore,$fourDaysBefore,$threeDaysBefore,$twoDaysBefore,$yesterday,$today);
            $result = DB::connection('icarryTmp')->table('jamie_lee_part_2')
                ->where('vendor_number','!=',82920342)
                ->whereRaw("LEFT(create_time,10) IN('{$sixDaysBefore}','{$fiveDaysBefore}','{$fourDaysBefore}','{$threeDaysBefore}','{$twoDaysBefore}','{$yesterday}','{$today}')")
                ->get();
            //沒資料時生成資料
            if(count($result) <= 0){
                foreach($this->vendors as $vendor){
                    $totalMoney = 0;
                    $vendorName = $vendor[1];
                    $vendorNumber = $vendor[0];
                    $apiKey = $vendor[2];
                    $totalMonthlyMoney =str_replace(',','',$vendor[3]);
                    $totalCCMoney = str_replace(',','',$vendor[4]);
                    $totalAppleMoney = str_replace(',','',$vendor[5]);
                    $totalSamsungMoney = str_replace(',','',$vendor[6]);
                    $totalAlipayMoney = str_replace(',','',$vendor[7]);
                    $totalWechatMoney = str_replace(',','',$vendor[8]);
                    $totalTwpayMoney = str_replace(',','',$vendor[9]);
                    $maxValue = $totalMonthlyMoney / $month_days; //每個店家每天總額
                    $ccPercent = $ccPercent2 = round(($totalCCMoney / $totalMonthlyMoney) * 100);
                    $ccPercent1 = round($ccPercent / 2);
                    $applePercent=round(($totalAppleMoney / $totalMonthlyMoney) * 100) + $ccPercent2;
                    $samsungPercent=round(($totalSamsungMoney / $totalMonthlyMoney) * 100) + $applePercent;
                    $alipayPercent=round(($totalAlipayMoney / $totalMonthlyMoney) * 100) + $samsungPercent;
                    $wechatPercent=round(($totalWechatMoney / $totalMonthlyMoney) * 100) + $alipayPercent;
                    $twpayPercent = 100;
                    $invoiceNumber = '';
                    $fiveOrTen = [5,0];
                    $maxValue = $totalMonthlyMoney; //每個店家每天總額
                    while ( $totalMoney < $maxValue ) {
                        $whatDayIs = $whatDay[rand(0,6)];
                        $dayStartTime = $this->dayStartTime($whatDayIs);
                        $timestamp = $dayStartTime + rand(0,36000);
                        $createTime = date('Y-m-d H:i:s',$timestamp);
                        $payMoney = intval(rand(4,30).''.$fiveOrTen[(rand(1,10)%2)]);
                        $payType = 21;
                        $a100Rand = rand(1,100);
                        if($a100Rand <= $ccPercent1){
                            $payType = 21; //VISA 35% 編號 21
                        }elseif($a100Rand > $ccPercent1 && $a100Rand <= $ccPercent2){
                            $payType=22; //mastercard 35% 編號 22
                        }elseif($a100Rand > $ccPercent2 && $a100Rand <= $applePercent){
                            $payType=34; //apple pay 20% 編號 34
                        }elseif($a100Rand > $applePercent && $a100Rand <= $samsungPercent){
                            $payType=35; //samsung pay 20% 編號 35
                        }elseif($a100Rand > $samsung_percent && $a100Rand <= $alipayPercent){
                            $payType = 37; //alipay 5% 編號 37
                        }elseif($a100Rand > $alipayPercent && $a100Rand <= $wechatPercent){
                            $payType = 38; //wechat pay 4% 編號 38
                        }elseif($a100Rand > $wechatPercent && $a100Rand <= $twpayPercent){
                            $payType = 31; //twpay 1 % 編號 31
                        }
                        DB::connection('icarryTmp')->table('jamie_lee_part_2')
                            ->insert([
                                'create_time' => $createTime,
                                'pay_type' => $payType,
                                'pay_money' => $createTime,
                                'invoice_number' => $invoiceNumber,
                                'vendor_name' => $vendorName,
                                'vendor_number' => $vendorNumber,
                                'api_key' => $apiKey,
                            ]);
                        $totalMoney += $payMoney;
                    }
                }
                //更新排序
                foreach($this->vendors as $vendor){
                    $vendorName = $vendor[1];
                    $vendorNumber = $vendor[0];
                    $apiKey = $vendor[2];
                    $token = $this->bizlionOauth($vendorNumber,$apiKey);
                    $dataArray = [];
                    //宣告mysql裡面的變數 @i := 0
                    DB::connection('icarryTmp')->statement("SET @i := 0");
                    $results = DB::connection('icarryTmp')->table('jamie_lee_part_2')
                        ->where('vendor_number',$vendorNumber)
                        ->select([
                            'id',
                            DB::raw("LPAD((@i := @i+1), 5, 0) as new_sort_id"),
                            'sort_id',
                        ])->orderBy('vendor_number','asc')
                        ->orderBy('create_time','asc')
                        ->get();
                    foreach($results as $result){
                        if(empty($result->sort_id)){
                            if(substr($result->new_sort_id,0,1) == '0'){
                                $result->new_sort_id = substr($result->new_sort_id,1);
                            }
                            $result->update(['sort_id' => $result->new_sort_id]);
                        }
                    }
                    if($vendorNumber == '29067606'){//京盛宇現代食茶股份有限公司
                        $result = DB::connection('icarryTmp')->table('jamie_lee_part_2')->where('vendor_number',$vendorNumber)->orderBy('vendor_number','desc')->limit(1)->get();
                        $result = $result->update(['sort_id' => '0991']);
                    }
                    if($vendorNumber == '52890497'){//連沂有限公司
                        $result = DB::connection('icarryTmp')->table('jamie_lee_part_2')->where('vendor_number',$vendorNumber)->orderBy('vendor_number','desc')->limit(1)->get();
                        $result = $result->update(['sort_id' => '0997']);
                    }
                }
                //送資料
                foreach($this->vendor as $vendor){
                    $vendorName = $vendor[1];
                    $vendorNumber = $vendor[0];
                    $apiKey = $vendor[2];
                    $token = $this->bizlionOauth($vendorNumber,$apiKey);
                    $data = DB::connection('icarryTmp')->table('jamie_lee_part_2')
                        ->whereRaw("vendor_number='{$vendorNumber}' AND LEFT(create_time,10) IN('{$sixDaysBefore}','{$fiveDaysBefore}','{$fourDaysBefore}','{$threeDaysBefore}','{$twoDaysBefore}','{$yesterday}','{$today}')")
                        ->select([
                            'sort_id as 交易序號',
                            'create_time as 交易時間',
                            'pay_type as 支付方式',
                            'pay_money as 交易金額',
                            'invoice_number as 發票號碼',
                        ])
                        ->orderBy('vendor_number','asc')
                        ->orderBy('create_time','asc')
                        ->get()->toArray();
                    $status = $this->bizlionUpload($token,$apiKey,$data);
                }
            }
        }
    }

    private function bizlionOauthPost($PostURL,$str){
        $ch=curl_init();
        if(strstr($PostURL,'https://')){//SSL POST
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Content-Type:application/x-www-form-urlencoded'));
        curl_setopt($ch,CURLOPT_URL, $PostURL); // 設定所要傳送網址
        curl_setopt($ch,CURLOPT_HEADER, false); // 不顯示網頁
        curl_setopt($ch,CURLOPT_POST,1); // 開啟回傳
        curl_setopt($ch,CURLOPT_POSTFIELDS,$str); // 將post資料塞入
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
        $GetPost=curl_exec($ch); // 執行網頁
        curl_close($ch); // 關閉網頁
        return $GetPost;
    }

    private function bizlionOauth($vendorNumber,$apiKey){
        $api = 'https://sme.bizlion.com.tw/api/Token';
        $result = $this->bizlionOauthPost($api,'grant_type=password&username={$vendorNumber}&password={$apiKey}');
        $r = json_decode($result,true);
        return "{$r['token_type']} {$r['access_token']}";
    }

    private function bizlionUploadPost($PostURL,$str,$token){
        $ch=curl_init();
        if(strstr($PostURL,'https://')){//SSL POST
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array('Content-Type:application/json','Authorization:'.$token));
        curl_setopt($ch,CURLOPT_URL, $PostURL); // 設定所要傳送網址
        curl_setopt($ch,CURLOPT_HEADER, false); // 不顯示網頁
        curl_setopt($ch,CURLOPT_POST,1); // 開啟回傳
        curl_setopt($ch,CURLOPT_POSTFIELDS,$str); // 將post資料塞入
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1); // 開啟將網頁內容回傳值
        $GetPost=curl_exec($ch); // 執行網頁
        curl_close($ch); // 關閉網頁
        return $GetPost;
    }

    private function bizlionUpload($token,$apiKey,$data){
        $api_prifix = 'https://sme.bizlion.com.tw/api/';
        $is_test = 0;
        $is_test == 1 ? $api = '{$api_prifix}v1/Test/Upload/DATA1040' : $api = '{$api_prifix}v1/Upload/DATA1040';
        $json_data = ['金鑰'=>$apiKey,'上傳資料'=>$data];
        $str = json_encode($json_data,JSON_UNESCAPED_UNICODE);
        $result = $this->bizlionUploadPost($api,$str,$token);
        $r = json_decode($result,true);
        return $r['status'];
    }

    private function dayStartTime($date){
        $d = explode('-',$date);//Y-m-d
        $dd = intval($d[2]);
        $mm = intval($d[1]);
        $yyyy = intval($d[0]);
        return mktime(11, 0, 0, $mm, $dd, $yyyy);
    }
}
