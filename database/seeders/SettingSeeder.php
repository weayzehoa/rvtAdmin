<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryPayMethod as PayMethodDB;
use App\Models\iCarryReceiverBaseSet as ReceiverBaseSetDB;
use App\Models\iCarryReceiverBaseSetting as ReceiverBaseSettingDB;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarrySource as SourceDB;
use App\Models\iCarryAirportAddress as AirportAddressDB;
use DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //國家資料
        $countries = [
            ['name' => '台灣', 'name_en' => 'Taiwan', 'lang' => 'tw', 'code' => '+886', 'name_jp' => '台湾'],
            ['name' => '中國', 'name_en' => 'China', 'lang' => 'cn', 'code' => '+86', 'name_jp' => '中国'],
            ['name' => '香港', 'name_en' => 'Hong Kong', 'lang' => 'hk', 'code' => '+852', 'name_jp' => '香港'],
            ['name' => '澳門', 'name_en' => 'Macau', 'lang' => 'mo', 'code' => '+853', 'name_jp' => 'マカオ'],
            ['name' => '日本', 'name_en' => 'Japan', 'lang' => 'jp', 'code' => '+81', 'name_jp' => '日本'],
            ['name' => '韓國', 'name_en' => 'South Korea', 'lang' => 'kr', 'code' => '+82', 'name_jp' => '韓国'],
            ['name' => '新加坡', 'name_en' => 'Singapore', 'lang' => 'sg', 'code' => '+65', 'name_jp' => 'シンガポール'],
            ['name' => '馬來西亞', 'name_en' => 'Malaysia', 'lang' => 'my', 'code' => '+60', 'name_jp' => 'マレーシア'],
            ['name' => '越南', 'name_en' => 'Vietnam', 'lang' => 'vn', 'code' => '+84', 'name_jp' => 'ベトナム'],
            ['name' => '泰國-曼谷', 'name_en' => 'Thailand Bangkok', 'lang' => 'th', 'code' => '+66', 'name_jp' => 'タイ'],
            ['name' => '美國', 'name_en' => 'United States', 'lang' => 'us', 'code' => '+1', 'name_jp' => 'アメリカ'],
            ['name' => '加拿大', 'name_en' => 'Canada', 'lang' => 'ca', 'code' => '+1', 'name_jp' => 'カナダ'],
            ['name' => '英國', 'name_en' => 'United Kingdom', 'lang' => 'uk', 'code' => '+44', 'name_jp' => '英国'],
            ['name' => '法國', 'name_en' => 'France', 'lang' => 'fr', 'code' => '+33', 'name_jp' => 'フランス'],
            ['name' => '澳洲', 'name_en' => 'Australia', 'lang' => 'au', 'code' => '+61', 'name_jp' => 'オーストラリア'],
            ['name' => '紐西蘭', 'name_en' => 'New Zealand', 'lang' => 'nz', 'code' => '+64', 'name_jp' => 'ニュージーランド'],
            ['name' => '荷蘭', 'name_en' => 'Netherlands', 'lang' => 'nl', 'code' => '+31', 'name_jp' => 'オランダ'],
            ['name' => '比利時', 'name_en' => 'Belgium', 'lang' => 'be', 'code' => '+32', 'name_jp' => 'ベルギー'],
            ['name' => '西班牙', 'name_en' => 'Spain', 'lang' => 'es', 'code' => '+34', 'name_jp' => 'スペイン'],
            ['name' => '葡萄牙', 'name_en' => 'Portugal', 'lang' => 'pt', 'code' => '+351', 'name_jp' => 'ポルトガル'],
            ['name' => '愛爾蘭', 'name_en' => 'Ireland', 'lang' => 'ie', 'code' => '+353', 'name_jp' => 'アイルランド'],
            ['name' => '義大利', 'name_en' => 'Italy', 'lang' => 'it', 'code' => '+39', 'name_jp' => 'イタリア'],
            ['name' => '瑞士', 'name_en' => 'Switzerland', 'lang' => 'ch', 'code' => '+41', 'name_jp' => 'スイス'],
            ['name' => '捷克', 'name_en' => 'Czech Republi', 'lang' => 'cz', 'code' => '+420', 'name_jp' => 'チェコ共和国'],
            ['name' => '奧地利', 'name_en' => 'Austria', 'lang' => 'at', 'code' => '+43', 'name_jp' => 'オーストリア'],
            ['name' => '德國', 'name_en' => 'Germany', 'lang' => 'de', 'code' => '+49', 'name_jp' => 'ドイツ'],
            ['name' => '印尼', 'name_en' => 'Indonesia', 'lang' => 'id', 'code' => '+62', 'name_jp' => 'インドネシア'],
            ['name' => '菲律賓', 'name_en' => 'Philippines', 'lang' => 'ph', 'code' => '+63', 'name_jp' => 'フィリピン'],
            ['name' => '印度', 'name_en' => 'India', 'lang' => 'in', 'code' => '+91', 'name_jp' => 'インド'],
            ['name' => '其他', 'name_en' => 'Other', 'lang' => 'other', 'code' => 'o', 'name_jp' => 'その他'],
        ];
        if (env('DB_MIGRATE_ICARRY_COUNTRIES')) {
            $data = [];
            for ($i=0;$i<count($countries);$i++) {
                $data[] = [
                    'name' => $countries[$i]['name'],
                    'name_en' => $countries[$i]['name_en'],
                    'name_th' => $countries[$i]['name_en'],
                    'name_kr' => $countries[$i]['name_en'],
                    'name_jp' => $countries[$i]['name_jp'],
                    'lang' => $countries[$i]['lang'],
                    'code' => $countries[$i]['code'],
                    'sort' => $i+1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            $chunks = array_chunk($data, 5000);
            foreach($chunks as $chunk){
                CountryDB::insert($chunk);
            }
            echo "Country 建立完成\n";
        }

        if (env('DB_MIGRATE_ADD_TO_ICARRY_PAY_METHOD_TABLE')) {
            // Pay Method 遷移
            $data = [];
            $oldPayMethods = PayMethodDB::orderBy('id','asc')->get();
            $x = 1;
            foreach ($oldPayMethods as $oldPayMethod) {
                $typeArray = ['信用卡','支付寶','銀聯卡','ATM','CVS','行動銀行'];
                $type = '其它';
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
                $oldPayMethod->update([
                    'name' => $name,
                    'name_en' => $nameEn,
                    'value' => $value,
                    'type' => $type,
                    'is_on' => $isOn,
                    'sort' => $sort,
                ]);
                $x++;
            }
        }

        if (env('DB_MIGRATE_RECEIVER_BASE_SETTINGS')) {
            //從2022年開始到2032年
            $data = [];
            for ($y=2022;$y<=2032;$y++) {
                for ($m=1;$m<=12;$m++) {
                    $m<=9 ? $mm = '0'.$m : $mm = $m;
                    $d31 = [1,3,5,7,8,10,12]; //31天的月份
                    $d30 = [4,6,9,11]; //30天的月份
                    $d29 = [2020,2024,2028,2032]; //2月29天的年份
                    if(in_array($m,$d31)){
                        $end = 31;
                    }elseif(in_array($m,$d30)){
                        $end = 30;
                    }elseif(in_array($y,$d29) && $m == 2){
                        $end = 29;
                    }elseif($m == 2){
                        $end = 28;
                    }
                    for($d=1;$d<=$end;$d++){
                        $d<=9 ? $dd = '0'.$d : $dd = $d;
                        $date = "$y-$mm-$dd";
                        $callOK = 1;
                        $logisticsOK = 1;
                        $outOK = 1;
                        $pickupOK = 1;
                        $memoCall = null;
                        $memoLogistics = null;
                        $memoOut = null;
                        $memoPickup = null;
                        $week = date('w', strtotime($date));

                        if($week == 6){
                            $callOK = 0;
                            $logisticsOK = 0;
                            $outOK = 0;
                        }

                        if($week == 0){
                            $callOK = 0;
                            $logisticsOK = 0;
                            $outOK = 0;
                            $pickupOK = 0;
                            $memoPickup = '假日物流不派送';
                        }
                        $type = ['call','logistics','out','pickup'];
                        for($i=0;$i<count($type);$i++){
                            $data[] = ['select_date' => $date, 'week' => $week, 'type' => $type[$i], 'is_ok' => $callOK, 'memo' => $memoCall, 'admin_id' => 40];
                        }
                    }
                }
            }
            $chunks = array_chunk($data, 3000);
            foreach($chunks as $chunk){
                ReceiverBaseSettingDB::insert($chunk);
            }
            $oldReceiverSets = ReceiverBaseSetDB::where('select_time','>=','2022-01-01')->orderBy('select_time','asc')->get();
            foreach($oldReceiverSets as $oldReceiverSet){
                $newReceiverSettings = ReceiverBaseSettingDB::where('select_date',$oldReceiverSet->select_time)->get();
                foreach($newReceiverSettings as $newReceiverSetting){
                    if($newReceiverSetting->type == 'call'){
                        $newReceiverSetting->update([
                            'is_ok' => !empty($oldReceiverSet->is_call) ?? 0,
                            'memo' => $oldReceiverSet->call_memo,
                            'admin_id' => $oldReceiverSet->admin_id,
                        ]);
                    }
                    if($newReceiverSetting->type == 'logistics'){
                        $newReceiverSetting->update([
                            'is_ok' => !empty($oldReceiverSet->is_logistics) ?? 0,
                            'memo' => $oldReceiverSet->logistics_memo,
                            'admin_id' => $oldReceiverSet->admin_id,
                        ]);
                    }
                    if($newReceiverSetting->type == 'out'){
                        $newReceiverSetting->update([
                            'is_ok' => !empty($oldReceiverSet->is_out) ?? 0,
                            'memo' => $oldReceiverSet->out_memo,
                            'admin_id' => $oldReceiverSet->admin_id,
                        ]);
                    }
                    if($newReceiverSetting->type == 'pickup'){
                        $newReceiverSetting->update([
                            'is_ok' => !empty($oldReceiverSet->is_extract) ?? 0,
                            'memo' => $oldReceiverSet->extract_memo,
                            'admin_id' => $oldReceiverSet->admin_id,
                        ]);
                    }
                }
            }
            echo "Receiver Base Setting 建立完成\n";
        }

        if (env('DB_MIGRATE_ICARRY_SOURCES')) {
            $data = [];
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

        if (env('DB_MIGRATE_ICARRY_AIRPORT_ADDRESSES')) {
            $array = [
                ['country_id' => 1, 'name' => '桃園機場/第一航廈出境大廳門口', 'value' => '桃園機場/第一航廈出境大廳門口', 'name_en'=> 'Taiwan Taoyuan Airport - Terminal 1 Departure Hall','pickup_time_start' => '00:30', 'pickup_time_end' => '23:59'],
                ['country_id' => 1, 'name' => '桃園機場/第二航廈出境大廳門口', 'value' => '桃園機場/第二航廈出境大廳門口', 'name_en'=> 'Taiwan Taoyuan Airport - Terminal 2 Departure Hall','pickup_time_start' => '06:00', 'pickup_time_end' => '23:00'],
                ['country_id' => 1, 'name' => '松山機場/第一航廈台灣宅配通（E門旁）', 'value' => '松山機場/第一航廈台灣宅配通（E門旁）', 'name_en'=> 'Taipei Songshan Airport - Terminal 1 Taiwan Pelican Express Counter (Exit E)','pickup_time_start' => '05:00', 'pickup_time_end' => '22:00'],
                ['country_id' => 1, 'name' => '花蓮航空站/挪亞方舟旅遊', 'value' => '花蓮航空站/挪亞方舟旅遊', 'name_en'=> 'Hualien Airport - Ark Travel Agency Counter','pickup_time_start' => '08:00', 'pickup_time_end' => '20:30'],
                ['country_id' => 5, 'name' => '東京成田機場第一航廈4樓出境大廳南翼', 'value' => '東京成田機場第一航廈4樓出境大廳南翼', 'name_en'=> '東京成田機場第一航廈4樓出境大廳南翼','pickup_time_start' => null, 'pickup_time_end' => null],
                ['country_id' => 5, 'name' => '東京成田機場第二航廈3樓出境大廳', 'value' => '東京成田機場第二航廈3樓出境大廳', 'name_en'=> '東京成田機場第二航廈3樓出境大廳','pickup_time_start' => null, 'pickup_time_end' => null],
                ['country_id' => 5, 'name' => '東京羽田機場3樓出境大境', 'value' => '東京羽田機場3樓出境大境', 'name_en'=> '東京羽田機場3樓出境大境','pickup_time_start' => null, 'pickup_time_end' => null],
            ];
            AirportAddressDB::insert($array);
            echo "機場資料完成\n";
        }
    }
}
