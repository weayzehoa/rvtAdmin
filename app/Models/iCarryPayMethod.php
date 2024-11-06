<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryPayMethod extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'pay_method';
    protected $fillable = [
        'name',
        'name_en',
        'value',
        'type',
        'image',
        'is_on',
        'sort',
    ];
}
