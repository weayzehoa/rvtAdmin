<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\Curation as CurationDB;
use App\Models\Product as ProductDB;
use App\Models\ProductLang as ProductLangDB;
use App\Models\ProductImage as ProductImageDB;
use DB;

class CurationProduct extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '首頁策展-產品類';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'curation_id',
        'product_id',
        'sort',
    ];

    public function curation(){
        return $this->belongsTo(CurationDB::class);
    }
    public function langs()
    {
        return $this->hasMany(ProductLangDB::class,'product_id','product_id')
                ->select([
                    'product_id',
                    'lang',
                    'name',
                    'curation_text_top',
                    'curation_text_bottom',
                ]);
    }
    public function data(){
        return $this->belongsTo(ProductDB::class,'product_id','id')
                ->join('vendors','vendors.id','products.vendor_id')->where([['products.status',1],['vendors.is_on',1]])
                ->select([
                    'products.id',
                    'products.name',
                    'products.curation_text_top',
                    'products.curation_text_bottom',
                    'products.fake_price',
                    'products.price',
                    'products.status',
                    'vendors.name as vendor_name',
                ]);
    }

    public function image()
    {
        $host = env('AWS_FILE_URL');
        return $this->hasOne(ProductImageDB::class,'product_id','product_id')
                ->where('is_on',1)->orderBy('sort','asc')->select([
                    'product_id',
                    DB::raw("CONCAT('$host',filename) as filename"),
                ]);
    }

    public function curationImage()
    {
        return $this->hasOne(ProductImageDB::class,'product_id','product_id')
                ->where('is_on',1)->orderBy('sort','asc');
    }
}
