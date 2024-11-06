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

use App\Models\Vendor as VendorDB;
use App\Models\VendorShop as VendorShopDB;

class VendorAccount extends Authenticatable implements JWTSubject
{
    use HasFactory;
    use Notifiable;
    // use Searchable;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '商家帳號管理';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'id',
        'name',
        'email',
        'vendor_id',
        'vendor_shop_id',
        'account',
        'password',
        'icarry_token',
        'shop_admin',
        'pos_admin',
        'is_on',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
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

    public function vendor(){
        return $this->belongsTo(VendorDB::class);
    }

    public function shop(){
        return $this->belongsTo(VendorShopDB::class, 'vendor_shop_id', 'id');
    }

    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'name' => $this->name,
    //         'account' => $this->account,
    //     ];
    // }
    /**
     * 覆蓋Laravel中預設的getAuthPassword方法, 返回使用者的password和salt欄位
     * @return array
     */
    public function getAuthPassword()
    {
        return ['password' => $this->attributes['password'], 'salt' => ''];
    }
}
