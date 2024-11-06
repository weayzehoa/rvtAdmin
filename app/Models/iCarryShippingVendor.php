<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryShippingVendor extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'shipping_vendor';
    //不使用timestamps
    public $timestamps = FALSE;
    protected $fillable = [
        'name',
        'name_en',
        'api_url',
        'is_foreign',
        'sort_id',
        'is_on',
        'is_delete',
    ];
}
