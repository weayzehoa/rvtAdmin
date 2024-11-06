<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
// use Laravel\Scout\Searchable;

use App\Models\User as UserDB;

class UserAddress extends Model
{
    use HasFactory;
    // use Searchable;
    use LogsActivity;
    protected static $logName = '使用者常用地址';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用
    protected $hidden = ['created_at','updated_at']; //隱藏欄位
    protected $fillable = [
        'user_id',
        'name',
        'nation',
        'phone',
        'email',
        'address',
        'country',
        'province',
        'city',
        'area',
        'zip_code',
        'id_card',
        'china_id_img1',
        'china_id_img2',
        'is_default',
    ];

    public function user(){
        return $this->belongsTo(UserDB::class);
    }

    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'user_id' => $this->user_id,
    //         'name' => $this->name,
    //         'email' => $this->email,
    //         'mobile' => $this->mobile,
    //     ];
    // }
}
