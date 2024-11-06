<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
// use Laravel\Scout\Searchable;

use App\Models\User as UserDB;

class UserPoint extends Model
{
    use HasFactory;
    // use Searchable;
    use LogsActivity;
    protected static $logName = '購物金紀錄';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'user_id',
        'from_user_id',
        'point_type',
        'points',
        'balance',
        'dead_time',
        'is_dead',
    ];

    public function user(){
        return $this->belongsTo(UserDB::class);
    }

    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'user_id' => $this->user_id,
    //         'point_type' => $this->point_type,
    //     ];
    // }
}
