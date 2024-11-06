<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarrySubCategory as SubCategoryDB;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProductLang as ProductLangDB;
use App\Models\iCarryProductQuantityRecord as ProductQuantityRecordDB;
use App\Models\iCarryProductUpdateRecord as ProductUpdateRecordDB;

use App\Models\iCarryProductPackage as ProductPackageDB;
use App\Models\iCarryProductPackageList as ProductPackageListDB;
use App\Models\iCarryProductImage as ProductImageDB;
use App\Models\iCarryProductUnitName as ProductUnitNameDB;
use App\Models\iCarryShippingMethod as ShippingMethodDB;
use App\Models\iCarryShippingFee as ShippingFeeDB;
use App\Models\GateAdmin as AdminDB;
use App\Models\iCarryVendorAccount as VendorAccountDB;

use App\Models\iCarryLangProductEn as ProductEnDB;
use App\Models\iCarryLangProductJp as ProductJpDB;
use App\Models\iCarryLangProductKr as ProductKrDB;
use App\Models\iCarryLangProductTh as ProductThDB;
use App\Models\iCarryDigiwinProductCate as DigiwinProductCateDB;

use App\Http\Requests\Admin\ProductsRequest;
use App\Http\Requests\Admin\ProductsLangRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use Http;
use Validator;
use App\Traits\ProductFunctionTrait;
use App\Jobs\ChangePriceFileImportJob;
use App\Jobs\ChangeStatusFileImportJob;
use App\Jobs\ProductAutoUpdateToErpJob;

class ProductsController extends Controller
{
    use ProductFunctionTrait;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 先經過 middleware 檢查
        $this->middleware('auth:admin');
        $this->langRules = [
            'product_id' => 'required',
            'name' => 'required|max:64',
            'lang' => 'required|max:5',
            'brand' => 'required|max:64',
            'serving_size' => 'required|max:255',
            'title' => 'required|max:64',
            'intro' => 'required|max:500',
            'model_name' => 'nullable|max:32',
            'specification' => 'required|max:5000',
            'unable_buy' => 'nullable|max:100',
        ];
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menuCode = 'M4S1';
        $appends = [];
        $compact = [];
        $vendorId = '';
        $categories = CategoryDB::orderBy('is_on','desc')->get();
        $vendors = VendorDB::orderBy('is_on','desc')->orderBy('id','desc')->get();
        $products = $this->getProductData(request(),'index');

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }
        foreach($products as $product) {
            $product->service_fee_percent = 0;
            !empty($product->vendor_service_fee) ? $vendorServiceFee = $this->serviceFee($product->vendor_service_fee) : $vendorServiceFee = null;
            if(!empty($vendorServiceFee)){
                for($i=0;count($vendorServiceFee);$i++){
                    if($vendorServiceFee[$i]->name == 'iCarry'){
                        $product->service_fee_percent = $vendorServiceFee[$i]->percent;
                        break;
                    }
                }
            }
            $product->categories = CategoryDB::whereIn('id',explode(',',$product->category_id))->where('is_on',1)
            ->select(DB::raw("GROUP_CONCAT(name) as name"))->first()->name;
        }
        $compact = array_merge($compact, ['menuCode','products','appends','vendorId','categories','vendors']);
        return view('admin.products.index',compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (request()->from_vendor) {
            //如果是從商家管理點新增商品過來,則產生返回連結
            $backUrl = url('vendors/'.(INT)request()->from_vendor.'#vendor-product');
            Session::put('backUrl', $backUrl);
            $vendor = VendorDB::findOrFail((INT)request()->from_vendor);
        }elseif(request()->vendor_id){
            $vendor = VendorDB::findOrFail((INT)request()->vendor_id);
        }else{
            return redirect()->back();
        }
        $menuCode = 'M4S1';
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        $shippingMethods = ShippingMethodDB::all();
        $categories = CategoryDB::whereIn('id',explode(',',$vendor->categories))->where('is_on',1)->get();
        $digiwinProductCates = DigiwinProductCateDB::all();
        $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
        $countries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->distinct($countryTable.'.name')->get();
        $unitNames = ProductUnitNameDB::all();
        return view('admin.products.show',compact('vendor','menuCode','langs','shippingMethods','categories','unitNames','countries','digiwinProductCates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductsRequest $request)
    {
        $data = $request->all();
        isset($data['category_id']) ? $data['category_id'] = join(',',$data['category_id']) : $data['category_id'] = null;
        isset($data['sub_categories']) ? $data['sub_categories'] = join(',',$data['sub_categories']) : $data['sub_categories'] = null;
        isset($data['name']) ? $data['name'] = str_replace('_','-',$data['name']) : '';
        $modelType = $data['model_type'];
        $vendorId = $data['vendor_id'];
        $message = null;
        if ($modelType == 2) {
            if(!isset($data['model_data'])){
                $message = '您是不是忘記填寫款式資料??';
            }
        }elseif ($modelType == 3) {
            if(!isset($data['packageData'])){
                $message = '您是不是忘記建立組合品資料??';
            }else{
                $chkData = 0;
                for($i=0;$i<count($data['packageData']);$i++){
                    if(isset($data['packageData'][$i]['list']) && count($data['packageData'][$i]['list']) > 0){
                        $chkData++;
                    }
                }
                if($chkData == 0){
                    $message = '您是不是忘記填寫組合品內的商品資料??';
                }
            }
        }
        if(!empty($message)){
            Session::put('error', $message);
            return redirect()->back()->withInput($request->all());
        }
        $data['TMS_price'] ?? $data['TMS_price'] = 0;
        $data['fake_price'] ?? $data['fake_price'] = 0;
        $data['vendor_price'] ?? $data['vendor_price'] = 0;
        $data['airplane_days'] ?? $data['airplane_days'] = 0;
        $data['hotel_days'] ?? $data['hotel_days'] = 0;
        $data['is_hot'] ?? $data['is_hot'] = 0;
        $data['storage_life'] ?? $data['storage_life'] = 0;
        isset($data['shipping_methods']) ? $data['shipping_methods'] = join(',',$data['shipping_methods']) : '';
        if(isset($data['allow_country_ids'])){
            $data['allow_country'] = join(',',CountryDB::whereIn('id',$data['allow_country_ids'])->get()->pluck('name')->all());
            $data['allow_country_ids'] = join(',',$data['allow_country_ids']);
        }else{
            $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
            $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
            $allCountries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->distinct($countryTable.'.name')->get()->pluck('name')->all();
            $data['allow_country'] = join(',',$allCountries);
            $data['allow_country_ids'] = join(',',CountryDB::whereIn('name',$allCountries)->get()->pluck('id')->all());
        }
        if(isset($data['from_country_id'])){
            $tmp = CountryDB::find($data['from_country_id']);
            !empty($tmp) ? $data['product_sold_country'] = $tmp->name : $data['product_sold_country'] = '台灣';
        }else{
            $data['from_country_id'] = 1;
            $data['product_sold_country'] = '台灣'; //強制設定為台灣
        }
        if(isset($data['unit_name_id'])){
            $tmp = ProductUnitNameDB::find($data['unit_name_id']);
            !empty($tmp) ? $data['unit_name'] = $tmp->name : $data['unit_name'] = '個';
        }else{
            $data['unit_name_id'] = 1;
            $data['unit_name'] = '個'; //強制設定為個
        }
        if($data['type'] == 3){
            $data['price'] = 0;
            $data['status'] = -9;
        }else{ //檢查金額不可為0
            if($data['price'] <= 0){
                Session::put('error','注意!! 商品類型非贈品時，金額不可為 0 !!');
                return redirect()->back();
            }
        }
        //最快出貨日大於最晚出貨日 清空最晚出貨日
        if(!empty($data['vendor_earliest_delivery_date'])) {
            strtotime($data['vendor_earliest_delivery_date']) > strtotime($data['vendor_latest_delivery_date']) ? $data['vendor_latest_delivery_date'] = null : '';
        }
        //送審通過
        $data['status'] == -9 || $data['status'] == 1 || $data['status'] == -3 ? $data['pass_time'] = date('Y-m-d H:i:s') : '';
        if(is_numeric($modelType) && $modelType >= 1 || $modelType <= 3 ){
            $chk = 0;
            if($data['model_type'] == 1){
                $chk++;
            }elseif($data['model_type'] == 2){
                count($data['model_data']) > 0 ? $chk++ : '';
            }elseif($data['model_type'] == 3){
                for($i=0;$i<count($data['packageData']);$i++){
                    if(isset($data['packageData'][$i]['list'])){ //未選擇商品不建立
                        $chk++;
                    }
                }
            }
            if($chk > 0){
                    $product = ProductDB::create($data);
                    //處理圖片
                    for($i=1;$i<=5;$i++){
                        $columnName = 'new_photo'.$i;
                        if ($request->hasFile($columnName)) {
                            $file = $request->file($columnName);
                            $result = $this->storeImageFile($columnName, $product, $request);
                            !empty($result) ? $product->update([$columnName => $result]) : '';
                        }
                    }
                switch ($modelType) {
                    case 1: //單一商品
                        $productModelData['quantity'] = $data['quantity'] ?? 0;
                        $productModelData['safe_quantity'] = $data['safe_quantity'] ?? 0;
                        $productModelData['safe_quantity'] == 0 ? $productModelData['safe_quantity'] = 1 : '';
                        !empty($data['vendor_product_model_id']) ?  $productModelData['vendor_product_model_id'] = $data['vendor_product_model_id'] : $productModelData['vendor_product_model_id'] = null;
                        $productModelData['gtin13'] = $data['gtin13'];
                        $productModelData['name'] = '單一規格';
                        $productModelData['is_del'] = 0;
                        $productModelData['product_id'] = $product->id;
                        $productModel = ProductModelDB::create($productModelData);
                        //建立庫存調整紀錄
                        $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值',$vendorId);
                        //產生SKU及鼎新代碼
                        $data['product_model_id'] = $productModel->id;
                        $output = $this->makeSku($data);
                        $productModel->update($output);
                        break;
                    case 2: //多款商品
                        if(isset($data['model_data'])){
                            for($i=0;$i<count($data['model_data']);$i++){
                                $productModelData['quantity'] = $data['model_data'][$i]['quantity'];
                                $productModelData['safe_quantity'] = $data['model_data'][$i]['safe_quantity'];
                                $productModelData['safe_quantity'] == 0 ? $productModelData['safe_quantity'] = 1 : '';
                                $productModelData['gtin13'] = $data['model_data'][$i]['gtin13'];
                                $productModelData['vendor_product_model_id'] = $data['model_data'][$i]['vendor_product_model_id'];
                                $productModelData['name'] = $data['model_data'][$i]['name'];
                                $productModelData['is_del'] = 0;
                                $productModelData['product_id'] = $product->id;
                                $productModel = ProductModelDB::create($productModelData);
                                //建立庫存調整紀錄
                                $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值',$vendorId);
                                //產生SKU及鼎新代碼
                                $data['product_model_id'] = $productModel->id;
                                $output = $this->makeSku($data);
                                $productModel->update($output);
                            }
                        }
                        break;
                    case 3: //組合商品
                        for($i=0;$i<count($data['packageData']);$i++){
                            $data['packageData'][$i]['sku'] = $this->chkBOM($data['packageData'][$i]['sku'],$vendorId);
                            $productPackageData['sku'] = $data['packageData'][$i]['sku'];
                            $productPackageData['name'] = $data['packageData'][$i]['name'];
                            $productPackageData['vendor_product_model_id'] = $data['packageData'][$i]['vendor_product_model_id'];
                            $productPackageData['quantity'] = $data['packageData'][$i]['quantity'];
                            $productPackageData['safe_quantity'] = $data['packageData'][$i]['safe_quantity'];
                            $productPackageData['safe_quantity'] == 0 ? $productPackageData['safe_quantity'] = 1 : '';
                            $productPackageData['is_del'] = 0;
                            $productPackageData['product_id'] = $product->id;
                            $productModel = ProductModelDB::create($productPackageData);
                            $productPackage = ProductPackageDB::create([
                                'product_id' => $product->id,
                                'product_model_id' => $productModel->id,
                            ]);
                            //建立庫存調整紀錄
                            $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值',$vendorId);
                            //產生SKU及鼎新代碼
                            $data['sku'] = $data['packageData'][$i]['sku'];
                            $data['product_model_id'] = $productModel->id;
                            $output = $this->makeSku($data);
                            $productModel->update($output);
                            //建立組合商品-商品資料
                            if(isset($data['packageData'][$i]['list'])){
                                for($j=0;$j<count($data['packageData'][$i]['list']);$j++){
                                    $listData['product_package_id'] = $productPackage->id;
                                    $listData['product_model_id'] = $data['packageData'][$i]['list'][$j]['product_model_id'];
                                    $listData['quantity'] = $data['packageData'][$i]['list'][$j]['quantity'];
                                    $productPackageList = ProductPackageListDB::create($listData);
                                }
                            }
                        }
                        //回寫package_data欄位
                        $packageData = [];
                        $packages = $product->packagesWithTrashed;
                        if(count($packages) > 0){
                            $i=0;
                            foreach($packages as $package){
                                $packageData[$i]['name'] = $package->name;
                                $packageData[$i]['bom'] = $package->sku;
                                !empty($package->deleted_at) ? $packageData[$i]['is_del'] = '1' : $packageData[$i]['is_del'] = '0';
                                $packageData[$i]['quantity'] = '';
                                $packageData[$i]['safe_quantity'] = '';
                                $x = 0;
                                foreach($package->lists as $list){
                                    $packageData[$i]['lists'][$x]['sku'] = $list->sku;
                                    $packageData[$i]['lists'][$x]['quantity'] = "$list->quantity";
                                    $packageData[$i]['lists'][$x]['name'] = '';
                                    $packageData[$i]['lists'][$x]['price'] = '';
                                    $x++;
                                }
                                $i++;
                            }
                        }
                        if(count($packageData) > 0){
                            $product->update(['package_data' => json_encode($packageData)]);
                        }

                        break;
                    default:
                        break;
                }
            }else{
                $message = '款式商品/組合商品內容不正確。';
                Session::put('error', $message);
                return redirect()->back()->withInput($request->all());
            }
        }else{
            $message = '款式類別錯誤!!';
            Session::put('error', $message);
            return redirect()->back()->withInput($request->all());
        }
        $message = '商品建立完成!!';
        $message = '產品資料更新完成!!';
        //檢查條碼
        if($data['status'] == -9 || $data['status'] == 1 || $data['status'] == -3){
            if($modelType == 1){
                $gtin13 = $data['gtin13'];
                if(!empty($gtin13)){
                    $productModels = ProductModelDB::where('gtin13',"$gtin13")->get();
                    if(count($productModels) > 0){
                        $skus = [];
                        foreach($productModels as $pm){
                            $skus[] = $pm->sku;
                        }
                        $skus = join('、',$skus);
                        $url = 'https://'.env('GATE_DOMAIN').'/productTransfer?gtin13='.$gtin13;
                        $message .= " 此商品條碼 $gtin13 與 $skus 皆填寫完全相同的國際條碼，避免採購單有誤，請前往<a class='text-primary' href='$url' target='_blank'>「中繼 → 商品貨號轉換」</a>設定對應的貨號轉換。";
                    }
                }
            }elseif($modelType == 2){
                $models = $product->models;
                foreach($models as $model){
                    if(!empty($model->gtin13)){
                        $gtin13 = $model->gtin13;
                        if (!empty($gtin13)) {
                            $productModels = ProductModelDB::where('gtin13', "$gtin13")->get();
                            if (count($productModels) > 1) {
                                $skus = [];
                                foreach ($productModels as $pm) {
                                    $skus[] = $pm->sku;
                                }
                                $skus = join('、', $skus);
                                $url = 'https://'.env('GATE_DOMAIN').'/productTransfer?gtin13='.$gtin13;
                                $message .= "<br>此商品條碼 $gtin13 與 $skus 皆填寫完全相同的國際條碼，避免採購單有誤，請前往<a class='text-primary' href='$url' target='_blank'>「中繼 → 商品貨號轉換」</a>設定對應的貨號轉換。";
                            }
                        }
                    }
                }
            }
        }
        if($data['status'] == -9 || $data['status'] == 1 || $data['status'] == -3){
            ProductAutoUpdateToErpJob::dispatchNow($product->id);
        }
        Session::put('success', $message);
        $backUrl = Session::pull('backUrl');
        Session::forget('backUrl');
        if(!empty($backUrl)){
            return redirect($backUrl);
        }
        // return redirect()->route('admin.products.show',$product->id);
        return redirect()->route('admin.products.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (request()->from_vendor) {
            //如果是從商家管理點新增商品過來,則產生返回連結
            $backUrl = url('vendors/'.(INT)request()->from_vendor.'#vendor-product');
        }else{
            $backUrl = request()->server('HTTP_REFERER');
        }
        Session::put('backUrl', $backUrl);

        $menuCode = 'M4S1';
        $shippingMethods = ShippingMethodDB::all();
        $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
        $countries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->distinct($countryTable.'.name')->get();
        $unitNames = ProductUnitNameDB::all();
        $digiwinProductCates = DigiwinProductCateDB::all();
        request()->request->add(['id' => $id]);
        $product = $this->getProductData(request(),'show');

        //修正組合品資料
        if($product->model_type == 3){
            $dd = collect(json_decode(str_replace('	','',$product->package_data)));
            if(!empty($dd) && count($dd) > 0){
                foreach ($dd as $model) {
                    if (!empty($model->bom)) {
                        $tmp = ProductModelDB::where('sku', $model->bom)->first();
                        if(isset($model->is_del)){
                            if($model->is_del == 1){
                                if(!empty($tmp)){
                                    $pp = ProductPackageDB::where([['product_id',$product->id],['product_model_id',$tmp->id]])->first();
                                    if(!empty($pp)){
                                        $packageList = ProductPackageListDB::where([['product_package_id',$pp->id],['product_model_id',$tmp->id]])->first();
                                        if(!empty($packageList)){
                                            $packageList->delete();
                                        }
                                        $pp->delete();
                                    }
                                }
                            }else{
                                if(!empty($tmp)){
                                    $pp = ProductPackageDB::where([['product_id',$product->id],['product_model_id',$tmp->id]])->first();
                                    if(empty($pp)){
                                        $pp = ProductPackageDB::create([
                                            'product_id' => $product->id,
                                            'product_model_id' => $tmp->id,
                                        ]);
                                    }
                                    if(!empty($model->lists) && count($model->lists) > 0){
                                        foreach ($model->lists as $li) {
                                            if (!empty($li->sku)) {
                                                $tmp = ProductModelDB::where('sku', $li->sku)->first();
                                                if(!empty($tmp) && $li->quantity > 0){
                                                    $packageList = ProductPackageListDB::where([['product_package_id',$pp->id],['product_model_id',$tmp->id]])->first();
                                                    if(empty($packageList)){
                                                        //組合商品中包含多個商品
                                                        ProductPackageListDB::create([
                                                            'product_package_id' => $pp->id,
                                                            'product_model_id' => $tmp->id,
                                                            'quantity' => $li->quantity,
                                                        ]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }else{
                            if(!empty($tmp)){
                                $pp = ProductPackageDB::where([['product_id',$product->id],['product_model_id',$tmp->id]])->first();
                                if(empty($pp)){
                                    $pp = ProductPackageDB::create([
                                        'product_id' => $product->id,
                                        'product_model_id' => $tmp->id,
                                    ]);
                                }
                                if(!empty($model->lists) && count($model->lists) > 0){
                                    foreach ($model->lists as $li) {
                                        if (!empty($li->sku)) {
                                            $tmp = ProductModelDB::where('sku', $li->sku)->first();
                                            if(!empty($tmp) && $li->quantity > 0){
                                                $packageList = ProductPackageListDB::where([['product_package_id',$pp->id],['product_model_id',$tmp->id]])->first();
                                                if(empty($packageList)){
                                                    //組合商品中包含多個商品
                                                    ProductPackageListDB::create([
                                                        'product_package_id' => $pp->id,
                                                        'product_model_id' => $tmp->id,
                                                        'quantity' => $li->quantity,
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $subCategories = SubCategoryDB::whereIn('category_id',explode(',',$product->category_id))->where('is_on',1)->orderBy('sort_id','asc')->get();
        $categories = CategoryDB::whereIn('id',explode(',',$product->category_ids))->where('is_on',1)->get();
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        for($i=0;$i<count($langs);$i++){
            $getData = ProductLangDB::where([['lang',$langs[$i]['code']],['product_id',$product->id]])->get()->toArray();
            foreach($getData as $langData){
                $langs[$i]['data'] = $langData;
            }
        }
        $oldImages[] = $product->new_photo1;
        $oldImages[] = $product->new_photo2;
        $oldImages[] = $product->new_photo3;
        $oldImages[] = $product->new_photo4;
        $oldImages[] = $product->new_photo5;
        // dd($product);
        return view('admin.products.show',compact('menuCode','langs','shippingMethods','subCategories','categories','product','unitNames','countries','oldImages','digiwinProductCates'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductsRequest $request, $id)
    {
        $product = ProductDB::findOrFail($id);
        $data = $request->all();
        isset($data['category_id']) ? $data['category_id'] = join(',',$data['category_id']) : $data['category_id'] = null;
        isset($data['sub_categories']) ? $data['sub_categories'] = join(',',$data['sub_categories']) : $data['sub_categories'] = null;
        isset($data['name']) ? $data['name'] = str_replace('_','-',$data['name']) : '';
        $modelType = $data['model_type'];
        $vendorId = $data['vendor_id'];
        $message = null;
        if($data['status'] != -2) {
            if ($modelType == 2) {
                if(!isset($data['model_data'])){
                    $message = '您是不是忘記填寫款式資料??';
                }
            }elseif ($modelType == 3) {
                if(!isset($data['packageData'])) {
                    $message = '您是不是忘記建立組合品資料??';
                } else {
                    $chkData = 0;
                    for($i = 0;$i < count($data['packageData']);$i++) {
                        if(isset($data['packageData'][$i]['list']) && count($data['packageData'][$i]['list']) > 0) {
                            $chkData++;
                        }
                    }
                    if($chkData == 0) {
                        $message = '您是不是忘記填寫組合品內的商品資料??';
                    }
                }
            }
        }
        if(!empty($message)){
            Session::put('error', $message);
            return redirect()->back()->withInput($request->all());
        }
        $data['TMS_price'] ?? $data['TMS_price'] = 0;
        $data['fake_price'] ?? $data['fake_price'] = 0;
        $data['vendor_price'] ?? $data['vendor_price'] = 0;
        $data['airplane_days'] ?? $data['airplane_days'] = 0;
        $data['hotel_days'] ?? $data['hotel_days'] = 0;
        $data['is_hot'] ?? $data['is_hot'] = 0;
        $data['storage_life'] ?? $data['storage_life'] = 0;
        isset($data['shipping_methods']) ? $data['shipping_methods'] = join(',',$data['shipping_methods']) : '';
        isset($data['allow_country']) ? $data['allow_country'] = join(',',$data['allow_country']) : '';
        if(isset($data['allow_country_ids'])){
            $data['allow_country'] = join(',',CountryDB::whereIn('id',$data['allow_country_ids'])->get()->pluck('name')->all());
            $data['allow_country_ids'] = join(',',$data['allow_country_ids']);
        }else{
            $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
            $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
            $allCountries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->distinct($countryTable.'.name')->get()->pluck('name')->all();
            $data['allow_country'] = join(',',$allCountries);
            $data['allow_country_ids'] = join(',',CountryDB::whereIn('name',$allCountries)->get()->pluck('id')->all());
        }
        if(isset($data['from_country_id'])){
            $tmp = CountryDB::find($data['from_country_id']);
            !empty($tmp) ? $data['product_sold_country'] = $tmp->name : $data['product_sold_country'] = null;
        }
        if($data['type'] == 3){
            $data['price'] = 0;
            $data['status'] = -9;
        }else{ //檢查金額不可為0
            if($data['price'] <= 0){
                Session::put('error','注意!! 商品類型非贈品時，金額不可為 0 !!');
                return redirect()->back();
            }
        }
        if(isset($data['unit_name_id'])){
            $tmp = ProductUnitNameDB::find($data['unit_name_id']);
            !empty($tmp) ? $data['unit_name'] = $tmp->name : $data['unit_name'] = null;
        }
        //最快出貨日大於最晚出貨日 清空最晚出貨日
        if(!empty($data['vendor_earliest_delivery_date'])) {
            strtotime($data['vendor_earliest_delivery_date']) > strtotime($data['vendor_latest_delivery_date']) ? $data['vendor_latest_delivery_date'] = null : '';
        }
        if($data['category_id'] == 17){
            if((empty($data['ticket_group']) && empty($data['ticket_merchant_no']))){
                Session::put('error','注意!! 票券商品特店代號與群組至少要填一種!!');
                return redirect()->back();
            }elseif((!empty($data['ticket_group']) && !empty($data['ticket_merchant_no']))){
                Session::put('error','注意!! 票券商品特店代號與群組只能填一種!!');
                return redirect()->back();
            }
        }
        //處理圖片
        for($i=1;$i<=5;$i++){
            $columnName = 'new_photo'.$i;
            if ($request->hasFile($columnName)) {
                $file = $request->file($columnName);
                $result = $this->storeImageFile($columnName, $product, $request);
                !empty($result) ? $data[$columnName] = $result : '';
            }
        }
        // dd($data);
        //送審通過
        $data['status'] == -9 || $data['status'] == 1 || $data['status'] == -3 ? $data['pass_time'] = date('Y-m-d H:i:s') : '';
        $modelType = (INT)$request->model_type;
        if(is_numeric($modelType) && $modelType >= 1 || $modelType <= 3 ){
            //紀錄 vendor_price , price , fake_price && storage_life 變動
            if($product->vendor_price != $data['vendor_price']){
                $this->productUpdateRecord($product->id,'vendor_price',$product->vendor_price,$data['vendor_price']);
            }
            if($product->price != $data['price']){
                $this->productUpdateRecord($product->id,'price',$product->price,$data['price']);
            }
            if($product->fake_price != $data['fake_price']){
                $this->productUpdateRecord($product->id,'fake_price',$product->fake_price,$data['fake_price']);
            }
            if($product->storage_life != $data['storage_life']){
                $this->productUpdateRecord($product->id,'storage_life',$product->storage_life,$data['storage_life']);
            }
            $product->update($data);
            switch ($modelType) {
                case 1://單一款式
                    // dd($data);
                    if($data['product_model_id']){
                        !empty($data['quantity']) ? $productModelData['quantity'] = $data['quantity'] : $productModelData['quantity'] = 0;
                        !empty($data['safe_quantity']) ? $productModelData['safe_quantity'] = $data['safe_quantity'] : $productModelData['safe_quantity'] = 0;
                        $productModelData['safe_quantity'] == 0 ? $productModelData['safe_quantity'] = 1 : '';
                        !empty($data['gtin13']) ?  $productModelData['gtin13'] = $data['gtin13'] : $productModelData['gtin13'] = null;
                        !empty($data['vendor_product_model_id']) ?  $productModelData['vendor_product_model_id'] = $data['vendor_product_model_id'] : $productModelData['vendor_product_model_id'] = null;
                        $productModel = ProductModelDB::findOrFail($data['product_model_id']);
                        if(!empty($productModel)){
                            // 建立庫存調整紀錄 或 gtin13 國際條碼變動
                            if ($productModel->gtin13 != $productModelData['gtin13']){
                                if($productModel->quantity != $productModelData['quantity']){
                                    $this->productQuantityRecord($productModel->id,$productModel->quantity,$productModelData['quantity'],$productModel->gtin13,$productModelData['gtin13'],'國際條碼及庫存變更',$vendorId);
                                }else{
                                    $this->productQuantityRecord($productModel->id,null,null,$productModel->gtin13,$productModelData['gtin13'],'國際條碼變更',$vendorId);
                                }
                            }else{
                                if($productModel->quantity != $productModelData['quantity']){
                                    $this->productQuantityRecord($productModel->id,$productModel->quantity,$productModelData['quantity'],null,null,'商品庫存變更',$vendorId);
                                }
                            }
                            $productModel->update($productModelData);
                        }
                    }
                    break;
                case 2://多款產品
                    // dd($data['model_data']);
                    if(isset($data['model_data'])){
                        for($i=0;$i<count($data['model_data']);$i++){
                            $productModelData['name'] = $data['model_data'][$i]['name'];
                            $productModelData['quantity'] = (INT)$data['model_data'][$i]['quantity'];
                            $productModelData['safe_quantity'] = (INT)$data['model_data'][$i]['safe_quantity'];
                            $productModelData['safe_quantity'] == 0 ? $productModelData['safe_quantity'] = 1 : '';
                            $productModelData['gtin13'] = $data['model_data'][$i]['gtin13'];
                            $productModelData['vendor_product_model_id'] = $data['model_data'][$i]['vendor_product_model_id'];
                            $productModelId = $data['model_data'][$i]['product_model_id'];
                            if($productModelId){
                                $productModel = ProductModelDB::findOrFail($productModelId);
                                // 建立庫存調整紀錄 或 gtin13 國際條碼變動
                                if ($productModel->gtin13 != $productModelData['gtin13']){
                                    if($productModel->quantity != $productModelData['quantity']){
                                        $this->productQuantityRecord($productModel->id,$productModel->quantity,$productModelData['quantity'],$productModel->gtin13,$productModelData['gtin13'],'國際條碼及庫存變更',$vendorId);
                                    }else{
                                        $this->productQuantityRecord($productModel->id,null,null,$productModel->gtin13,$productModelData['gtin13'],'國際條碼變更',$vendorId);
                                    }
                                }else{
                                    if($productModel->quantity != $productModelData['quantity']){
                                        $this->productQuantityRecord($productModel->id,$productModel->quantity,$productModelData['quantity'],null,null,'商品庫存變更',$vendorId);
                                    }
                                }
                                $productModel->update($productModelData);
                            }else{
                                $productModelData['is_del'] = 0;
                                $productModelData['product_id'] = $product->id;
                                $productModel = ProductModelDB::create($productModelData);
                                //建立庫存調整紀錄
                                $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值',$vendorId);
                                //產生SKU及鼎新代碼
                                $data['product_model_id'] = $productModel->id;
                                $output = $this->makeSku($data);
                                $productModel->update($output);
                            }
                        }
                    }
                    break;
                case 3:
                    if(isset($data['packageData'])){
                        for($i=0;$i<count($data['packageData']);$i++){
                            $data['packageData'][$i]['sku'] = $this->chkBOM($data['packageData'][$i]['sku'],$vendorId);
                            isset($data['packageData'][$i]['product_package_id']) ? $productPackageData['product_package_id'] = $data['packageData'][$i]['product_package_id'] : $productPackageData['product_package_id'] = '';
                            $productPackageData['vendor_product_model_id'] = $data['packageData'][$i]['vendor_product_model_id'];
                            $productPackageData['sku'] = $data['packageData'][$i]['sku'];
                            $productPackageData['name'] = $data['packageData'][$i]['name'];
                            $productPackageData['quantity'] = $data['packageData'][$i]['quantity'];
                            $productPackageData['safe_quantity'] = $data['packageData'][$i]['safe_quantity'];
                            $productPackageData['safe_quantity'] == 0 ? $productPackageData['safe_quantity'] = 1 : '';
                            isset($data['packageData'][$i]['list']) ? $packageListData = $data['packageData'][$i]['list'] : $packageListData = null;
                            if(!empty($productPackageData['product_package_id'])){
                                $productPackage = ProductPackageDB::findOrFail($productPackageData['product_package_id']);
                                $productModel = ProductModelDB::findOrFail($productPackage->product_model_id);
                                if($productModel->quantity != $productPackageData['quantity']){
                                    //建立庫存調整紀錄
                                    $this->productQuantityRecord($productModel->id,$productModel->quantity,$productPackageData['quantity'],null,null,'商品庫存變更',$vendorId);
                                }
                                $productModel->update($productPackageData);
                                if(isset($packageListData)){
                                    for($j=0;$j<count($packageListData);$j++){
                                        $listData['product_package_id'] = $productPackage->id;
                                        $listData['product_model_id'] = $packageListData[$j]['product_model_id'];
                                        $listData['quantity'] = $packageListData[$j]['quantity'];
                                        if(isset($packageListData[$j]['product_package_list_id'])){
                                            ProductPackageListDB::findOrFail($packageListData[$j]['product_package_list_id'])->update($listData);
                                        }else{
                                            $productPackageList = ProductPackageListDB::create($listData);
                                        }
                                    }
                                }
                            }else{
                                $productPackageData['product_id'] = $product->id;
                                $productModel = ProductModelDB::create($productPackageData);
                                //新增Package資料
                                $productPackage = ProductPackageDB::create([
                                    'product_id' => $product->id,
                                    'product_model_id' => $productModel->id,
                                ]);
                                //建立庫存調整紀錄
                                $this->productQuantityRecord($productModel->id,null,$productModel->quantity,null,$productModel->gtin13,'初始值',$vendorId);
                                //產生SKU及鼎新代碼
                                $data['sku'] = $data['packageData'][$i]['sku'];
                                $data['product_model_id'] = $productModel->id;
                                $output = $this->makeSku($data);
                                $productModel->update($output);
                                //新增PackageList資料
                                if ($packageListData) {
                                    for ($j=0;$j<count($packageListData);$j++) {
                                        $listData['product_package_id'] = $productPackage->id;
                                        $listData['product_model_id'] = $packageListData[$j]['product_model_id'];
                                        $listData['quantity'] = $packageListData[$j]['quantity'];
                                        $productPackageList = ProductPackageListDB::create($listData);
                                    }
                                }
                            }
                        }
                        //回寫package_data欄位
                        $packageData = [];
                        $packages = $product->packagesWithTrashed;
                        if(count($packages) > 0){
                            $i=0;
                            foreach($packages as $package){
                                $packageData[$i]['name'] = $package->name;
                                $packageData[$i]['bom'] = $package->sku;
                                !empty($package->deleted_at) ? $packageData[$i]['is_del'] = '1' : $packageData[$i]['is_del'] = '0';
                                $packageData[$i]['quantity'] = '';
                                $packageData[$i]['safe_quantity'] = '';
                                $x = 0;
                                foreach($package->lists as $list){
                                    $packageData[$i]['lists'][$x]['sku'] = $list->sku;
                                    $packageData[$i]['lists'][$x]['quantity'] = "$list->quantity";
                                    $packageData[$i]['lists'][$x]['name'] = '';
                                    $packageData[$i]['lists'][$x]['price'] = '';
                                    $x++;
                                }
                                $i++;
                            }
                        }
                        if(count($packageData) > 0){
                            $product->update(['package_data' => json_encode($packageData)]);
                        }
                    }
                    break;
                default:
                    break;
            }
        }else{
            $message = '款式類別錯誤!!';
            Session::put('error', $message);
            return redirect()->back();
        }
        $message = '產品資料更新完成!!';
        //檢查條碼
        if($data['status'] == -9 || $data['status'] == 1 || $data['status'] == -3){
            if($modelType == 1){
                $gtin13 = $data['gtin13'];
                if (!empty($gtin13)) {
                    $productModels = ProductModelDB::where('gtin13', "$gtin13")->get();
                    if (count($productModels) > 1) {
                        $skus = [];
                        foreach ($productModels as $pm) {
                            $skus[] = $pm->sku;
                        }
                        $skus = join('、', $skus);
                        $url = 'https://'.env('GATE_DOMAIN').'/productTransfer?gtin13='.$gtin13;
                        $message .= " 此商品條碼 $gtin13 與 $skus 皆填寫完全相同的國際條碼，避免採購單有誤，請前往<a class='text-primary' href='$url' target='_blank'>「中繼 → 商品貨號轉換」</a>設定對應的貨號轉換。";
                    }
                }
            }elseif($modelType == 2){
                $models = $product->models;
                foreach($models as $model){
                    if(!empty($model->gtin13)){
                        $gtin13 = $model->gtin13;
                        if(!empty($gtin13)){
                            $productModels = ProductModelDB::where('gtin13',"$gtin13")->get();
                            if(count($productModels) > 1){
                                $skus = [];
                                foreach($productModels as $pm){
                                    $skus[] = $pm->sku;
                                }
                                $skus = join('、',$skus);
                                $url = 'https://'.env('GATE_DOMAIN').'/productTransfer?gtin13='.$gtin13;
                                $message .= "<br>此商品條碼 $gtin13 與 $skus 皆填寫完全相同的國際條碼，避免採購單有誤，請前往<a class='text-primary' href='$url' target='_blank'>「中繼 → 商品貨號轉換」</a>設定對應的貨號轉換。";
                            }
                        }
                    }
                }
            }
        }
        if($data['status'] == -9 || $data['status'] == 1 || $data['status'] == -3){
            ProductAutoUpdateToErpJob::dispatchNow($product->id);
        }
        //你訂案商品狀態回傳
        if(!strstr(env('APP_URL'),'localhost')){
            if(in_array($product->vendor_id,[723,729,730]) && $product->model_type == 1 && ($data['status'] == -9 || $data['status'] == 0 || $data['status'] == -2)){
                $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
                $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
                $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
                $productData = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                ->where($vendorTable.'.id',$product->vendor_id)
                ->where($productTable.'.id',$product->id)
                ->where($productTable.'.is_del',0)
                ->select([
                    $vendorTable.'.merchant_no',
                    $productModelTable.'.digiwin_no',
                    $productModelTable.'.vendor_product_model_id as vendor_product_no',
                    $productTable.'.name',
                    $productTable.'.price',
                    DB::raw("(CASE WHEN $productTable.status = -9 THEN '2' WHEN $productTable.status = 0 THEN '1' WHEN $productTable.status = -2 THEN '3' END) as status"),
                ])->first();
                if(!empty($productData)){
                    $url = env('NIDIN_PRODUCT_RETURN_URL');
                    $sendData = [
                        'merchant_no' => $productData->merchant_no,
                        'product'  => [
                            'digiwin_no' => $productData->digiwin_no,
                            'vendor_product_no' => $productData->vendor_product_no,
                            'name' => $productData->name,
                            'price' => $productData->price,
                            'status' => $productData->status,
                        ],
                    ];
                    $sendData = json_encode($sendData,true);
                    $response = Http::withHeaders([
                        "Content-Type" => "application/json;charset=utf-8"
                    ])->send("POST", $url, [
                        "body" => $sendData
                    ]);
                    $response = $response->body();
                    $response = json_decode($response,true);
                    $message .= "<span class='text-warning text-bold'> 你訂商品回傳訊息: ".$response['message']."</span>";
                }
            }
        }
        Session::put('success', $message);
        $backUrl = Session::pull('backUrl');
        Session::forget('backUrl');
        if($backUrl){
            return redirect($backUrl);
        }
        // return redirect()->back();
        return redirect()->route('admin.products.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = ProductDB::findOrFail($id);
        $product->update(['is_del' => 1, 'status' => -9]);
        if(request()->has('from')){
            return redirect()->route('admin.vendors.show',request()->vendor_id.request()->from);
        }
        return redirect()->back();
    }
    /*
        語言功能
     */
    public function lang(Request $request)
    {
        if (Validator::make($request->all(), $this->langRules)->fails()) {
            return redirect()->route('admin.products.show', $request->product_id.'#lang-'.$request->lang)->withErrors(Validator::make($request->all(), $this->langRules));
        }
        $data = $request->all();
        $modelType = $request->model_type;
        $data['id'] = $data['product_id'] = $request->product_id;
        $langId = $request->langId;
        $product = ProductDB::find($data['product_id'])->toArray();
        //回寫舊資料
        if($data['lang'] == 'en'){
            $tmp = ProductEnDB::find($data['product_id']);
            !empty($tmp) ?  $tmp->update($data) : $tmp = ProductEnDB::create($data);
        }elseif($data['lang'] == 'jp'){
            $tmp = ProductJpDB::find($data['product_id']);
            !empty($tmp) ?  $tmp->update($data) : $tmp = ProductJpDB::create($data);
        }elseif($data['lang'] == 'kr'){
            $tmp = ProductKrDB::find($data['product_id']);
            !empty($tmp) ?  $tmp->update($data) : $tmp = ProductKrDB::create($data);
        }elseif($data['lang'] == 'th'){
            $tmp = ProductThDB::find($data['product_id']);
            !empty($tmp) ?  $tmp->update($data) : $tmp = ProductThDB::create($data);
        }
        if(!empty($langId)){
            $productLang = ProductLangDB::find($langId);
            if(!empty($productLang)){
                $productLang->update($data);
            }
        }else{
            $productLang = ProductLangDB::create($data);
        }
        switch ($modelType) {
            case 1:
                //只有多款及組合商品需要多語言，單一款式多國語言已包含在ProductLang那邊
                break;

            case 2:
                for($i=0;$i<count($data['model_data']);$i++){
                    $productModel['id'] = $data['model_data'][$i]['product_model_id'];
                    $productModel['name_'.$data['lang']] = $data['model_data'][$i]['name_'.$data['lang']];
                    $pm = ProductModelDB::find($productModel['id']);
                    !empty($pm) ? $pm->update($productModel) : '';
                }
                break;

            case 3:
                for($i=0;$i<count($data['packageData']);$i++){
                    $productModel['id'] = $data['packageData'][$i]['product_model_id'];
                    $productModel['name_'.$data['lang']] = $data['packageData'][$i]['name_'.$data['lang']];
                    $pm = ProductModelDB::find($productModel['id']);
                    !empty($pm) ? $pm->update($productModel) : '';
                }
                break;

            default:
                # code...
                break;
        }
        return redirect()->route('admin.products.show', $request->product_id.'#lang-'.$request->lang);
    }

    /*
        產品搜尋列表
     */
    public function getList(Request $request)
    {
        $keyword = $request->search;
        $data = ProductDB::join('product_model', 'product.id', '=', 'product_model.product_id')
        ->join('vendor', 'vendor.id', '=', 'product.vendor_id')
        ->where(function($q){
            $q->where('product.package_data','')->orWhere('product.package_data',null);
        })->where('product.is_del',0)
        ->whereIn('status',[1,-3,-9])
        ->where(function ($query) use ($keyword) {
            $query->where('product.name','like',"%$keyword%")
            ->orWhere('product_model.sku','like',"%$keyword%")
            ->orWhere('vendor.name','like',"%$keyword%");
        })->select(
            'product.name as name',
            DB::raw('(CASE WHEN product.model_name is null or product.model_name = "" THEN product_model.name ELSE product.model_name END) as model_name'),
            'product_model.sku as sku',
            'vendor.name as vendor_name',
            'product_model.id as product_model_id')->get();
        return response()->json($data);
    }

    public function delModel(Request $input){
        $id = (int)request()->id;
        $productModel = ProductModelDB::findOrFail($id);
        if(!empty($productModel)){
            $sku = $productModel->sku;
            $productModel->update(['is_del' => 1]);
            //找出相同的sku號碼未被移除的一起移除掉
            $productModels = ProductModelDB::where([['is_del',0],['sku',$sku]])->get();
            if(count($productModels) > 0){
                foreach($productModels as $pm){
                    $pm->update(['is_del' => 1]);
                }
            }
        }
        return redirect()->back();
    }

    public function delPackage(Request $input){
        $id = (int)request()->id;
        $productPackage = ProductPackageDB::findOrFail($id);
        if($productPackage){
            $productModel = ProductModelDB::find($productPackage->product_model_id);
            !empty($productModel) ? $productModel->update(['is_del' => 1]) : '';

            $productPackage->delete();

            //回寫package_data欄位
            $product = ProductDB::with('packagesWithTrashed')->find($productPackage->product_id);
            if(!empty($product)){
                $packageData = [];
                $packages = $product->packagesWithTrashed;
                if(count($packages) > 0){
                    $i=0;
                    foreach($packages as $package){
                        $packageData[$i]['name'] = $package->name;
                        $packageData[$i]['bom'] = $package->sku;
                        !empty($package->deleted_at) ? $packageData[$i]['is_del'] = '1' : $packageData[$i]['is_del'] = '0';
                        $packageData[$i]['quantity'] = '';
                        $packageData[$i]['safe_quantity'] = '';
                        $x = 0;
                        foreach($package->lists as $list){
                            $packageData[$i]['lists'][$x]['sku'] = $list->sku;
                            $packageData[$i]['lists'][$x]['quantity'] = "$list->quantity";
                            $packageData[$i]['lists'][$x]['name'] = '';
                            $packageData[$i]['lists'][$x]['price'] = '';
                            $x++;
                        }
                        $i++;
                    }
                }
                if(count($packageData) > 0){
                    $product->update(['package_data' => json_encode($packageData)]);
                }
            }
        }
        return redirect()->back();
    }

    public function delList(Request $input){
        $id = (int)request()->id;
        $productPackageList = ProductPackageListDB::findOrFail($id);
        if($productPackageList){
            $productPackageList->delete();
        }
        return redirect()->back();
    }

    public function getStockRecord(Request $input){
        $id = (int)request()->id;
        $productModel = ProductModelDB::with('qtyRecords')->findOrFail($id);
        request()->request->add(['product_id' => $productModel->product_id]);
        $product = $this->getProductData(request(),'show');
        $productQtyRecord = $productModel->qtyRecords;
        $data = collect(['product' => $product, 'productModel' => $productModel, 'productQtyRecord' => $productQtyRecord]);
        return response($data);
    }

    public function stockModify(Request $input){
        $productModelId = (int)request()->product_model_id;
        $newStock = (int)request()->quantity;
        $newSafeStock = (int)request()->safe_quantity;
        $newSafeStock == 0 ? $newSafeStock = 1 : ''; //安全庫存強制改為1
        $reason = request()->reason;
        $productQtyRecord = null;
        $adminTable = env('DB_ERPGATE').'.'.(new AdminDB)->getTable();
        $productQuantityRecordTable = env('DB_ICARRY').'.'.(new ProductQuantityRecordDB)->getTable();
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        //找出product model 舊的資料
        $productModel = ProductModelDB::with('product','product.vendor')->findOrFail($productModelId);
        $vendorId = $productModel->product->vendor->id;
        if($productModel->quantity != $newStock || $productModel->safe_quantity != $newSafeStock){
            ProductModelDB::where('id',$productModelId)->update(['quantity' => $newStock, 'safe_quantity' => $newSafeStock]);
            if($productModel->quantity != $newStock){
                $pqrId = $this->productQuantityRecord($productModel->id,$productModel->quantity,$newStock,null,null,$reason,$vendorId);
                $productQtyRecord = ProductQuantityRecordDB::select([
                    $productQuantityRecordTable.'.*',
                    'admin' => AdminDB::whereColumn($adminTable.'.id',$productQuantityRecordTable.'.admin_id')->select($adminTable.'.name')->limit(1),
                    'vendor' => VendorDB::whereColumn($vendorTable.'.id',$productQuantityRecordTable.'.vendor_id')->select($vendorTable.'.name')->limit(1),
                ])->find($pqrId);
            }
        }
        $data = collect(['productModel' => $productModel, 'productQtyRecord' => $productQtyRecord]);
        return response($data);
    }

    public function getHistory(Request $request){
        $data = ProductUpdateRecordDB::where([['product_id',$request->product_id],['column',$request->column]])
        ->select([
            '*',
            'admin_name' => AdminDB::whereColumn('id','admin_id')->select('name')->limit(1),
            'vendor_name' => VendorDB::whereColumn('id','vendor_id')->select('name')->limit(1),
            DB::raw("DATE_FORMAT(create_time,'%Y/%m/%d %H:%i:%s') as createTime"),
        ])->orderBy('create_time','desc')->get();
        return response($data);
    }

    public function getGtin13History(Request $request){
        $gtin13 = $request->gtin13;
        $data = ProductQuantityRecordDB::where('product_model_id',$request->product_model_id)
        ->where(function($query)use($gtin13){
            $query->where('before_gtin13',"$gtin13")
            ->orWhere('after_gtin13',"$gtin13");
        })->select([
            '*',
            'admin_name' => AdminDB::whereColumn('id','admin_id')->select('name')->limit(1),
            'vendor_name' => VendorDB::whereColumn('id','vendor_id')->select('name')->limit(1),
            DB::raw("DATE_FORMAT(create_time,'%Y/%m/%d %H:%i:%s') as createTime"),
        ])->orderBy('create_time','desc')->get();
        return response($data);
    }
    /*
        匯出功能
    */
    public function export()
    {
        $exportFile = '商品資料匯出_'.date('YmdHis').'.xlsx';
        return Excel::download(new ProductsExport, $exportFile);
    }
    /*
        匯入功能
    */
    public function import(Request $request)
    {
        // $request->request->add(['test' => true]); //加入test bypass 檔案匯入功能
        $request->request->add(['admin_id' => auth('admin')->user()->id]); //加入request
        $request->request->add(['import_no' => time()]); //加入request
        if ($request->hasFile('filename')) {
            $file = $request->file('filename');
            $uploadedFileMimeType = $file->getMimeType();
            $mimes = array('application/excel','application/vnd.ms-excel','application/vnd.msexcel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/CDFV2','application/octet-stream');
            if(in_array($uploadedFileMimeType, $mimes)){
                if($request->cate == 'changePrice'){
                    //檔案不可以直接放入Job中使用dispatch去跑,只能使用dispatchNow
                    $result = ChangePriceFileImportJob::dispatchNow($request);
                    $success = $result['success'];
                    $fail = $result['fail'];
                    $message = $result['message'];
                    $total = $result['total'];
                }
                if($request->cate == 'changeStatus'){
                    //檔案不可以直接放入Job中使用dispatch去跑,只能使用dispatchNow
                    $result = ChangeStatusFileImportJob::dispatchNow($request);
                    $success = $result['success'];
                    $fail = $result['fail'];
                    $message = $result['message'];
                    $total = $result['total'];
                }
                if($request->cate == 'addProduct') {
                    dd('匯入商品功能, 還沒做好');
                }
                Session::put('success', "匯入 $total 筆，成功 $success 筆，失敗 $fail 筆。<br>".$message);
            } else{
                $message = '只接受 xls 或 xlsx 檔案格式';
                Session::put('error', $message);
            }
        }
        return redirect()->back();
    }
    /*
        Quantity && gtin13 變更紀錄
    */
    private function productQuantityRecord($productModelId,$beforeQuantity,$afterQuantity,$beforeGtin13,$afterGtin13,$reason,$vendorId)
    {
        $record = ProductQuantityRecordDB::create([
            'product_model_id' => $productModelId,
            'admin_id' => auth('admin')->user()->id,
            'before_quantity' => $beforeQuantity,
            'after_quantity' => $afterQuantity,
            'before_gtin13' => $beforeGtin13,
            'after_gtin13' => $afterGtin13,
            'reason' => $reason,
            'vendor_id' => $vendorId,
        ]);
        return $record->id;
    }
    /*
        price && storage_life 變更紀錄
    */
    private function productUpdateRecord($productId,$column,$beforeValue,$afterValue)
    {
        $record = ProductUpdateRecordDB::create([
            'product_id' => $productId,
            'admin_id' => auth('admin')->user()->id,
            'column' => $column,
            'before_value' => $beforeValue,
            'after_value' => $afterValue,
        ]);
    }
    /*
        舊版圖檔上傳
    */
    public function upload(Request $request)
    {
        $columnName = $request->column_name;
        if(!empty($columnName)){
        //檢查表單是否有檔案
        if(!$request->hasFile($request->column_name)){
            $message = "請選擇要上傳的檔案再按送出按鈕";
            Session::put('info',$message);
        }else{
            $result = $this->storeFile($request);
            if($result == true){
                $message = "檔案上傳成功";
                Session::put('success',$message);
            }else{
                $message = "檔案上傳失敗";
                Session::put('error',$message);
            }
        }
            return redirect()->route('admin.products.show',$request->product_id.'#old-product-image');
        }
    }
    public function storeImageFile($columnName, $product, $request){
        if(!empty($columnName) && !empty($product)){
            //目的目錄
            $destPath = '/upload/product/';
            //檢查本地目錄是否存在，不存在則建立
            !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
            //檢查S3目錄是否存在，不存在則建立
            !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
            //實際檔案
            $file = $request->file($columnName);
            //副檔名
            $ext = $file->getClientOriginalExtension();
            //新檔名
            $fileName1 = str_replace('new_','',$columnName).'_'.$product->id.'_'. Carbon::now()->timestamp;
            $fileName = $fileName1. '.' . $ext;
            $smallFileName = $fileName1. '_s.' . $ext;
            //變更尺寸寬高
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;
            $originFileName = 'originFileName.'.$ext;
            //將檔案搬至本地目錄
            $file->move(public_path().$destPath, $originFileName);

            //使用Spatie/image的套件Resize圖檔
            Image::load(public_path().$destPath.$originFileName)
            ->width($reSizeWidth)
            ->height($reSizeHeigh)
            ->save(public_path().$destPath.$fileName);
            //將檔案傳送至 S3
            //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
            Storage::disk('s3')->put($destPath.$fileName, file_get_contents(public_path().$destPath.$fileName) , 'public');

            //縮圖
            Image::load(public_path().$destPath.$originFileName)
            ->width(600)
            ->height(320)
            ->save(public_path().$destPath.$smallFileName);
            //將檔案傳送至 S3
            //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
            Storage::disk('s3')->put($destPath.$smallFileName, file_get_contents(public_path().$destPath.$smallFileName) , 'public');

            //刪除本地檔案
            unlink(public_path().$destPath.$originFileName);
            unlink(public_path().$destPath.$fileName);
            unlink(public_path().$destPath.$smallFileName);

            //更新檔案名稱至資料表中
            $product->update([$columnName => $destPath.$fileName]);

            return $destPath.$fileName;
        }
        return null;
    }
    public function storeFile($request){
        isset($request->column_name) ? $columnName = $request->column_name : $columnName = null;
        $product = ProductDB::find($request->product_id);
        if(!empty($columnName) && !empty($product)){
            //目的目錄
            $destPath = '/upload/product/';
            //檢查本地目錄是否存在，不存在則建立
            !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
            //檢查S3目錄是否存在，不存在則建立
            !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
            //實際檔案
            $file = $request->file($columnName);
            //副檔名
            $ext = $file->getClientOriginalExtension();
            //新檔名
            $fileName1 = str_replace('new_','',$columnName).'_'.$request->product_id.'_'. Carbon::now()->timestamp;
            $fileName = $fileName1. '.' . $ext;
            $smallFileName = $fileName1. '_s.' . $ext;
            //變更尺寸寬高
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;
            $originFileName = 'originFileName.'.$ext;
            //將檔案搬至本地目錄
            $file->move(public_path().$destPath, $originFileName);

            //使用Spatie/image的套件Resize圖檔
            Image::load(public_path().$destPath.$originFileName)
            ->width($reSizeWidth)
            ->height($reSizeHeigh)
            ->save(public_path().$destPath.$fileName);
            //將檔案傳送至 S3
            //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
            Storage::disk('s3')->put($destPath.$fileName, file_get_contents(public_path().$destPath.$fileName) , 'public');

            //縮圖
            Image::load(public_path().$destPath.$originFileName)
            ->width(600)
            ->height(320)
            ->save(public_path().$destPath.$smallFileName);
            //將檔案傳送至 S3
            //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
            Storage::disk('s3')->put($destPath.$smallFileName, file_get_contents(public_path().$destPath.$smallFileName) , 'public');

            //刪除本地檔案
            unlink(public_path().$destPath.$originFileName);
            unlink(public_path().$destPath.$fileName);
            unlink(public_path().$destPath.$smallFileName);

            //更新檔案名稱至資料表中
            $product->update([$columnName => $destPath.$fileName]);
            return true;
        }
        return false;
    }
    public function copy($id)
    {
        $menuCode = 'M4S1';
        $copy = true;
        $shippingMethods = ShippingMethodDB::all();
        $countryTable = env('DB_ICARRY').'.'.(new CountryDB)->getTable();
        $shipingFeeTable = env('DB_ICARRY').'.'.(new ShippingFeeDB)->getTable();
        $countries = ShippingFeeDB::join($countryTable,$countryTable.'.name',$shipingFeeTable.'.shipping_methods')
            ->select([
                $countryTable.'.*',
            ])->get();
        $unitNames = ProductUnitNameDB::all();
        request()->request->add(['id' => $id]); //加入request
        $product = $this->getProductData(request(),'show');
        $vendor = VendorDB::find($product->vendor_id);
        $subCategories = SubCategoryDB::where([['category_id',$product->category_id],['is_on',1]])->orderBy('sort_id','asc')->get();
        $categories = CategoryDB::whereIn('id',explode(',',$product->category_ids))->where('is_on',1)->get();
        $productImages = ProductImageDB::where('product_id',$product->id)->orderBy('is_top','desc')->orderBy('is_on','desc')->orderBy('sort','asc')->get();
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        for($i=0;$i<count($langs);$i++){
            $getData = ProductLangDB::where([['lang',$langs[$i]['code']],['product_id',$product->id]])->get()->toArray();
            foreach($getData as $langData){
                $langs[$i]['data'] = $langData;
            }
        }

        //找出款式商品資料
        $product->models = ProductModelDB::where('product_id',$product->id)
            ->select(
                'id',
                'name',
                'sku',
                'quantity',
                'safe_quantity',
                'gtin13',
            )->get();

        //找出組合商品資料
        if($product->model_type == 3){
            $product->packages = ProductPackageDB::join('product_model','product_model.id','product_packages.product_model_id')
            ->where('product_packages.product_id',$product->id)
            ->select(
                'product_packages.id',
                'product_model.name',
                'product_model.name_en',
                'product_model.name_jp',
                'product_model.name_kr',
                'product_model.name_th',
                'product_model.sku',
                'product_model.quantity',
                'product_model.safe_quantity',
            )->get();

            foreach ($product->packages as $package) {
                $lists = ProductPackageListDB::join('product_model','product_model.id','product_package_lists.product_model_id')
                ->join('product','product.id','product_model.product_id')
                ->where('product_package_lists.product_package_id',$package->id)
                ->select(
                    'product_package_lists.id',
                    'product_package_lists.product_model_id',
                    'product_model.sku',
                    'product.name',
                    'product_package_lists.quantity',
                )->get();
                $package->lists = $lists;
            }
        }
        $oldImages[] = $product->new_photo1;
        $oldImages[] = $product->new_photo2;
        $oldImages[] = $product->new_photo3;
        $oldImages[] = $product->new_photo4;
        $oldImages[] = $product->new_photo5;
        $digiwinProductCates = DigiwinProductCateDB::all();
        return view('admin.products.show',compact('menuCode','digiwinProductCates','vendor','langs','shippingMethods','subCategories','categories','product','unitNames','countries','productImages','copy','oldImages'));
    }
    public function deloldimage(Request $request)
    {
        if(!empty($request->id) && !empty($request->columnName)){
            $product = ProductDB::findOrFail($request->id);
            $product->update([$request->columnName => null]);
            return 'success';
        }
        return null;
    }
    private function serviceFee($input = ''){
        if($input == ''){
            $serviceFees = json_decode('[{"name":"天虹","percent":0},{"name":"閃店","percent":0},{"name":"iCarry","percent":0},{"name":"現場提貨","percent":0}]');
        }elseif(is_array($input)){
            for($i=0;$i<count($input['name']);$i++){
                $serviceFees[$i]['name'] = $input['name'][$i];
                $serviceFees[$i]['percent'] = $input['percent'][$i];
            }
            $serviceFees = json_encode($serviceFees);
        }else{
            $serviceFees = json_decode(str_replace('"percent":}','"percent":0}',$input));
        }
        return $serviceFees;
    }

    public function recover(Request $request)
    {
        if(!empty($request->id)){
            $product = ProductDB::find($request->id);
            if(!empty($product)){
                $product->update(['is_del' => 0]);
                Session::put('success','商品已復原。');
            }else{
                Session::put('error','商品資料不存在。');
            }
        }
        return redirect()->back();
    }

    public function getSubCate(Request $request)
    {
        if(count($request->category_id) > 0){
            $subCategories = SubCategoryDB::whereIn('category_id',$request->category_id)->where('is_on',1)->get();
            $product = ProductDB::find($request->product_id);
            !empty($product) ? $subCates = $product->sub_categories : $subCates = null;
            !empty($subCates) ? $subCates = explode(',',$subCates) : '';
            foreach($subCategories as $subCate){
                $subCate->chk = null;
                if(is_array($subCates)){
                    in_array($subCate->id,$subCates) ? $subCate->chk = 'checked' : '';
                }
            }
            return $subCategories;
        }
        return [];
    }

    public function multiProcess(Request $request)
    {
        $products = $this->getProductData(request());
        if($request->cate == 'changeStatus'){
            $status = $request->changeStatus;
            foreach($products as $product){
                $oldStatus = $product->status;
                $productId = $product->id;
                if($oldStatus != $status){
                    if(!empty($product->pass_time)){
                        $product->update(['status' => $status]);
                    }else{
                        if($status == 0 || $status == -1){
                            $product->update(['status' => $status]);
                        }else{
                            $product->update(['pass_time' => date('Y-m-d H:i:s'), 'status' => $status]);
                            ProductAutoUpdateToErpJob::dispatchNow($productId);
                        }
                    }
                    //你訂案
                    if(in_array($product->vendor_id,[723,729,730]) && $product->model_type == 1 && ($status == -9 || $status == 0 || $status == -2)){
                        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
                        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
                        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
                        $productData = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
                        ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                        ->where($vendorTable.'.id',$product->vendor_id)
                        ->where($productTable.'.id',$product->id)
                        ->where($productTable.'.is_del',0)
                        ->select([
                            $vendorTable.'.merchant_no',
                            $productModelTable.'.digiwin_no',
                            $productModelTable.'.vendor_product_model_id as vendor_product_no',
                            $productTable.'.name',
                            $productTable.'.price',
                            DB::raw("(CASE WHEN $productTable.status = -9 THEN '2' WHEN $productTable.status = 0 THEN '1' WHEN $productTable.status = -2 THEN '3' END) as status"),
                        ])->first();
                        if(!empty($productData)){
                            $url = env('NIDIN_PRODUCT_RETURN_URL');
                            $sendData = [
                                'merchant_no' => $productData->merchant_no,
                                'product'  => [
                                    'digiwin_no' => $productData->digiwin_no,
                                    'vendor_product_no' => $productData->vendor_product_no,
                                    'name' => $productData->name,
                                    'price' => $productData->price,
                                    'status' => $productData->status,
                                ],
                            ];
                            $sendData = json_encode($sendData,true);
                            $response = Http::withHeaders([
                                "Content-Type" => "application/json;charset=utf-8"
                            ])->send("POST", $url, [
                                "body" => $sendData
                            ]);
                        }
                    }
                }
            }
        }elseif($request->cate == 'change'){
            $airplane_days = $request->airplane_days;
            $hotel_days = $request->hotel_days;
            foreach($products as $product){
                $product->update(['airplane_days' => $airplane_days, 'hotel_days' => $hotel_days ]);
            }
        }elseif($request->cate == 'changeDate'){
            $message = null;
            $earliestDate = $request->earliestDate;
            $latestDate = $request->latestDate;
            $transStartDate = $request->trans_start_date;
            $transEndDate = $request->trans_end_date;
            if(!empty($latestDate) && !empty($earliestDate)){
                if(strtotime($earliestDate) > strtotime($latestDate)){
                    $message = "最快出貨日大於最晚出貨日。";
                }
            }
            if(!empty($transStartDate) && !empty($transEndDate)){
                if(strtotime($transStartDate) > strtotime($transEndDate)){
                    $message .= "特定轉倉開始日大於特定轉倉結束日。";
                }
            }
            if(!empty($message)){
                Session::put('error',$message);
            }else{
                foreach($products as $product){
                    $product->update(['vendor_earliest_delivery_date' => $earliestDate, 'vendor_latest_delivery_date' => $latestDate]);
                }
            }
        }
        return redirect()->back();
    }
}
