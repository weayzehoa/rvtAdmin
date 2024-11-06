<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
// use Laravel\Scout\Searchable;
use App\Models\Product as ProductDB;

class ProductLang extends Model
{
    use HasFactory;
    // use Searchable;
    use LogsActivity;
    protected static $logName = '產品語言資料';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'product_id',
        'name',
        'lang',
        'brand',
        'serving_size',
        'title',
        'intro',
        'model_name',
        'specification',
        'unable_buy',
        'curation_text_top',
        'curation_text_bottom',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'product_id' => $this->product_id,
    //         'name' => $this->name,
    //         'brand' => $this->brand,
    //         'title' => $this->title,
    //         'intro' => $this->intro,
    //         'model_name' => $this->model_name,
    //         'specification' => $this->specification,
    //     ];
    // }

    public function product(){
        return $this->belongsTo(ProductDB::class);
    }
}
