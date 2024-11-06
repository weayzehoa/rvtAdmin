<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use App\Models\User as UserDB;

class ReferCode extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '推薦註冊碼設定';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'code',
        'point',
        'point_type',
        'status',
        'total_register',
        'start_time',
        'end_time',
        'memo',
    ];

    public function users(){
        return $this->hasMany(UserDB::class,'refer_code','code');
    }
}
