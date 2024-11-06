<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Product as ProductDB;
use App\Models\ProductPackage as ProductPackageDB;
use App\Models\ProductQuantityRecord as ProductQuantityRecordDB;
use App\Models\ShoppingCart as ShoppingCartDB;
use App\Models\OrderItem as OrderItemDB;

//使用軟刪除
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductModel extends Model
{
    use HasFactory;
    //使用軟刪除
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '產品款式設定';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'id', //資料移轉後須將此行移除
        'product_id',
        'name',
        'name_en',
        'name_jp',
        'name_kr',
        'name_th',
        'quantity',
        'safe_quantity',
        'gtin13',
        'digiwin_no',
        'origin_digiwin_no',
        'sku',
        'is_del',
    ];

    public function product(){
        return $this->belongsTo(ProductDB::class,'product_id','id')->withTrashed();
    }

    public function packages(){
        return $this->hasMany(ProductPackageDB::class);
    }

    public function orderItems(){
        return $this->hasMany(OrderItemDB::class,'id','product_model_id');
    }

    public function qtyRecords(){
        return $this->hasMany(ProductQuantityRecordDB::class);
    }

    public function shoppingCarts(){
        return $this->hasMany(ShoppingCartDB::class);
    }
}
