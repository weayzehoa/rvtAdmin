<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarrySiteSetup extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'site_setup';
    public $timestamps = FALSE;
    protected $fillable = [
        'exchange_rate',
        'exchange_rate_SGD',
        'exchange_rate_MYR',
        'exchange_rate_HKD',
        'exchange_rate_USD',
        'airport_shipping_fee',
        'airport_shipping_fee_over_free',
        'shipping_fee',
        'shipping_fee_over_free',
        'pre_order_start_date',
        'pre_order_end_date',
    ];
}
