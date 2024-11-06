<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alipay extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'order_number',
        'post_json',
        'get_json',
        'pay_status',
        'payment_number',
        'rmb_fee',
        'currency',
        'gateway',
        'wallet',
    ];
}



