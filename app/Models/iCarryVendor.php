<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryVendorShop as VendorShopDB;
use App\Models\iCarryVendorAccount as VendorAccountDB;

class iCarryVendor extends Model
{
    use HasFactory;
    protected $connection = 'icarry';
    protected $table = 'vendor';
    //變更 Laravel 預設 created_at 與 updated_at 欄位名稱
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    public function products(){
        return $this->hasMany(ProductDB::class,'vendor_id','id')->where('is_del',0)->orderBy('id','desc');
    }

    protected $fillable = [
        'name','company','VAT_number','boss','contact_person',
        'tel','fax','email','categories','address','shipping_setup',
        'shipping_verdor_percent','is_on','summary','description',
        'shopping_notice','service_fee','digiwin_vendor_no',
        'cover','img_cover','img_logo','img_site','shipping_self',
        'factory_address','product_sold_country','curation','notify_email','bill_email',
        'pause_start_date', 'pause_end_date','use_sf','new_cover','new_logo','new_site_cover'
    ];

    public function shops(){
        return $this->hasMany(VendorShopDB::class,'vendor_id','id');
    }

    public function accounts(){
        return $this->hasMany(VendorAccountDB::class,'vendor_id','id');
    }
}
