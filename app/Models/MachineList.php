<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use App\Models\Vendor as VendorDB;
use App\Models\VendorAccount as VendorAccountDB;
use App\Models\MposRecord as MposRecordDB;

class MachineList extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = 'ACPay機台列表';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'id', //資料移轉後須將此行移除
        'vendor_id',
        'vendor_account_id',
        'name',
        'contact_person',
        'tel',
        'fax',
        'email',
        'address',
        'airport_shipping',
        'hotel_shipping',
        'yourself_shipping',
        'overseas_shipping',
        'taiwan_shipping',
        'airport_box',
        'hotel_box',
        'yourself_box',
        'overseas_box',
        'taiwan_box',
        'airport_base',
        'hotel_base',
        'yourself_base',
        'overseas_base',
        'taiwan_base',
        'card_paying',
        'alipay_paying',
        'card_draw',
        'alipay_draw',
        'free_shipping',
        'bank',
        'is_on',
        'can_return',
        'can_cancel',
    ];

    public function vendor(){
        return $this->belongsTo(VendorDB::class);
    }

    public function account(){
        return $this->belongsTo(VendorAccountDB::class,'vendor_id','vendor_id');
    }

    public function records(){
        return $this->hasMany(MposRecordDB::class);
    }
}
