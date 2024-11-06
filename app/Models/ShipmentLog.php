<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipmentLog extends Model
{
    use HasFactory;


    protected $fillable = [
        'order_id',
        'user_id',
        'shipping_method',
        'order_number',
        'send',
    ];
}
