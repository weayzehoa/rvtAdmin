<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryCurationLang as CurationLangDB;
use App\Models\iCarryCurationImage as CurationImageDB;
use App\Models\iCarryCurationImageLang as CurationImageLangDB;
use App\Models\iCarryCurationProduct as CurationProductDB;
use App\Models\iCarryCurationVendor as CurationVendorDB;
use DB;

class CurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (env('DB_MIGRATE_ICARRY_CURATIONS')) {
            // Curation 遷移
            $data = [];
            $oldCurations = DB::connection('icarry')->table('curation')->get();
            $curationId = 1;
            foreach ($oldCurations as $oldCuration) {
                $oldCuration->is_select == 'photo' ? $oldCuration->is_select = 'image' : '';
                empty($oldCuration->layout) ? $oldCuration->layout = 4 : '';
                $oldCuration->more_caption == '' ? $oldCuration->more_caption = null : '';
                $data[] = [
                    'category' => 'home', //首頁策展
                    'main_title' => $oldCuration->main_title,
                    'show_main_title' => 1,
                    'sub_title' => $oldCuration->subtitle,
                    'show_sub_title' => 1,
                    'columns' => $oldCuration->layout,
                    'rows' => 1,
                    'caption' => $oldCuration->more_caption,
                    'type' => $oldCuration->is_select,
                    'url' => $oldCuration->more_caption_url,
                    'old_url' => $oldCuration->more_caption_url,
                    'show_url' => 1,
                    'start_time' => $oldCuration->start_time,
                    'end_time' => $oldCuration->end_time,
                    'is_on' => $oldCuration->is_on,
                    'sort' => $oldCuration->sort,
                    'created_at' => $oldCuration->create_time,
                    'updated_at' => $oldCuration->update_time,
                    'old_curation_id' => $oldCuration->id,
                    'old_text_layout' => $oldCuration->text_layout,
                ];

                if(env('DB_MIGRATE_ICARRY_CURATION_LANGS')){
                    $langData['en']['main_title'] = $oldCuration->main_title_en;
                    $langData['en']['sub_title'] = $oldCuration->main_title_en;
                    $langData['en']['caption'] = $oldCuration->more_caption_en == '' ? $oldCuration->more_caption_en = null : '';

                    $langData['jp']['main_title'] = $oldCuration->main_title_jp;
                    $langData['jp']['sub_title'] = $oldCuration->main_title_jp;
                    $langData['jp']['caption'] = $oldCuration->more_caption_jp == '' ? $oldCuration->more_caption_jp = null : '';

                    $langData['kr']['main_title'] = $oldCuration->main_title_kr;
                    $langData['kr']['sub_title'] = $oldCuration->main_title_kr;
                    $langData['kr']['caption'] = $oldCuration->more_caption_kr == '' ? $oldCuration->more_caption_kr = null : '';

                    $langData['th']['main_title'] = $oldCuration->main_title_th;
                    $langData['th']['sub_title'] = $oldCuration->main_title_th;
                    $langData['th']['caption'] = $oldCuration->more_caption_th == '' ? $oldCuration->more_caption_th = null : '';
                    $langs = ['en','jp','kr','th'];
                    $data2 = [];
                    foreach($langs as $key => $lang){
                        $data2[] = [
                            'curation_id' => $curationId,
                            'lang' => $lang,
                            'main_title' => $langData[$lang]['main_title'],
                            'sub_title' => $langData[$lang]['sub_title'],
                            'caption' => $langData[$lang]['caption'],
                            'created_at' => $oldCuration->create_time,
                            'updated_at' => $oldCuration->update_time,
                        ];
                    }
                    $chunks2 = array_chunk($data2, 5000);
                    foreach($chunks2 as $chunk2){
                        CurationLangDB::insert($chunk2);
                    }
                }

                if(env('DB_MIGRATE_ICARRY_CURATION_VENDORS')){
                    $data3 = [];
                    if($oldCuration->is_select == 'vendor'){
                        if ($oldCuration->select_data) {
                            $vendors = explode(',', $oldCuration->select_data);
                            foreach ($vendors as $key => $id) {
                                $data3[] = [
                                    'curation_id' => $curationId,
                                    'vendor_id' => $id,
                                    'sort' => $key + 1,
                                    'created_at' => $oldCuration->create_time,
                                    'updated_at' => $oldCuration->update_time,
                                ];
                            }
                        }
                        $chunks3 = array_chunk($data3, 5000);
                        foreach($chunks3 as $chunk3){
                            CurationVendorDB::insert($chunk3);
                        }
                    }
                }

                if(env('DB_MIGRATE_ICARRY_CURATION_PRODUCTS')){
                    $data4 = [];
                    if($oldCuration->is_select == 'product'){
                        if($oldCuration->select_data){
                            $products = explode(',',$oldCuration->select_data);
                            foreach ($products as $key => $id) {
                                $data4[] = [
                                    'curation_id' => $curationId,
                                    'product_id' => $id,
                                    'sort' => $key + 1,
                                    'created_at' => $oldCuration->create_time,
                                    'updated_at' => $oldCuration->update_time,
                                ];
                            }
                        }
                        $chunks4 = array_chunk($data4, 5000);
                        foreach($chunks4 as $chunk4){
                            CurationProductDB::insert($chunk4);
                        }
                    }
                }

                if(env('DB_MIGRATE_ICARRY_CURATION_IMAGES')){
                    $data5 = [];
                    if ($oldCuration->is_select == 'image') {
                        if($oldCuration->photo_data){
                            $photoData = collect(json_decode($oldCuration->photo_data,true));
                            if(count($photoData)> 0){
                                for($i=0;$i<count($photoData);$i++){
                                    $curationImage = CurationImageDB::create([
                                        'style' => 'image',
                                        'curation_id' => $curationId,
                                        'main_title' => $photoData[$i]['photo_main_title'],
                                        'sub_title' => $photoData[$i]['photo_subtitle'],
                                        'caption' => null,
                                        'row' => 1,
                                        'text_position' => $oldCuration->text_layout,
                                        'url' => $photoData[$i]['photo_url'],
                                        'old_url' => $photoData[$i]['photo_url'],
                                        'image' => $photoData[$i]['photo'],
                                        'sort' => $i+1,
                                        'created_at' => $oldCuration->create_time,
                                        'updated_at' => $oldCuration->update_time,
                                    ]);
                                    $imgLangData['en']['main_title'] = $photoData[$i]['photo_main_title_en'];
                                    $imgLangData['en']['sub_title'] = $photoData[$i]['photo_main_title_en'];
                                    $imgLangData['en']['caption'] = null;

                                    $imgLangData['jp']['main_title'] = $photoData[$i]['photo_main_title_jp'];
                                    $imgLangData['jp']['sub_title'] = $photoData[$i]['photo_main_title_jp'];
                                    $imgLangData['jp']['caption'] = null;

                                    $imgLangData['kr']['main_title'] = $photoData[$i]['photo_main_title_kr'];
                                    $imgLangData['kr']['sub_title'] = $photoData[$i]['photo_main_title_kr'];
                                    $imgLangData['kr']['caption'] = null;

                                    $imgLangData['th']['main_title'] = $photoData[$i]['photo_main_title_th'];
                                    $imgLangData['th']['sub_title'] = $photoData[$i]['photo_main_title_th'];
                                    $imgLangData['th']['caption'] = null;
                                    $imgLangs = ['en','jp','kr','th'];
                                    if (env('DB_MIGRATE_ICARRY_CURATION_IMAGE_LANGS')) {
                                        $data6 = [];
                                        foreach ($imgLangs as $key => $imgLang) {
                                            $data6[] = [
                                                'curation_image_id' => $curationImage->id,
                                                'lang' => $imgLang,
                                                'main_title' => $imgLangData[$imgLang]['main_title'],
                                                'sub_title' => $imgLangData[$imgLang]['sub_title'],
                                                'caption' => $imgLangData[$imgLang]['caption'],
                                                'created_at' => $oldCuration->create_time,
                                                'updated_at' => $oldCuration->update_time,
                                            ];
                                        }
                                        $chunks6 = array_chunk($data6, 5000);
                                        foreach($chunks6 as $chunk6){
                                            CurationImageLangDB::insert($chunk6);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $curationId++;
            }
            $chunks = array_chunk($data, 5000);
            foreach($chunks as $chunk){
                CurationDB::insert($chunk);
            }
            echo "Curation 遷移完成\n";
        }
    }
}
