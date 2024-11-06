<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin as AdminDB;
use App\Models\AdminLoginLog as AdminLoginLogDB;
use App\Models\AdminPwdUpdateLog as AdminPwdUpdateLogDB;
use App\Models\SystemSetting as SystemSettingDB;
use App\Models\CompanySetting as CompanySettingDB;
use App\Models\PowerAction as PowerActionDB;
use DB;
class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('DB_MIGRATE_ADMINS')) {
            $data = [];
            $oldAdmins = DB::connection('icarryOld')->table('admin')->get();
            $i = 0;
            foreach ($oldAdmins as $oldAdmin) {
                $password = app('hash')->make($oldAdmin->pwd);
                $mainmenuPower = join(',', range(3, 20));
                $submenuPower = join(',', range(1, 50));
                $powerAction = '';
                $power = '';
                //特定管理員的代號, 信成, Chris, Roger, Eide.
                $spID = array('2','4','40','44');
                if (in_array($oldAdmin->id, $spID)) {
                    $power = 'M1S0,M1S1,M1S1N,M1S1D,M1S1M,M1S1O,M1S3,M1S3N,M1S3M,M1S3O,M1S3S,M1S4,M1S4M,M1S5,M1S5M,M1S6,M1S6M,M1S7,M1S7N,M1S7M,M1S7O,M1S7S,M2S0,M2S1,M2S1N,M2S1D,M2S1M,M2S1O,M2S1EX,M2S2,M2S2N,M2S2D,M2S2M,M2S2O,M2S3,M2S3N,M2S3D,M2S3M,M2S3O,M2S3T,M3S0,M3S1,M3S1N,M3S1D,M3S1M,M3S1S,M3S2,M3S2N,M3S2M,M3S2O,M3S3,M4S0,M4S1,M4S1N,M4S1D,M4S1M,M4S1EX,M4S1SM,M4S2,M4S2EX,M4S3,M4S3N,M4S3M,M4S3S,M4S4,M4S4N,M4S4M,M4S4O,M4S4S,M5S0,M5S1,M5S1M,M5S1O,M5S1P,M5S1SMS,M5S1SMM,M5S2,M6S0,M6S1,M6S1M,M6S1IM,M6S1EX,M6S1MK,M6S1PR,M6S1CO,M6S1RM,M6S2,M6S2MK,M6S2CO,M6S3,M6S3NE,M6S3DE,M6S3M,M6S3IM,M6S3EX,M6S3PR,M6S4,M6S4M,M6S4EX,M7S0,M7S1,M7S1N,M7S1M,M7S1O,M7S1S,M7S2,M7S2N,M7S2M,M7S2O,M7S2S,M7S3,M7S3N,M7S3M,M7S3O,M7S4,M7S4N,M7S4M,M7S4O,M8S0,M8S1,M8S2,M8S3,M8S4,M8S5,M8S6,M8S7,M9S0,M9S1,M9S1N,M9S1M,M9S1O,M9S1EX,M9S2,M9S2EX,M9S3,M9S3M,M9S3EX,M9S3MK,M9S4,M10S0,M10S1,M10S2,M10S3,M10S4,M11S0,M11S1,M23S0,M26S0,M26S1,M26S1N,M26S1D,M26S1M,M26S1O,M27S0,M27S1,M28S0,M28S1,M29S0';
                }
                //帳號應該是唯一，但舊資料裡面有兩個eva，故須將其分離成eva0,eva1
                if ($oldAdmin->account == 'eva') {
                    $oldAdmin->account = 'eva'.$i;
                    $i++;
                }
                $data[] = [
                    'account' => $oldAdmin->account,
                    'name' => $oldAdmin->name,
                    'email' => $oldAdmin->email,
                    'password' => $password,
                    'is_on' => $oldAdmin->is_on,
                    'power' => $power,
                    'lock_on' => $oldAdmin->lock_on,
                    'created_at' => $oldAdmin->create_time,
                ];
            }
            $chunks = array_chunk($data, 5000);
            foreach($chunks as $chunk){
                AdminDB::insert($chunk);
            }
            echo "Admin 遷移完成\n";
        }

        if (env('DB_MIGRATE_SYSTEM_SETTINGS')) {
            SystemSettingDB::create([
                'exchange_rate_RMB' => 4.34,
                'exchange_rate_SGD' => 21.80,
                'exchange_rate_MYR' => 7.30,
                'exchange_rate_HKD' => 3.75,
                'exchange_rate_USD' => 30.35,
                'sms_supplier' => 'twilio',
                'payment_supplier' => '藍新',
                'email_supplier' => 'aws',
                'invoice_supplier' => 'ezpay',
                'customer_service_supplier' => 'crisp',
                'admin_id' => 40,
                'twpay_quota' => 100042,
                'gross_weight_rate' => 1.3,
            ]);
            echo "System Setting 建立完成\n";
        }

        if (env('DB_MIGRATE_COMPANY_SETTINGS')) {
            CompanySettingDB::create([
                'name' => '直流電通股份有限公司',
                'name_en' => 'Direct Current Co., Ltd.',
                'tax_id_num' => '46452701',
                'tel' => '+886-2-2508-2891',
                'fax' => '+886-2-2508-2892',
                'address' => '台灣台北市中山區南京東路三段103號11樓之1',
                'address_en' => 'Rm. 1, 11F., No. 103, Sec. 3, Nanjing E. Rd., Zhongshan Dist., Taipei City 104507, Taiwan (R.O.C.)',
                'service_tel' => '+886-906-486688',
                'service_email' => 'icarry@icarry.me',
                'url' => 'https://icarry.me/',
                'website' => 'icarry.me',
                'fb_url' => 'https://www.facebook.com/icarryme',
                'Instagram_url' => 'https://www.instagram.com/icarrytaiwan/',
                'Telegram_url' => 'https://t.me/icarryme',
                'line' => '',
                'wechat' => '',
                'admin_id' => 40,
            ]);
            echo "COMPANY Setting 建立完成\n";
        }

        $PowerActions = [
            ['name' => '新增', 'code' => 'N'],
            ['name' => '刪除', 'code' => 'D'],
            ['name' => '開立', 'code' => 'NE'],
            ['name' => '作廢', 'code' => 'DE'],
            ['name' => '修改', 'code' => 'M'],
            ['name' => '上線/架、啟用', 'code' => 'O'],
            ['name' => '排序', 'code' => 'S'],
            ['name' => '匯入', 'code' => 'IM'],
            ['name' => '匯出', 'code' => 'EX'],
            ['name' => '審查', 'code' => 'C'],
            ['name' => '執行', 'code' => 'E'],
            ['name' => '傳送門', 'code' => 'T'],
            ['name' => '購物金', 'code' => 'P'],
            ['name' => '其他', 'code' => 'X'],
            ['name' => '發送簡訊', 'code' => 'SMS'],
            ['name' => '發送訊息', 'code' => 'SMM'],
            ['name' => '註記', 'code' => 'MK'],
            ['name' => '列印', 'code' => 'PR'],
            ['name' => '取消訂單', 'code' => 'CO'],
            ['name' => '發退款信', 'code' => 'RM'],
            ['name' => '庫存調整', 'code' => 'SM'],
        ];

        if (env('DB_MIGRATE_POWER_ACTIONS')) {
            PowerActionDB::insert($PowerActions);
            echo "Power Actions 建立完成\n";
        }

        if (env('DB_MIGRATE_ADMIN_LOGIN_LOGS')) {
            //ADMIN LOGIN LOGS 資料移轉
            $subQuery = DB::connection('icarryOld')->table('admin_login_log');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(5000, function ($items) {
                $data = [];
                foreach ($items as $item) {
                    $data[] = [
                        'admin_id' => $item->admin_id,
                        'result' => $item->result,
                        'ip' => $item->ip,
                        'account' => $item->account,
                        'created_at' => $item->create_time,
                    ];
                }
                AdminLoginLogDB::insert($data);
            });
            echo "ADMIN LOGIN LOG 遷移完成\n";
        }

        if (env('DB_MIGRATE_ADMIN_PWD_UPDATE_LOGS')) {
            //ADMIN PWD UPDATE LOGS 資料移轉
            $subQuery = DB::connection('icarryOld')->table('admin_pwd_update_log');
            $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(5000, function ($items) {
                $data = [];
                foreach ($items as $item) {
                    $data[] = [
                        'admin_id' => $item->admin_id,
                        'ip' => $item->ip,
                        'password' => app('hash')->make($item->pwd),
                        'editor_id' => $item->admin_id,
                        'created_at' => $item->create_time,
                    ];
                }
                AdminPwdUpdateLogDB::insert($data);
            });
            echo "ADMIN LOGIN LOG 遷移完成\n";
        }
    }
}
