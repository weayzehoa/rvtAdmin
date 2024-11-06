<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryProductQuantityRecord as ProductQuantityRecordDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryVendorAccount as VendorAccountDB;
use App\Models\GateAdmin as AdminDB;
use App\Models\ACErpProduct as ACErpProductDB;
use App\Models\ErpProduct as ErpProductDB;

class iCarryProductModel extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'product_model';
    //不使用時間戳記
    public $timestamps = FALSE;
    protected $fillable = [
        'product_id',
        'name',
        'name_en',
        'name_jp',
        'name_kr',
        'name_th',
        'quantity',
        'safe_quantity',
        'stock',
        'gtin13',
        'digiwin_no',
        'sku',
        'is_del',
        'origin_digiwin_no',
        'vendor_product_model_id'
    ];

    public function product(){
        return $this->belongsTo(ProductDB::class, 'product_id', 'id');
    }
    public function erpProduct()
    {
        return $this->hasOne(ErpProductDB::class,'MB010','digiwin_no');
    }
    public function acErpProduct()
    {
        return $this->hasOne(ACErpProductDB::class,'MB010','digiwin_no');
    }
    public function qtyRecords(){
        $adminTable = env('DB_ERPGATE').'.'.(new AdminDB)->getTable();
        $vendorAccountTable = env('DB_ICARRY').'.'.(new VendorAccountDB)->getTable();
        $productQuantityRecordTable = env('DB_ICARRY').'.'.(new ProductQuantityRecordDB)->getTable();
        return $this->hasMany(ProductQuantityRecordDB::class,'product_model_id','id')
        ->select([
            $productQuantityRecordTable.'.*',
            'admin' => AdminDB::whereColumn($adminTable.'.id',$productQuantityRecordTable.'.admin_id')->select($adminTable.'.name')->limit(1),
            'vendor' => VendorAccountDB::whereColumn($vendorAccountTable.'.id',$productQuantityRecordTable.'.vendor_id')->select($vendorAccountTable.'.name')->limit(1),
        ])->orderBy($productQuantityRecordTable.'.create_time','desc');
    }
}
