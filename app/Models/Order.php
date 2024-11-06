<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use Illuminate\Database\Eloquent\SoftDeletes; //使用軟刪除
// use Laravel\Scout\Searchable; //搜尋功能

use App\Models\User as UserDB;
use App\Models\Product as ProductDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\ProductImage as ProductImageDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\OrderLog as OrderLogDB;
use App\Models\OrderAsiamiles as OrderAsiamilesDB;
use App\Models\OrderShipping as OrderShippingDB;
use App\Models\OrderVendorShipping as OrderVendorShippingDB;
use App\Models\ShippingMethod as ShippingMethodDB;
use App\Models\ShopcomOrder as ShopcomOrderDB;
use App\Models\TradevanOrder as TradevanOrderDB;
use App\Models\Country as CountryDB;
use App\Models\Pay2go as Pay2goDB;
use App\Models\Spgateway as SpgatewayDB;
use App\Models\VendorLang as VendorLangDB;
use App\Models\ShippingFee as ShippingFeeDB;
use DB;

class Order extends Model
{
    use HasFactory;
    // use Searchable;
    //使用軟刪除
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '訂單資料';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    protected $fillable = [
        'id', //資料移轉後須將此行移除
        'order_number',
        'user_id',
        'origin_country',
        'ship_to',
        'from',
        'to',
        'book_shipping_date',
        'receiver_name',
        'receiver_id_card',
        'receiver_nation_number',
        'receiver_phone_number',
        'receiver_tel',
        'receiver_email',
        'receiver_address',
        'receiver_birthday',
        'receiver_province',
        'receiver_city',
        'receiver_area',
        'receiver_zip_code',
        'receiver_keyword',
        'receiver_key_time',
        'shipping_method',
        'invoice_time',
        'invoice_type',
        'invoice_sub_type',
        'invoice_number',
        'invoice_title',
        'invoice_address',
        'spend_point',
        'amount',
        'shipping_fee',
        'parcel_tax',
        'pay_method',
        'get_point',
        'exchange_rate',
        'shipping_number',
        'shipping_memo',
        'promotion_code',
        'discount',
        'admin_memo',
        'user_memo',
        'vendor_memo',
        'partner_order_number',
        'partner_country',
        'pay_time',
        'buyer_name',
        'buyer_email',
        'buyer_id_card',
        'carrier_type',
        'carrier_num',
        'love_code',
        'print_flag',
        'shipping_time',
        'buy_memo',
        'billOfLoading_memo',
        'special_memo',
        'new_shipping_memo',
        'star_color',
        'tax_refund',
        'domain',
        'create_type',
        'create_id',
        'is_invoice_no',
        'is_invoice_cancel',
        'invoice_memo',
        'china_id_img1',
        'china_id_img2',
        'is_del',
        'is_call',
        'is_print',
        'is_invoice',
        'status',
    ];

    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'order_number' => $this->order_number,
    //     ];
    // }

    public function user(){
        return $this->belongsTo(UserDB::class);
    }

    public function shipfrom(){
        return $this->belongsTo(CountryDB::class,'from','id');
    }

    public function shipto(){
        return $this->belongsTo(CountryDB::class,'to','id');
    }

    public function shippingMethod(){
        return $this->belongsTo(ShippingMethodDB::class,'shipping_method','id');
    }

    public function pay2gos(){
        return $this->hasMany(Pay2goDB::class,'order_number','order_number');
    }

    public function spgateway(){
        return $this->hasOne(SpgatewayDB::class,'order_number','order_number');
    }

    public function items(){
        return $this->hasMany(OrderItemDB::class)
            ->select([
                '*',
                'gtin13' => ProductModelDB::whereColumn('order_items.product_model_id','product_models.id')
                    ->select('gtin13')->limit(1),
            ])->withTrashed();
    }

    public function shippings(){
        return $this->hasMany(OrderShippingDB::class);
    }

    public function logs(){
        return $this->hasMany(OrderLogDB::class);
    }

    public function vendorShippings(){
        return $this->hasMany(OrderVendorShippingDB::class);
    }

    public function asiamiles(){
        return $this->hasOne(OrderAsiamilesDB::class);
    }

    public function shopcom(){
        return $this->hasOne(ShopcomOrderDB::class);
    }

    public function tradevan(){
        return $this->hasOne(TradevanOrderDB::class);
    }

    public function orderShippings(){
        return $this->hasMany(OrderShippingDB::class);
    }

    //前台訂單資料用
    public function orderItems()
    {
        $lang = request()->lang;
        $langs = ['en','jp','kr','th'];
        $orders = $this->hasMany(OrderItemDB::class);
        $awsFileUrl = env('AWS_FILE_URL');
        $orders = $orders->select([
            'order_id',
            'vendor_name',
            'product_name',
        ]);

        if(!empty($lang) && in_array($lang,$langs)){
            $orders = $orders->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = order_items.vendor_id and vendor_langs.lang = '$lang' limit 1) is not null THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = order_items.vendor_id and vendor_langs.lang = '$lang' limit 1) ELSE order_items.vendor_name END) as vendor_name"),
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = order_items.product_id and product_langs.lang = '$lang' limit 1) is not null THEN (SELECT name from product_langs where product_langs.product_id = order_items.product_id and product_langs.lang = '$lang' limit 1) ELSE order_items.product_name END) as product_name"),
            ]);
        }

        $orders = $orders->addSelect([
                'quantity',
                'fake_price' => ProductDB::whereColumn('products.id','order_items.product_id')->select('fake_price')->limit(1),
                'price',
                'gross_weight',
                DB::raw('quantity * price as amount_price'),
                'image' => ProductImageDB::whereColumn('product_images.product_id','order_items.product_id')->where('product_images.is_on',1)->where('product_images.is_on',1)->orderBy('sort','asc')->select([DB::raw("CONCAT('$awsFileUrl',filename)")])->limit(1),
                'airport_days' => ProductDB::whereColumn('products.id','order_items.product_id')->select('airport_days')->limit(1),
            ]);

        return $orders;
    }
    //前台使用者訂單用
    public function itemsImage()
    {
        $awsFileUrl = env('AWS_FILE_URL');
        $orders = $this->hasMany(OrderItemDB::class);
        $orders = $orders->select([
            'order_id',
            'image' => ProductImageDB::whereColumn('product_images.product_id','order_items.product_id')
            ->where('product_images.is_on',1)->orderBy('sort','asc')->select([DB::raw("CONCAT('$awsFileUrl',filename)")])->limit(1),
        ]);
        return $orders;
    }

    public function shippingInfo(){
        return $this->hasOne(ShippingFeeDB::class,'to','to','from');
    }
}
