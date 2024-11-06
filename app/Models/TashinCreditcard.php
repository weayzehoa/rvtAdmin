<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TashinCreditcard extends Model
{
    use HasFactory;
    protected $fillable = [
        'amount',
        'order_number',
        'amount',
        'pay_status',
        'post_json',
        'get_json',
        'created_at',
        'updated_at',
    ];
}
