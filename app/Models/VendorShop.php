<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use Illuminate\Database\Eloquent\SoftDeletes; //使用軟刪除
// use Laravel\Scout\Searchable; //搜尋功能

use App\Models\Vendor as VendorDB;
use App\Models\VendorAccount as VendorAccountDB;

class VendorShop extends Model
{
    use HasFactory;
    // use Searchable;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '商家分店管理';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'name',
        'vendor_id',
        'address',
        'tel',
        'location',
        'is_on',
    ];

    public function vendor(){
        return $this->belongsTo(VendorDB::class);
    }

    public function accounts(){
        return $this->hasMany(VendorAccountDB::class);
    }

    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'name' => $this->name,
    //         'tel' => $this->tel,
    //         'address' => $this->address,
    //     ];
    // }
}
