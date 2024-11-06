<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryCategoryLang as CategoryLangDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryProductLang as ProductLangDB;
use App\Models\iCarryProductPackage as ProductPackageDB;
use App\Models\iCarryProductPackageList as ProductPackageListDB;
use App\Models\iCarryProductUnitName as ProductUnitNameDB;
use DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('DB_MIGRATE_ICARRY_CATEGORY_LANGS')) {
            $oldcategories = CategoryDB::get();
            foreach ($oldcategories as $oldcategorie) {
                $langs = ['en'=>$oldcategorie->name_en,'jp' => $oldcategorie->name_jp,'kr' => $oldcategorie->name_kr,'th' => $oldcategorie->name_th];
                foreach($langs as $key => $value){
                    if(!empty($value) && $value != 'test' && $value != 'qdscdwf' && $value != 'th' && $value != '6666'){
                        $categoryLang = CategoryLangDB::create([
                            'category_id' => $oldcategorie->id,
                            'lang' => $key,
                            'name' => $value,
                        ]);
                    }
                }
            }
            echo "Category Lang遷移完成\n";
        }

        if (env('DB_MIGRATE_ICARRY_PRODUCT_LANGS')) {
            //遷移舊語言資料
            $langs = array('en','jp','kr','th');
            for ($i=0;$i<count($langs);$i++) {
                $lang = $langs[$i];
                $subQuery = DB::connection('icarryLang')->table('product_'.$langs[$i]);
                $query = DB::query()->fromSub($subQuery, 'alias')->orderBy('alias.id','asc')->chunk(5000, function ($oldDBs) use($lang) {
                    $data = [];
                    foreach ($oldDBs as $oldDB) {
                        $data[] = [
                            'product_id' => $oldDB->id,
                            'lang' => $lang,
                            'name' => $oldDB->name,
                            'brand' => $oldDB->brand,
                            'serving_size' => $oldDB->serving_size,
                            'title' => $oldDB->title,
                            'intro' => $oldDB->intro,
                            'model_name' => $oldDB->model_name,
                            'specification' => $oldDB->model_name,
                            'created_at' => $oldDB->create_time,
                            'updated_at' => $oldDB->update_time,
                        ];
                    }
                    ProductLangDB::insert($data);
                });
            }
            echo "Product Language 遷移完成\n";
        }
        $countries = CountryDB::all();
        $unitNames = ProductUnitNameDB::all();
        if (env('DB_MIGRATE_ADD_TO_ICARRY_PRODUCT_TABLE') || env('DB_MIGRATE_ICARRY_PRODUCT_PACKAGES')) {
            //Product Package資料遷移
            ProductDB::with('models')->orderBy('id','asc')->chunk(1000, function ($oldProducts) use($countries,$unitNames) {
                $data = [];
                foreach ($oldProducts as $product) {
                    if (env('DB_MIGRATE_ADD_TO_ICARRY_PRODUCT_TABLE')) {
                        //單位代號對應
                        $unitNameId = null;
                        foreach ($unitNames as $unitName) {
                            $product->unit_name == $unitName->name ? $unitNameId = $unitName->id : '';
                        }
                       //國家代號對應
                        $fromCountryId = null;
                        foreach ($countries as $country) {
                            $product->product_sold_country == $country->name ? $fromCountryId = $country->id : '';
                        }
                        //寄送國家轉換
                        $allowCountryIds = null;
                        $newAllowCountry = [];
                        $oldAllowCountry = explode(',', $product->allow_country);
                        if(count($oldAllowCountry) > 0){
                            for ($i=0;$i<count($oldAllowCountry);$i++) {
                                //國家代號對應
                                foreach ($countries as $country) {
                                    $oldAllowCountry[$i] == $country->name ? $newAllowCountry[$i] = $country->id : '';
                                }
                            }
                            $allowCountryIds = join(',', $newAllowCountry);
                        }
                        //找出款式類別 (1: 單一, 2: 多款, 3:組合, 0:資料異常)
                        $modelType = null;
                        if (!empty($product->package_data) || $product->package_data != null || $product->package_data != '') {
                            $packageData = json_decode(str_replace('	','',$product->package_data));
                            $chk = 0;
                            if(is_array($packageData)){
                                foreach($packageData as $package){
                                    if(isset($package->is_del)){
                                        if($package->is_del == 0){
                                            $chk++;
                                        }
                                    }else{
                                        $chk++;
                                    }
                                }
                            }
                            $chk > 0 ? $modelType = 3 : $modelType = 0;
                        } else {
                            $count = count($product->models);
                            if ($count == 1) {
                                $modelType = 1;
                            } elseif ($count > 1) {
                                $modelType = 2;
                            }
                        }
                        $product->update(['allow_country_ids' => $allowCountryIds,'from_country_id' => $fromCountryId, 'model_type' => $modelType, 'unit_name_id' => $unitNameId]);
                    }
                    if (env('DB_MIGRATE_ICARRY_PRODUCT_PACKAGES')) {
                        //Package Data 資料遷移
                        if (!empty($product->package_data)) {
                            $models = collect(str_replace('	','',$product->package_data));
                            foreach ($models as $model) {
                                if (!empty($model->bom)) {
                                    $modelTmp = DB::connection('icarry')->table('product_model')->where('sku', $model->bom)->first();
                                    if(!empty($modelTmp)){
                                        $package = ProductPackageDB::create([
                                            'product_id' => $product->id,
                                            'product_model_id' => $modelTmp->id,
                                        ]);
                                        if(isset($model->is_del) && $model->is_del == 1){
                                            $package->delete();
                                        }
                                    }
                                    if(count($model->lists) > 0){
                                        foreach ($model->lists as $list) {
                                            if (!empty($list->sku)) {
                                                $listTmp = DB::connection('icarry')->table('product_model')->where('sku', $list->sku)->first();
                                                if(!empty($listTmp) && $list->quantity > 0){
                                                    //組合商品中包含多個商品
                                                    ProductPackageListDB::create([
                                                        'product_package_id' => $package->id,
                                                        'product_model_id' => $listTmp->id,
                                                        'quantity' => $list->quantity,
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });
            echo "Product Package 遷移完成\n";
        }
    }
}
