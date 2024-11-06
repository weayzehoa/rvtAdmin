<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order as OrderDB;
use App\Models\Vendor as VendorDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\Promotion as PromotionDB;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use Illuminate\Database\Eloquent\SoftDeletes; //使用軟刪除

class OrderItem extends Model
{
    use HasFactory;
    //使用軟刪除
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '訂單商品';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'order_id',
        'product_id',
        'product_model_id',
        'vendor_id',
        'vendor_name',
        'sku',
        'product_name',
        'unit_name',
        'price',
        'gross_weight',
        'net_weight',
        'quantity',
        'vendor_service_fee_percent',
        'shipping_verdor_percent',
        'product_service_fee_percent',
        'admin_memo',
        'promotion_id',
        'is_tax_free',
        'is_del',
        'is_call',
    ];

    public function order(){
        return $this->belongsTo(OrderDB::class,'order_id','id')->withTrashed();
    }

    public function vendor(){
        return $this->belongsTo(VendorDB::class,'vendor_id','id');
    }

    public function model(){
        return $this->belongsTo(ProductModelDB::class,'product_model_id','id')->withTrashed();
    }

    public function promotion(){
        return $this->belongsTo(PromotionDB::class,'promotion_id','id');
    }
}
