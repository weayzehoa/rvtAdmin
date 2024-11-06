<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorShop as VendorShopDB;

class iCarryVendorAccount extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'vendor_account';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $fillable = [
        'id',
        'name',
        'email',
        'vendor_id',
        'shop_id',
        'account',
        'password',
        'pwd',
        'icarry_token',
        'shop_admin',
        'pos_admin',
        'is_on',
        'lock_on',
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

    public function vendor(){
        return $this->belongsTo(VendorDB::class,'vendor_id','id');
    }

    public function shop(){
        return $this->belongsTo(VendorShopDB::class, 'shop_id', 'id');
    }

    /**
     * 覆蓋Laravel中預設的getAuthPassword方法, 返回使用者的password和salt欄位
     * @return array
     */
    public function getAuthPassword()
    {
        return ['password' => $this->attributes['password'], 'salt' => ''];
    }
}
