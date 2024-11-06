<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'old_erp_id',
        'name',
        'name_en',
        'value',
        'type',
        'image',
        'is_on',
        'sort',
    ];
}
