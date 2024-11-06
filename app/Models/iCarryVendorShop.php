<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorAccount as VendorAccountDB;

class iCarryVendorShop extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'vendor_shop';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    protected $fillable = [
        'name',
        'vendor_id',
        'address',
        'tel',
        'location',
        'is_on',
    ];

    public function vendor(){
        return $this->belongsTo(VendorDB::class,'vendor_id','id');
    }

    public function accounts(){
        return $this->hasMany(VendorAccountDB::class,'vendor_id','id');
    }
}
