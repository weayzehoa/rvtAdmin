<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YiruiOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'serial_number',
        'order_number',
        'row_data',
    ];
}
