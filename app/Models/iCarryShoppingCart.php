<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class iCarryShoppingCart extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'shopping_cart';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'user_id',
        'session',
        'product_model_id',
        'quantity',
        'vendor_id',
        'shipping_method',
        'quantity',
        'domain',
        'country',
        'take_time',
    ];
}
