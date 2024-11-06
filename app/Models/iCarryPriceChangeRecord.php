<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryPriceChangeRecord extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'price_change_record';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'product_id',
        'colA',
        'colB',
        'colC',
        'colD',
        'colE',
        'colF',
        'colG',
        'status_updown',
        'admin_id',
        'original_price',
        'original_fake_price',
        'original_vendor_price',
        'original_status',
        'is_disabled',
    ];
}
