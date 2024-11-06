<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotProduct extends Model
{
    use HasFactory;
    //不使用時間戳記
    public $timestamps = false;
    protected $fillable = [
        'product_model_id',
        'product_id',
        'vendor_id',
        'category_id',
        'hits',
        'quantity',
    ];
}
