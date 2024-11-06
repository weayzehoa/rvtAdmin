<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryIndexBanner extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'index_banner';
    //不使用時間戳記
    public $timestamps = false;
    protected $fillable = [
        'name',
        'img_desktop',
        'img_mobile',
        'is_on',
        'url',
        'sort_id',
        'start_time',
        'end_time',
    ];
}
