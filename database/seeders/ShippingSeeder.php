<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use App\Models\Country as CountryDB;
use App\Models\ShippingLocal as ShippingLocalDB;
use App\Models\ShippingVendor as ShippingVendorDB;
use App\Models\ShippingMethod as ShippingMethodDB;
use App\Models\ShippingFee as ShippingFeeDB;

class ShippingSeeder extends Seeder
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
            ['name' => '泰國', 'name_en' => 'Thailand Bangkok', 'lang' => 'th', 'code' => '+66', 'name_jp' => 'タイ'],
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
        if (env('DB_MIGRATE_COUNTRIES')) {
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
                ];
            }
            $chunks = array_chunk($data, 5000);
            foreach($chunks as $chunk){
                CountryDB::insert($chunk);
            }
            echo "Country 建立完成\n";
        }

        if (env('DB_MIGRATE_SHIPPING_LOCALS')) {
            //Shipping Local 資料
            $shippingLocals = [
                ['name' => '當地地址', 'name_en' => 'Local Address'],
                ['name' => '當地機場', 'name_en' => 'Local Airport'],
                ['name' => '當地旅店', 'name_en' => 'Local Hotel'],
            ];
            ShippingLocalDB::insert($shippingLocals);
            echo "Shipping Local 建立完成\n";
        }

        if (env('DB_MIGRATE_SHIPPING_VENDORS')) {
            //Shipping Vendor 資料遷移
            $oldSPvendors = DB::connection('icarryOld')->table('shipping_vendor')->get();
            foreach ($oldSPvendors as $oldVendor) {
                $shippingVendor = ShippingVendorDB::create([
                    'name' => $oldVendor->name,
                    'name_en' => $oldVendor->name_en,
                    'api_url' => $oldVendor->api_url,
                    'is_foreign' => $oldVendor->is_foreign,
                    'sort' => $oldVendor->sort_id,
                ]);
                $oldVendor->is_delete == 1 ? $shippingVendor->delete() : '';
            }
            echo "Shipping Vendor 遷移完成\n";
        }

        if (env('DB_MIGRATE_SHIPPING_METHODS')) {
            //Shipping Method 遷移
            $oldSPmethods = DB::connection('icarryOld')->table('shipping_method')->get();
            foreach ($oldSPmethods as $oldSPmethod) {
                ShippingMethodDB::create([
                    'name' => $oldSPmethod->name,
                ]);
            }
            echo "Shipping Method 遷移完成\n";
        }

        if (env('DB_MIGRATE_SHIPPING_FEES')) {
            //物流運費遷移
            $oldSPsettings = DB::connection('icarryOld')->table('shipping_set')->get();
            $countries = CountryDB::all();
            $shippingLocals = ShippingLocalDB::all();
            $data = [];
            foreach ($oldSPsettings as $oldSPsetting) {
                $oldSPsetting->shipping_methods = str_replace(array('南韓','泰國-曼谷'), array('韓國','泰國'), $oldSPsetting->shipping_methods);
                $to = 0;
                $is_local = 1;
                foreach ($countries as $country) {
                    $country->name == $oldSPsetting->product_sold_country ? $from = $country->id : '';
                    $oldSPsetting->shipping_methods == $country->name ? $to = $country->id : '';
                    $oldSPsetting->shipping_methods == $country->name ? $is_local = 0 : '';
                }
                if ($is_local == 1) {
                    foreach ($shippingLocals as $shippingLocal) {
                        $shippingLocal->name == $oldSPsetting->shipping_methods ? $local_id = $shippingLocal->id : '';
                    }
                    $to = $from;
                } else {
                    $local_id = 1;
                }
                $oldSPsetting->shipping_type == 'base' ? $price = $oldSPsetting->shipping_base_price : $price = $oldSPsetting->shipping_kg_price;
                $data[] = [
                    'from' => $from,
                    'to' => $to,
                    'description' => $oldSPsetting->description_tw,
                    'description_en' => $oldSPsetting->description_en,
                    'type' => $oldSPsetting->shipping_type,
                    'price' => $price,
                    'free_shipping' => $oldSPsetting->free_shipping,
                    'tax_rate' => $oldSPsetting->tax_rate,
                    'is_local' => $is_local,
                    'shipping_local_id' => $local_id,
                    'is_on' => $oldSPsetting->is_on,
                ];
            }
            $chunks = array_chunk($data, 5000);
            foreach($chunks as $chunk){
                ShippingFeeDB::insert($chunk);
            }
            echo "Shipping Fee 遷移完成\n";
        }
    }
}
