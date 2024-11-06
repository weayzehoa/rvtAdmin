<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductQuantityRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_model_id',
        'admin_id',
        'vendor_id',
        'before_quantity',
        'after_quantity',
        'before_gtin13',
        'after_gtin13',
        'reason',
    ];
}
