<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirportAddress extends Model
{
    use HasFactory;
    protected $hidden = ['created_at','updated_at']; //隱藏欄位
    protected $fillable = [
        'country_id',
        'value',
        'name',
        'name_en',
        'pickup_time_start',
        'pickup_time_end',
    ];
}
