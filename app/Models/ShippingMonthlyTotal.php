<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMonthlyTotal extends Model
{
    use HasFactory;
    protected $fillable = [
        'yyyymm',
        'source',
        'shipping_1_count',
        'shipping_1_money',
        'shipping_2_count',
        'shipping_2_money',
        'shipping_3_count',
        'shipping_3_money',
        'shipping_4_count',
        'shipping_4_money',
        'shipping_5_count',
        'shipping_5_money',
        'shipping_6_count',
        'shipping_6_money',
    ];
}
