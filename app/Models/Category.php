<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能

use App\Models\Product as ProductDB;
use App\Models\ProductLang as ProductLangDB;
use App\Models\CategoryLang as CategoryLangDB;
use DB;

class Category extends Model
{
    use HasFactory;
    use LogsActivity;
    protected static $logName = '產品類別';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'id',
        'name',
        'intro',
        'logo',
        'cover',
        'sort',
        'is_on',
    ];

    public function products(){
        $this->request = request();
        $this->langs = ['en','jp','kr','th'];
        $this->rules = [
            'type' => 'nullable|string|in:vendor,product',
            'lang' =>'nullable|string|in:en,jp,kr,th',
        ];
        foreach ($this->request->all() as $key => $value) {
            if(in_array($key, array_keys($this->rules))){
                $this->{$key} = $value;
            }
        }
        $products = $this->hasMany(ProductDB::class)
        ->whereIn('status',[1,-3]);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $products = $products->join('product_langs','product_langs.product_id','products.id')
                ->select([
                    'products.id',
                    'products.category_id',
                    DB::raw("(CASE WHEN product_langs.lang = '$this->lang' and product_langs.name != '' THEN product_langs.name WHEN product_langs.lang = 'en' and product_langs.name !='' THEN product_langs.name ELSE products.name END) as name"),
                ]);
        }else{
            $products = $products->select([
                'id',
                'category_id',
                'name',
            ]);
        }
        return $products;
    }

    public function langs(){
        return $this->hasMany(CategoryLangDB::class);
    }
}
