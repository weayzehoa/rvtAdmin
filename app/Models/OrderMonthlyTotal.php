<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderMonthlyTotal extends Model
{
    use HasFactory;
    protected $fillable = [
        'yyyymm',
        'pay_orders',
        'pay_money_total',
        'ffeight_tariff_total',
        'no_pay_orders',
        'avg_orders_money',
        'registered_num',
        'no_repeat_consumption',
        'source',
    ];
}
