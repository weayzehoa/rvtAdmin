<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use Illuminate\Database\Eloquent\SoftDeletes; //使用軟刪除
// use Laravel\Scout\Searchable; //搜尋功能

use App\Models\Product as ProductDB;
use App\Models\ProductImage as ProductImageDB;
use App\Models\VendorLang as VendorLangDB;
use App\Models\VendorShop as VendorShopDB;
use App\Models\VendorAccount as VendorAccountDB;
use App\Models\OrderShipping as OrderVendorShippingDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\Curation as CurationDB;
use App\Models\HotProduct as HotProductDB;
use App\Models\UserFavoriteProduct as UserFavoriteProductDB;
use DB;

class Vendor extends Model
{
    use HasFactory;
    // use Searchable;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '商家管理';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'name','company','vat_number','boss','contact',
        'tel','fax','email','categories','address','shipping_setup',
        'shipping_verdor_percent','is_on','summary','description',
        'shopping_notice','service_fee',
        'cover','img_cover','img_logo','img_site','shipping_self',
        'factory_address','product_sold_country','curation',
    ];

    public function langs(){
        return $this->hasMany(VendorLangDB::class);
    }

    public function shops(){
        return $this->hasMany(VendorShopDB::class);
    }

    public function accounts(){
        return $this->hasMany(VendorAccountDB::class);
    }

    public function products(){
        return $this->hasMany(ProductDB::class);
    }

    public function orderItems(){
        return $this->hasMany(OrderItemDB::class);
    }

    public function orderShippings(){
        return $this->hasMany(OrderVendorShippingDB::class);
    }

    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'name' => $this->name,
    //         'company' => $this->company,
    //         'vat_number' => $this->vat_number,
    //         'contact' => $this->contact,
    //         // 'summary' => strip_tags($this->summary),
    //         // 'description' => strip_tags($this->description),
    //     ];
    // }

    //前台API用
    public function curations(){
        $this->langs = ['en','jp','kr','th'];
        $this->lang = request()->lang;
        $now = date('Y-m-d H:i:s');
        $curations = $this->hasMany(CurationDB::class)->with('products')
            ->where([['is_on',1],['category','vendor']])->where(function ($query) use ($now) {
                $query->where([['start_time','<=',$now],['end_time','>=',$now]])
                    ->orWhere([['start_time','<=',$now],['end_time',null]])
                    ->orWhere([['start_time',null],['end_time',null]])
                    ->orWhere([['start_time',null],['end_time','>=',$now]]);
            })->select([
                'id',
                'vendor_id',
                'main_title',
                'show_main_title',
                'sub_title',
                'show_sub_title',
            ]);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $curations = $curations->addSelect([
                DB::raw("(CASE WHEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT main_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.main_title END) as main_title"),
                DB::raw("(CASE WHEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) != '' THEN (SELECT sub_title from curation_langs where curation_langs.curation_id = curations.id and curation_langs.lang = 'en' limit 1) ELSE curations.sub_title END) as sub_title"),
            ]);
        }
        return $curations;
    }
    //前台API用
    public function productsData(){
        $host = env('AWS_FILE_URL');
        $langs = ['en','jp','kr','th'];
        foreach (request()->all() as $key => $value) {
            $this->{$key} = $$key = $value;
        }
        $products = $this->hasMany(ProductDB::class)
        ->join('product_models','product_models.product_id','products.id')
        ->whereIn('products.status',[1,-3])
        ->select([
            'products.id',
            'products.vendor_id',
            'products.name',
            'products.fake_price',
            'products.price',
            'products.pass_time',
            'products.model_type',
            'products.status',
            'products.curation_text_top',
            'products.curation_text_bottom',
            'hotest' => HotProductDB::whereColumn('hot_products.product_id','products.id')->select([
                DB::raw("(CASE WHEN hot_products.vendor_id = 482 THEN FLOOR( 444 + RAND() * 2345) ELSE hot_products.hits END) as hotest")
            ])->limit(1),
            DB::raw('(CASE WHEN products.model_type = 1 and product_models.quantity <= 0 THEN 1 ELSE 0 END) as outOffStock'),
            'image' => ProductImageDB::whereColumn('products.id', 'product_images.product_id')->where('product_images.is_on',1)
            ->select(DB::raw("(CASE WHEN filename != '' THEN (CONCAT('$host',filename)) END) as image"))->orderBy('sort','asc')->limit(1),
        ]);
        if(!empty($this->userId)){
            $products = $products->addSelect([
                'is_favorite' => UserFavoriteProductDB::whereColumn('products.id', 'user_favorite_products.product_id')->where('user_favorite_products.user_id',$this->userId)->select([
                    DB::raw("(CASE WHEN count(id) > 0 THEN 1 ELSE 0 END)")
                ])->limit(1),
            ]);
        }
        if(!empty($lang) && in_array($lang,$langs)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = '$lang' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = '$lang' limit 1) WHEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) ELSE products.name END) as name"),
                DB::raw("(CASE WHEN (SELECT curation_text_top from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation_text_top from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT curation_text_top from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT curation_text_top from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) ELSE products.curation_text_top END) as curation_text_top"),
                DB::raw("(CASE WHEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT curation_text_bottom from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) ELSE products.curation_text_bottom END) as curation_text_bottom"),
            ]);
        }
        $products = $products->orderBy('hotest','desc');
        return $products;
    }
}
