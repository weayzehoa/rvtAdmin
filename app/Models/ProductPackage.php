<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Product as ProductDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\ProductPackageList as ProductPackageListDB;
//使用軟刪除
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductPackage extends Model
{
    use HasFactory;
    //使用軟刪除
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '組合商品';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用
    protected $hidden = ['created_at','updated_at','deleted_at']; //隱藏欄位
    protected $fillable = [
        'product_id',
        'product_model_id',
    ];

    //對應product的id欄位
    public function product(){
        return $this->belongsTo(ProductDB::class,'product_id','id');
    }

    //對應product_models的id欄位
    public function model(){
        return $this->belongsTo(ProductModelDB::class,'product_model_id','id');
    }

    //對應product_package_lists的id欄位
    public function lists(){
        return $this->hasMany(ProductPackageListDB::class)
        ->join('product_models','product_models.id','product_package_lists.product_model_id')
        ->join('products','products.id','product_models.product_id')
        ->select([
            'product_package_lists.*',
            'product_models.sku',
            'products.name',
        ]);
    }

}
