<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class iCarryShortUrl extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'short_url';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;

    protected $fillable = [
        'code',
        'url',
        'memo',
        'clicks',
    ];
}
