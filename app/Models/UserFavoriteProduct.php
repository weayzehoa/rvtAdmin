<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use App\Models\User as UserDB;
use App\Models\Product as ProductDB;
use App\Models\ProductImage as ProductImageDB;
use DB;

class UserFavoriteProduct extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '使用者喜愛產品';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用
    protected $hidden = ['created_at','updated_at']; //隱藏欄位
    protected $fillable = [
        'user_id',
        'product_id',
    ];

    public function user(){
        return $this->belongsTo(UserDB::class);
    }

    public function products(){
        return $this->hasMany(ProductDB::class);
    }

    public function image()
    {
        $host = env('AWS_FILE_URL');
        return $this->hasOne(ProductImageDB::class,'product_id','product_id')
                ->where('is_on',1)->orderBy('sort','asc')->select([
                    'product_id',
                    DB::raw("CONCAT('$host',filename) as filename"),
                ]);
    }
}
