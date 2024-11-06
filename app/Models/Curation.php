<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\CurationLang as CurationLangDB;
use App\Models\CurationImage as CurationImageDB;
use App\Models\CurationImageLang as CurationImageLangDB;
use App\Models\CurationProduct as CurationProductDB;
use App\Models\UserFavoriteProduct as UserFavoriteProductDB;
use App\Models\Vendor as VendorDB;
use App\Models\VendorLang as VendorLangDB;
use App\Models\CurationVendor as CurationVendorDB;
use App\Models\ProductImage as ProductImageDB;
use App\Models\ProductLang as ProductLangDB;
use App\Models\Product as ProductDB;
use DB;

class Curation extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '首頁策展';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用
    protected $hidden = ['created_at','updated_at']; //隱藏欄位
    protected $fillable = [
        'category',
        'vendor_id',
        'main_title',
        'show_main_title',
        'main_title_background',
        'show_main_title_background',
        'sub_title',
        'show_sub_title',
        'background_color',
        'background_image',
        'background_css',
        'show_background_type',
        'columns',
        'rows',
        'caption',
        'type',
        'url',
        'url_open_window',
        'show_url',
        'start_time',
        'end_time',
        'is_on',
        'sort',
    ];

    protected $langs;
    protected $lang = '';
    protected $awsFileUrl;

    public function vendor(){
        return $this->belongsTo(VendorDB::class);
    }

    public function langs()
    {
        return $this->hasMany(CurationLangDB::class)->select([
            'curation_id',
            'lang',
            'main_title',
            'sub_title',
            'caption',
        ]);
    }

    public function images()
    {
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $images = $this->hasMany(CurationImageDB::class)
                ->where('style','image')->orderBy('sort','asc')
                ->select([
                    'id',
                    'curation_id',
                    'style',
                    'open_method',
                    'main_title',
                    'show_main_title',
                    'sub_title',
                    'show_sub_title',
                    'text_position',
                    'url',
                    'url_open_window',
                    'modal_content',
                    DB::raw("(CASE WHEN image is not null THEN CONCAT('$this->awsFileUrl',image) END) as image"),
                    'sort'
                ]);

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $images = $images->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.sub_title END) as sub_title"),
                DB::raw("(CASE WHEN (SELECT modal_content from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT modal_content from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT modal_content from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT modal_content from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.modal_content END) as modal_content"),
            ]);
        }

        return $images;
    }

    public function blocks()
    {
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $blocks = $this->hasMany(CurationImageDB::class)
                ->where('style','block')->orderBy('sort','asc')
                ->select([
                    'id',
                    'curation_id',
                    'style',
                    'main_title',
                    'show_main_title',
                    'sub_title',
                    'show_sub_title',
                    'text_position',
                    'url',
                    'url_open_window',
                    DB::raw("(CASE WHEN image is not null THEN CONCAT('$this->awsFileUrl',image) END) as image"),
                    'sort',
                    'row',
                ]);

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $blocks = $blocks->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.sub_title END) as sub_title"),
            ]);
        }

        return $blocks;
    }

    public function noWordBlocks()
    {
        return $this->hasMany(CurationImageDB::class)
                ->where('style','nowordblock')->orderBy('sort','asc')
                ->select([
                    'id',
                    'curation_id',
                    'style',
                    'url',
                    'url_open_window',
                    DB::raw("(CASE WHEN image is not null THEN CONCAT('$this->awsFileUrl',image) END) as image"),
                    'sort',
                    'row',
                ]);
    }

    public function events()
    {
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $events = $this->hasMany(CurationImageDB::class)
                ->where('style','event')->orderBy('sort','asc')
                ->select([
                    'id',
                    'curation_id',
                    'style',
                    'main_title',
                    'show_main_title',
                    'sub_title',
                    'show_sub_title',
                    'text_position',
                    'url',
                    DB::raw("(CASE WHEN image is not null THEN CONCAT('$this->awsFileUrl',image) END) as image"),
                    'sort',
                ]);

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $events = $events->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_image_langs where curation_image_langs.curation_image_id = curation_images.id and curation_image_langs.lang = 'en' limit 1) ELSE curation_images.sub_title END) as sub_title"),
                ]);
        }

        return $events;
    }

    public function vendors()
    {
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $vendors = $this->hasMany(CurationVendorDB::class,'curation_id','id')
                ->join('vendors','vendors.id','curation_vendors.vendor_id')
                ->where('vendors.is_on',1)
                ->select([
                    'curation_vendors.id',
                    'curation_vendors.curation_id',
                    'curation_vendors.vendor_id',
                    'curation_vendors.sort',
                    'vendors.name',
                    DB::raw("(CASE WHEN img_logo is not null THEN CONCAT('$this->awsFileUrl',img_logo) END) as img_logo"),
                    DB::raw("(CASE WHEN img_cover is not null THEN CONCAT('$this->awsFileUrl',img_cover) END) as img_cover"),
                    'vendors.curation',
                    'vendors.is_on',
                ]);

        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $vendors = $vendors->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) ELSE vendors.name END) as name"),
                DB::raw("(CASE WHEN (SELECT curation from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT curation from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT curation from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) ELSE vendors.curation END) as curation"),
            ]);
        }

        return $vendors;
    }

    //前後台共用，前台判斷參數帶語言資料，後台用with語言資料加快速度，前台unset掉即可
    public function products()
    {
        $this->langs = ['','en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $this->userId = request()->userId;
        $products = $this->hasMany(CurationProductDB::class)->with('langs')
            ->join('products','products.id','curation_products.product_id')
            ->join('vendors','vendors.id','products.vendor_id')
            ->where('vendors.is_on',1)
            ->whereIn('products.status',[1,-3])
            ->select([
                'curation_products.id',
                'curation_products.curation_id',
                'curation_products.sort',
                'products.id as product_id',
                'products.name',
                'products.curation_text_top',
                'products.curation_text_bottom',
                DB::raw("(CASE WHEN products.fake_price > 0 THEN products.fake_price END) as fake_price"),
                'products.price',
                'products.status',
                'vendors.name as vendor_name',
                'image' => ProductImageDB::whereColumn('products.id', 'product_images.product_id')->where('is_on',1)
                ->select(DB::raw("(CASE WHEN filename is not null THEN (CONCAT('$this->awsFileUrl',filename)) END) as image"))->orderBy('sort','asc')->limit(1),
            ]);
        if(!empty($this->userId)){
            $products = $products->addSelect([
                'is_favorite' => UserFavoriteProductDB::whereColumn('products.id', 'user_favorite_products.product_id')->where('user_favorite_products.user_id',$this->userId)->select([
                    DB::raw("(CASE WHEN count(id) > 0 THEN 1 ELSE 0 END)")
                ])->limit(1),
            ]);
        }
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) ELSE vendors.name END) as vendor_name"),
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) ELSE products.name END) as name"),
                DB::raw("(CASE WHEN (SELECT curation_text_top from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation_text_top from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT curation_text_top from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT curation_text_top from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) ELSE products.curation_text_top END) as curation_text_top"),
                DB::raw("(CASE WHEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) ELSE products.curation_text_bottom END) as curation_text_bottom"),
            ]);
        }
        $products = $products->orderBy('sort','asc');
        return $products;
    }
}
