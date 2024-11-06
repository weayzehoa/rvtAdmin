<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingTogetherFromVendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'serial_number',
        'vendor_id',
        'order_ids',
        'express_way',
        'express_no',
    ];
}
