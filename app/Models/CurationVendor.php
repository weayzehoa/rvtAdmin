<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\Curation as CurationDB;
use App\Models\Vendor as VendorDB;
use App\Models\VendorLang as VendorLangDB;
use DB;

class CurationVendor extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '首頁策展-品牌類';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'curation_id',
        'vendor_id',
        'sort',
    ];

    public function curation(){
        return $this->belongsTo(CurationDB::class);
    }
    public function langs()
    {
        return $this->hasMany(VendorLangDB::class,'vendor_id','vendor_id')
                ->select([
                    'vendor_id',
                    'lang',
                    'name',
                    'curation',
                ]);
    }
    public function data(){
        $host = env('AWS_FILE_URL');
        return $this->belongsTo(VendorDB::class,'vendor_id','id')->where('is_on',1)
                ->select([
                    'id',
                    'name',
                    DB::raw("CONCAT('$host',img_logo) as img_logo"),
                    DB::raw("CONCAT('$host',img_cover) as img_cover"),
                    'curation',
                    'is_on',
                ]);
    }
}
