<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDailyTotal extends Model
{
    use HasFactory;

    protected $fillable = [
        'yyyymm',
        'yyyymmdd',
        'total_order',
        'total_money',
        'total_shipping_tax',
        'not_ok_total',
        'avg',
        'user_total',
        'distinct_buyer_total',
        'source',
    ];
}

