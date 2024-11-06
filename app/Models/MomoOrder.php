<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MomoOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'colF',
        'colG',
        'colH',
        'colM',
        'colN',
        'colO',
        'colT',
        'colU',
        'all_cols',
        'created_at',
    ];
}
