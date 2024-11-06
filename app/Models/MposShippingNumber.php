<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MposShippingNumber extends Model
{
    use HasFactory;
    protected $fillable = [
        'number',
        'check_code',
        'is_use',
        'use_time',
    ];
}
