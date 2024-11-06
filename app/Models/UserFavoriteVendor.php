<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use App\Models\User as UserDB;
use App\Models\Vendor as VendorDB;

class UserFavoriteVendor extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '使用者喜愛品牌';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用
    protected $hidden = ['created_at','updated_at']; //隱藏欄位
    protected $fillable = [
        'user_id',
        'vendor_id',
    ];

    public function user(){
        return $this->belongsTo(UserDB::class);
    }

    public function vendors(){
        return $this->hasMany(VendorDB::class);
    }
}