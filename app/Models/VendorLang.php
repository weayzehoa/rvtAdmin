<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Vendor as VendorDB;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
// use Laravel\Scout\Searchable;

class VendorLang extends Model
{
    use HasFactory;
    // use Searchable;
    use LogsActivity;
    protected static $logName = '商家管理_語言資料';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'vendor_id',
        'lang',
        'name',
        'summary',
        'description',
        'curation',
    ];

    public function vendor(){
        return $this->belongsTo(VendorDB::class);
    }

    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'name' => $this->name,
    //         'summary' => strip_tags($this->summary),
    //         'description' => strip_tags($this->description),
    //     ];
    // }
}
