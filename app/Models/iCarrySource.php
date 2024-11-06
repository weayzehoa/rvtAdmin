<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarrySource extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'sources';
    //不使用時間戳記
    public $timestamps = false;
    protected $fillable = [
        'source',
        'name',
    ];
}
