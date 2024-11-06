<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use Illuminate\Database\Eloquent\SoftDeletes; //使用軟刪除

use App\Models\Order as OrderDB;
use App\Models\Vendor as VendorDB;

class OrderVendorShipping extends Model
{
    use HasFactory;
    //使用軟刪除
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '商家出貨物流單';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'order_id',
        'vendor_id',
        'express_way',
        'express_no',
    ];

    public function order(){
        return $this->belongsTo(OrderDB::class,'order_id','id');
    }

    public function vendor(){
        return $this->belongsTo(VendorDB::class);
    }
}
