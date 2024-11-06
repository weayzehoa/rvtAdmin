<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'name_jp',
        'name_kr',
        'name_th',
        'intro',
        'intro_en',
        'intro_jp',
        'intro_kr',
        'intro_th',
        'url',
        'start_time',
        'end_time',
        'discount_type',
        'select_products',
        'logo',
        'cover',
        'sort',
        'is_on',
    ];

}
