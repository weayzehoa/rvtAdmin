<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUpdateRecord extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'product_id',
        'admin_id',
        'vendor_id',
        'column',
        'before_value',
        'after_value',
    ];
}
