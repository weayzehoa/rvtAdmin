<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use Illuminate\Database\Eloquent\SoftDeletes; //使用軟刪除
// use Laravel\Scout\Searchable; //搜尋功能
use Tymon\JWTAuth\Contracts\JWTSubject; //JWT用, 加入 JWTSubject implements 及 官方提供的兩個 function

use App\Models\GateAdminPwdUpdateLog as PwdUpdateLogDB;
class GateAdmin extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;
    use LogsActivity;
    // use Searchable;
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    // const CREATED_AT = 'create_time';
    // const UPDATED_AT = 'update_time';

    //指定 table 名稱
    protected $connection = 'erpGate';
    protected $table = 'admins';

    protected static $logName = '管理員帳號'; // //log_name 欄位資料
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    //使用軟刪除
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account',
        'name',
        'email',
        'password',
        'is_on',
        'power',
        'mainmenu_power',
        'submenu_power',
        'power_action',
        'lock_on',
        'mobile',
        'otp',
        'otp_time',
        'off_time',
        'sms_vendor',
        'google2fa_secret',
        'verify_mode',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    // Rest omitted for brevity

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'name' => $this->name,
    //         'email' => $this->email,
    //         'account' => $this->account,
    //     ];
    // }

    public function passChange()
    {
        return $this->hasOne(PwdUpdateLogDB::class,'admin_id','id')->orderBy('created_at','desc');
    }
}