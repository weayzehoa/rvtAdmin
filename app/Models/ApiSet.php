<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiSet extends Model
{
    use HasFactory;
    protected $fillable = [
        'partner_id',
        'key1',
        'key2',
        'name',
        'is_test',
    ];
}
