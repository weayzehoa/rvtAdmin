<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguagePack extends Model
{
    use HasFactory;
    protected $hidden = ['created_at','updated_at']; //隱藏欄位
    protected $fillable = [
        'key_value',
        'tw',
        'en',
        'jp',
        'kr',
        'th',
        'memo',
    ];
}
