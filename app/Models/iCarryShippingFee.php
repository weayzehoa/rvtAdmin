<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryShippingFee extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'shipping_set';
    //不使用timestamps
    public $timestamps = FALSE;
    protected $fillable = [
        'product_sold_country',
        'shipping_methods',
        'shipping_methods_en',
        'free_shipping',
        'tax_rate',
        'description_tw',
        'description_en',
        'fill_vendor_earliest_delivery_date_tw',
        'fill_vendor_earliest_delivery_date_en',
        'shipping_base_price',
        'shipping_kg_price',
        'is_on',
        'shipping_type',
    ];
}
