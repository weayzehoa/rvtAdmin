<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity; //資料表記錄功能
use Illuminate\Database\Eloquent\SoftDeletes; //使用軟刪除
// use Laravel\Scout\Searchable; //搜尋功能
use Tymon\JWTAuth\Contracts\JWTSubject; //JWT用, 加入 JWTSubject implements 及 官方提供的兩個 function

use App\Models\UserPoint as UserPointDB;
use App\Models\UserAddress as UserAddressDB;
use App\Models\UserFavoriteProduct as UserFavoriteProductDB;
use App\Models\UserFavoriteVendor as UserFavoriteVendorDB;
use App\Models\SmsLog as SmsLogDB;
use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\Country as CountryDB;
use App\Models\ShoppingCart as ShoppingCartDB;
use App\Models\ProductImage as ProductImageDB;
use App\Models\ProductLang as ProductLangDB;
use DB;
use App\Traits\LanguagePack;

class User extends Authenticatable implements JWTSubject
{
    use LanguagePack;
    use HasFactory;
    use Notifiable;
    // use Searchable;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    use LogsActivity;
    protected static $logName = '使用者管理';
    protected static $logAttributes = ['*']; //代表全部欄位
    protected static $logAttributesToIgnore = ['updated_at']; //忽略特定欄位
    // protected static $logAttributes = ['updated_at']; 只紀錄特定欄位
    protected static $logOnlyDirty = true; //只記錄有改變的欄位
    protected static $submitEmptyLogs = false; //無異動資料則不增加空資料,若沒設定 $ogOnlyDirty = true 時使用

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', //資料移轉後須將此行移除
        'nation',
        'mobile',
        'email',
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
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        // 'email_verified_at' => 'datetime',
    ];

    /*
    * 覆蓋Laravel中預設的getAuthPassword方法, 返回使用者的password和salt欄位
    * @return array
    */
    public function getAuthPassword()
    {
        return ['password' => $this->attributes['password'], 'salt' => ''];
    }

    // Rest omitted for brevity
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    // public function toSearchableArray()
    // {
    //     return [
    //         'id' => $this->id,
    //         'name' => $this->name,
    //         'email' => $this->email,
    //         'mobile' => $this->mobile,
    //         'address' => $this->address,
    //         'refer_id' => $this->refer_id,
    //         'refer_code' => $this->refer_code,
    //     ];
    // }

    //後台使用
    public function pointLogs(){
        return $this->hasMany(UserPointDB::class);
    }

    public function smsLogs(){
        return $this->hasMany(SmsLogDB::class);
    }

    public function orders(){
        return $this->hasMany(OrderDB::class);
    }

    public function address(){
        return $this->hasMany(UserAddressDB::class);
    }

    public function shoppingCarts(){
        return $this->hasMany(ShoppingCartDB::class);
    }

    //以下前台使用
    public function favoriteProducts(){
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $products = $this->hasMany(UserFavoriteProductDB::class)
            ->join('products','products.id','user_favorite_products.product_id')
            ->join('vendors','vendors.id','products.vendor_id')
            ->where('vendors.is_on',1)
            ->whereIn('products.status',[1,-3])
            ->select([
                'user_favorite_products.user_id',
                'products.id as product_id',
                'vendors.id as vendor_id',
                'products.name',
                'vendors.name as vendor_name',
                'products.fake_price',
                'products.price',
                'image' => ProductImageDB::whereColumn('products.id', 'product_images.product_id')->where('product_images.is_on',1)
                ->select(DB::raw("(CASE WHEN filename is not null THEN (CONCAT('$this->awsFileUrl',filename)) END) as image"))->orderBy('sort','asc')->limit(1),
            ]);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) ELSE vendors.name END) as vendor_name"),
                DB::raw("(CASE WHEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) != '' THEN (SELECT name from product_langs where product_langs.product_id = products.id and product_langs.lang = 'en' limit 1) ELSE products.name END) as name"),
            ]);
        }
        return $products;
    }

    public function favoriteVendors(){
        $this->langs = ['en','jp','kr','th'];
        $this->awsFileUrl = env('AWS_FILE_URL');
        $this->lang = request()->lang;
        $vendors = $this->hasMany(UserFavoriteVendorDB::class)
            ->join('vendors','vendors.id','user_favorite_vendors.vendor_id')
            ->where('vendors.is_on',1)
            ->select([
                'user_favorite_vendors.user_id',
                'vendors.id as vendor_id',
                'vendors.name',
                DB::raw("(CASE WHEN img_logo is not null THEN CONCAT('$this->awsFileUrl',img_logo) END) as img_logo"),
                DB::raw("(CASE WHEN img_cover is not null THEN CONCAT('$this->awsFileUrl',img_cover) END) as img_cover"),
            ]);
        if(!empty($this->lang) && in_array($this->lang,$this->langs)){
            $products = $products->addSelect([
                DB::raw("(CASE WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = '{$this->lang}' limit 1) WHEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) != '' THEN (SELECT name from vendor_langs where vendor_langs.vendor_id = vendors.id and vendor_langs.lang = 'en' limit 1) ELSE vendors.name END) as name"),
            ]);
        }
        return $vendors;
    }

    public function userAddress(){
        return $this->hasMany(UserAddressDB::class)
        ->select([
            'id',
            'user_id',
            'name',
            'nation',
            'phone',
            'province',
            'country',
            'area',
            'city',
            'address',
            'china_id_img1', //資料內含完整網址
            'china_id_img2', //資料內含完整網址
            'is_default',
        ])->orderBy('country','asc')->orderBy('is_default','desc')->orderBy('id','desc');
    }

    public function userOrders(){
        $lang = request()->lang;
        $langs = ['en','jp','kr','th'];
        $this->translate = $this->translate(['免自提','超商代碼','ATM轉帳','尚未付款','信用卡','待出貨','集貨中','已出貨','已完成','已取消']);
        //字串取代
        $row = 'pay_method'; //欄位名稱
        $replaceByLike = ''; //使用like方式
        $replaceByReplace = 'pay_method'; //使用replace方式
        $findStr = ['智付通','國際','玉山','台新','資策會','CVS','ATM','信用卡']; //找出字串
        $replaceStr = ['','','','',$this->translate['免自提'],$this->translate['超商代碼'],$this->translate['ATM轉帳'],$this->translate['信用卡']]; //要取代的字串
        for($i=0;$i<count($findStr);$i++){
            $replaceByLike .= " WHEN $row like '%".$findStr[$i]."%' THEN REPLACE($row,'".$findStr[$i]."','".$replaceStr[$i]."') ";
            $replaceByReplace = "REPLACE(".$replaceByReplace.",'".$findStr[$i]."','".$replaceStr[$i]."')";
        }
        $row = 'status';
        $replaceStatus = '';
        $status = [0 => $this->translate['尚未付款'], 1 => $this->translate['待出貨'], 2 => $this->translate['集貨中'], 3 => $this->translate['已出貨'], 4 => $this->translate['已完成'], -1 => $this->translate['已取消']];
        foreach ($status as $key => $value) {
            $replaceStatus .= " WHEN $row = $key THEN '$value' ";
        }
        $orders = $this->hasMany(OrderDB::class)->with('itemsImage');
        $orders = $orders->where('created_at','>=','2020-01-01 00:00:00.000'); //限制訂單2020-01-01之後
        $orders = $orders->select([
                'id',
                'user_id',
                'order_number',
                DB::raw("(CASE WHEN NOW() >= DATE_ADD(created_at, INTERVAL 6 HOUR) THEN 0
                ELSE 1 END) as in_six_hour"),
                DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d')) as create_date"),
                DB::raw("(amount - spend_point - discount + shipping_fee + parcel_tax) as price"),
                DB::raw("($replaceByReplace) as pay_method"),
                DB::raw("(CASE $replaceStatus END) as order_status"),
                'status',
                'to as to_country_id',
                'ship_to',
                'receiver_name',
                'receiver_address',
                DB::raw("(SELECT count(id) from order_items where orders.id = order_items.order_id) as totalItems ")
            ])->orderBy('created_at','desc')->limit(100);

        if(!empty($lang) && in_array($lang,$langs)){
            $orders = $orders->addSelect([
                DB::raw("(CASE WHEN (SELECT name_$lang from countries where countries.id = orders.to limit 1) is not null THEN (SELECT name_$lang from countries where countries.id = orders.to limit 1) ELSE (CASE WHEN (SELECT name_en from countries where countries.id = orders.to limit 1) is not null THEN (SELECT name_en from countries where countries.id = orders.to limit 1) ELSE (SELECT name from countries where countries.id = orders.to limit 1) END) END) as ship_to"),
            ]);
        }

        return $orders;
    }

    public function pointsHistory(){
        return $this->hasMany(UserPointDB::class)
            ->where('is_dead',0)
            ->select([
                'user_id',
                'points',
                'point_type',
                DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d')) as create_time"),
            ])->orderBy('created_at','desc');
    }
}
