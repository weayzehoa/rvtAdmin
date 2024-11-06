<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use Illuminate\Database\Eloquent\SoftDeletes; //使用軟刪除
// use Laravel\Scout\Searchable; //搜尋功能

use App\Models\ProductImage as ProductImageDB;
use App\Models\ProductLang as ProductLangDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\ProductPackage as ProductPackageDB;
use App\Models\ProductPackageList as ProductPackageListDB;
use App\Models\ProductUnitName as ProductUnitNameDB;
use App\Models\HotProduct as HotProductDB;
use App\Models\Vendor as VendorDB;
use App\Models\VendorLang as VendorLangDB;
use App\Models\Category as CategoryDB;
use App\Models\Country as CountryDB;
use DB;

class Product extends Model
{
    use HasFactory;
    // use Searchable;
    //使用軟刪除
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '商品資料';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'id', //資料移轉後須將此行移除
        'vendor_id',
        'category_id',
        'unit_name_id',
        'from_country_id',
        'name',
        'export_name_en',
        'brand',
        'serving_size',
        'shipping_methods',
        'price',
        'gross_weight',
        'net_weight',
        'title',
        'intro',
        'model_name',
        'model_type',
        'is_tax_free',
        'specification',
        'verification_reason',
        'status',
        'is_hot',
        'hotel_days',
        'airport_days',
        'storage_life',
        'fake_price',
        'TMS_price',
        'allow_country',
        'vendor_price',
        'unable_buy',
        'pause_reason',
        'tags',
        'is_del',
        'pass_time',
        'curation_text_top',
        'curation_text_bottom',
        'service_fee_percent',
        // 'package_data', 此欄位不在使用，僅保留舊資料
    ];
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'name' => $this->name,
    //         'brand' => $this->brand,
    //         'title' => $this->title,
    //         'intro' => $this->intro,
    //         'model_name' => $this->model_name,
    //         'specification' => $this->specification,
    //     ];
    // }

    public function languages(){
        return $this->hasMany(ProductLangDB::class);
    }

    public function models(){
        return $this->hasMany(ProductModelDB::class);
    }

    public function category(){
        return $this->belongsTo(CategoryDB::class, 'category_id', 'id');
    }

    public function unitName(){
        return $this->belongsTo(ProductUnitNameDB::class, 'unit_name_id', 'id');
    }

    public function vendor(){
        return $this->belongsTo(VendorDB::class, 'vendor_id', 'id');
    }

    public function from(){
        return $this->belongsTo(CountryDB::class, 'from_country_id', 'id');
    }

/* 下面 function 前台使用 */
    public function images(){
        $host = env('AWS_FILE_URL');
        return $this->hasMany(ProductImageDB::class)->where('is_on',1)
        ->select([
            'product_id',
            DB::raw("CONCAT('$host',filename) as filename"),
            'sort',
        ])->orderBy('sort','asc');
    }

    public function image(){
        $host = env('AWS_FILE_URL');
        return $this->hasOne(ProductImageDB::class)->where('is_on',1)
        ->select([
            'product_id',
            DB::raw("CONCAT('$host',filename) as filename"),
        ])->orderBy('sort','asc');
    }

    public function vendorLangs(){
        return $this->hasMany(VendorLangDB::class, 'vendor_id', 'vendor_id')
                ->select([
                    'vendor_id',
                    'lang',
                    'name',
                    'summary',
                    'description',
                ]);
    }

    public function langs(){
        return $this->hasMany(ProductLangDB::class,'product_id','id')
        ->select([
            'product_id',
            'lang',
            'name',
            'brand',
            'serving_size',
            'unable_buy',
            'title',
            'intro',
            'model_name',
            'specification',
            'curation_text_top',
            'curation_text_bottom',
        ]);
    }

    public function styles(){
        $langs = ['en','jp','kr','th'];
        $lang = request()->lang;
        $styles = $this->hasMany(ProductModelDB::class)
        ->select([
            'id as product_model_id',
            'product_id',
            'name',
            'sku',
            'quantity',
            'safe_quantity',
        ]);
        if(!empty($lang) && in_array($lang,$langs)){
            $styles = $styles->addSelect([
                DB::raw("(CASE WHEN name_$lang != '' THEN name_$lang WHEN name_en != '' THEN name_en ELSE name END) as name"),
            ]);
        }
        return $styles;
    }

    public function packages(){
        $langs = ['en','jp','kr','th'];
        $lang = request()->lang;
        $packages = $this->hasMany(ProductPackageDB::class)
            ->join('product_models','product_models.id','product_packages.product_model_id')
            ->select([
                'product_packages.*',
                'product_models.name',
                'product_models.sku',
                'product_models.quantity',
                'product_models.safe_quantity',
            ]);
        if(!empty($lang) && in_array($lang,$langs)){
            $packages = $packages->addSelect([
                DB::raw("(CASE WHEN product_models.name_{$lang} != '' THEN product_models.name_{$lang} WHEN product_models.name_en != '' THEN product_models.name_en ELSE product_models.name END) as name"),
            ]);
        }
        return $packages;
    }

    public function packs(){
        return $this->hasMany(ProductPackageDB::class)
        ->join('product_models','product_models.id','product_packages.product_model_id')
        ->select([
            'product_packages.*',
            'product_models.name',
            'product_models.sku',
            'product_models.quantity',
            'product_models.safe_quantity',
        ]);
    }

    public function vendorHotProducts(){
        $langs = ['en','jp','kr','th'];
        $lang = request()->lang;
        $host = env('AWS_FILE_URL');
        $hotProducts = $this->hasMany(HotProductDB::class,'vendor_id','vendor_id')
            ->join('products','products.id','hot_products.product_id')
            ->join('vendors','vendors.id','hot_products.vendor_id')
            ->whereIn('products.status',[1,-3])
            ->select([
                'hot_products.vendor_id',
                'hot_products.product_id',
                'vendors.name as vendor_name',
                'products.name as product_name',
                'products.fake_price',
                'products.price',
                'image' => ProductImageDB::whereColumn('products.id', 'product_images.product_id')->where('product_images.is_on',1)
                    ->selectRaw("(CASE WHEN product_images.filename != '' THEN (CONCAT('$host',product_images.filename)) END) as image")->orderBy('sort','asc')->limit(1),
            ]);
        if(!empty($lang) && in_array($lang,$langs)){
            $hotProducts = $hotProducts->addSelect([
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = '$lang' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = '$lang' limit 1) ELSE products.name END) as name"),
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '$lang' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '$lang' limit 1) ELSE vendors.name END) as vendor_name"),
            ]);
        }
        $hotProducts = $hotProducts->orderBy('hot_products.hits','desc');
        return $hotProducts;
    }
}
