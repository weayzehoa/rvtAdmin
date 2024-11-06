<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryUserPoint as UserPointDB;
use App\Models\GateSmsLog as SmsLogDB;
use App\Models\iCarryShoppingCart as ShoppingCartDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use DB;

class iCarryUser extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'users';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = null;
    protected $fillable = [
        'nation',
        'mobile',
        'email',
        'pwd',
        'password',
        'refer_id',
        'refer_code',
        'name',
        'status',
        'verify_code',
        'from_site',
        'from_token',
        'points',
        'smsTime',
        'address',
        'id_card',
        'asiamiles_account',
        'asiamiles_name',
        'asiamiles_last_name',
        'avatar',
        'ip',
        'mark',
        'is_mark',
        'carrier_type',
        'carrier_num',
        'remember_me',
        'memo',
    ];

    //後台使用
    public function pointLogs(){
        return $this->hasMany(UserPointDB::class,'user_id','id');
    }

    public function smsLogs(){
        return $this->hasMany(SmsLogDB::class,'user_id','id')->orderBy('created_at','desc');
    }

    public function address(){
        return $this->hasMany(UserAddressDB::class,'user_id','id');
    }

    public function shoppingCarts(){
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $shoppingCartTable = env('DB_ICARRY').'.'.(new ShoppingCartDB)->getTable();

        return $this->hasMany(ShoppingCartDB::class,'user_id','id')
        ->join($productModelTable,$productModelTable.'.id',$shoppingCartTable.'.product_model_id')
        ->join($productTable,$productTable.'.id',$productModelTable.'.product_id')
        ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
        ->select([
            $shoppingCartTable.'.*',
            $vendorTable.'.name as vendor_name',
            DB::raw("CONCAT($vendorTable.name,' ',$productTable.name,'-',$productModelTable.name) as product_name"),
            $productTable.'.unit_name',
            $productTable.'.gross_weight',
            $productTable.'.price',
            $productModelTable.'.digiwin_no',
        ]);

    }
}
