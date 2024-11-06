<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User as UserDB;
use App\Models\Vendor as VendorDB;
use App\Models\Country as CountryDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\ShippingMethod as ShippingMethodDB;


class ShoppingCart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session',
        'domain',
        'product_model_id',
        'quantity',
    ];
    protected $hidden = ['created_at','updated_at']; //隱藏欄位
    public function user(){
        return $this->belongsTo(UserDB::class);
    }

    public function vendor(){
        return $this->belongsTo(VendorDB::class);
    }

    public function product(){
        return $this->belongsTo(ProductModelDB::class);
    }

    public function shippingMethod(){
        return $this->belongsTo(ShippingMethodDB::class);
    }

    public function country(){
        return $this->belongsTo(CountryDB::class,'to_country_id','id');
    }
}
