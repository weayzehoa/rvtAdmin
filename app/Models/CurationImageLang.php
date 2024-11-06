<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\CurationImage as CurationImageDB;

class CurationImageLang extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '首頁策展-圖片類語言';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'curation_image_id',
        'main_title',
        'sub_title',
        'caption',
        'modal_content',
        'lang',
    ];

    public function curationImage(){
        return $this->belongsTo(CurationImageDB::class);
    }
}
