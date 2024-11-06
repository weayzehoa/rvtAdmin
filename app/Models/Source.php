<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    use HasFactory;
    //不使用時間戳記
    public $timestamps = false;
    protected $fillable = [
        'source',
        'name',
    ];
}
