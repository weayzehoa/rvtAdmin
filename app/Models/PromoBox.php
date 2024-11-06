<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class PromoBox extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '優惠活動設定';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'title',
        'teaser',
        'content',
        'title_en',
        'teaser_en',
        'content_en',
        'title_jp',
        'teaser_jp',
        'content_jp',
        'title_kr',
        'teaser_kr',
        'content_kr',
        'title_th',
        'teaser_th',
        'content_th',
        'image',
        'is_on',
        'start_time',
        'end_time',
    ];
}
















