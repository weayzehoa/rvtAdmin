<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigiwinImportOrder extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_number',
        'colA',
        'colB',
        'colC',
        'colD',
        'colE',
        'colF',
        'colG',
        'colH',
        'colI',
        'colJ',
        'colK',
        'colL',
        'colM',
        'colN',
        'colO',
        'colP',
        'colQ',
        'colR',
        'created_at',
    ];
}
