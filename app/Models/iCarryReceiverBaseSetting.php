<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use App\Models\GateAdmin as AdminDB;

class iCarryReceiverBaseSetting extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $connection = 'icarry';
    protected $table = 'receiver_base_settings';
    protected static $logName = '提貨日設定';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用
    protected $hidden = ['admin_id','created_at','updated_at']; //隱藏欄位
    protected $fillable = [
        'select_date',
        'type',
        'is_ok',
        'memo',
        'admin_id',
    ];

    public function admin(){
        return $this->belongsTo(AdminDB::class,'admin_id','id');
    }
}
