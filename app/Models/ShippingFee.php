<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use Illuminate\Database\Eloquent\SoftDeletes; //使用軟刪除

use App\Models\Country as CountryDB;
use App\Models\ShippingLocal as ShippingLocalDB;

class ShippingFee extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '物流運費設定';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'type',
        'from',
        'to',
        'price',
        'free_shipping',
        'tax_rate',
        'description',
        'description_en',
        'is_local',
        'shipping_local_id',
        'is_on',
    ];

    public function start(){
        return $this->belongsTo(CountryDB::class, 'from', 'id');
    }

    public function destination(){
        return $this->belongsTo(CountryDB::class, 'to', 'id');
    }

    public function local(){
        return $this->belongsTo(ShippingLocalDB::class, 'shipping_local_id', 'id');
    }
}
