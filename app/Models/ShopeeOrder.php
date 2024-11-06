<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopeeOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'ordersn',
        'order_status',
        'detail',
        'country',
        'memo',
        'update_time',
    ];

}
