<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\Curation as CurationDB;
use App\Models\CurationImageLang as CurationImageLangDB;

class CurationImage extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '首頁策展-圖片類';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'style',
        'curation_id',
        'open_method',
        'main_title',
        'show_main_title',
        'sub_title',
        'show_sub_title',
        'caption',
        'text_position',
        'row',
        'url',
        'url_open_window',
        'modal_content',
        'image',
        'sort',
    ];

    public function langs()
    {
        return $this->hasMany(CurationImageLangDB::class)
                ->select([
                    'curation_image_id',
                    'lang',
                    'main_title',
                    'sub_title',
                    'modal_content',
                ]);
    }

    public function curation(){
        return $this->belongsTo(CurationDB::class);
    }
}
