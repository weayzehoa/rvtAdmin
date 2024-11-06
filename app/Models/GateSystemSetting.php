<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\GateAdmin as AdminDB;

class GateSystemSetting extends Model
{
    use HasFactory;
    use LogsActivity;
    protected $connection = 'erpGate';
    protected $table = 'system_settings';
    protected static $logName = '系統參數設定';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'exchange_rate_RMB',
        'exchange_rate_SGD',
        'exchange_rate_MYR',
        'exchange_rate_HKD',
        'exchange_rate_USD',
        'sms_supplier',
        'email_supplier',
        'invoice_supplier',
        'customer_service_supplier',
        'payment_supplier',
        'gross_weight_rate',
        'twpay_quota',
        'mitake_points',
        'airport_shipping_fee',
        'airport_shipping_fee_over_free',
        'shipping_fee',
        'shipping_fee_over_free',
        'pre_order_start_date',
        'pre_order_end_date',
        'admin_id'
    ];

    public function admin(){
        return $this->belongsTo(AdminDB::class);
    }
}
