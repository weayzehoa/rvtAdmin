<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\iCarryVendorAccount as VendorAccountDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use DB;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('DB_MIGRATE_ADD_TO_VENDOR_ACCOUNT_TABLE')) {
            //遷移帳號資料
            $oldAccounts = VendorAccountDB::get();
            foreach ($oldAccounts as $oldAccount) {
                $oldAccount->update(['password' => $oldAccount->pwd, 'icarry_token' => strtoupper(str_replace('-','',Str::uuid()->toString()))]);
            }
            echo "Vendor Account 遷移完成\n";
        }
        if (env('DB_MIGRATE_ADD_TO_ICARRY_VENDOR_TABLE')) {
            $oldVendors = VendorDB::get();
            foreach ($oldVendors as $oldVendor) {
                $cover = $logo = $site = null;
                !empty($oldVendor->cover) ? $cover = $oldVendor->cover : '';
                !empty($oldVendor->new_cover) ? $cover = $oldVendor->new_cover : '';
                !empty($oldVendor->logo) ? $logo = $oldVendor->logo : '';
                !empty($oldVendor->new_logo) ? $logo = $oldVendor->new_logo : '';
                !empty($oldVendor->site_cover) ? $site = $oldVendor->site_cover : '';
                !empty($oldVendor->new_site_cover) ? $site = $oldVendor->new_site_cover : '';
                $oldVendor->update(['img_cover' => $cover, 'img_logo' => $logo, 'img_site' => $site ]);
            }
            echo "Vendor 圖片 遷移完成\n";
        }
        if (env('DB_MIGRATE_CREATE_ICARRY_VENDOR_LANGS')) {
            //遷移舊語言資料
            $data = [];
            $langs = array('en','jp','kr','th');
            for ($i=0;$i<count($langs);$i++) {
                $oldDBs = DB::connection('icarryLang')->table('vendor_'.$langs[$i])->get();
                foreach ($oldDBs as $oldDB) {
                    $data[] = [
                        'vendor_id' => $oldDB->id,
                        'lang' => $langs[$i],
                        'name' => $oldDB->name,
                        'summary' => $oldDB->summary,
                        'description' => $oldDB->description,
                        'created_at' => $oldDB->create_time,
                        'updated_at' => $oldDB->update_time,
                    ];
                }
            }
            $chunks = array_chunk($data, 5000);
            foreach($chunks as $chunk){
                VendorLangDB::insert($chunk);
            }
            echo "Vendor Language 遷移完成\n";
        }
    }
}
