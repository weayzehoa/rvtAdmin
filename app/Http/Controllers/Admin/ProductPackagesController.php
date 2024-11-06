<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryCountry as CountryDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProductLang as ProductLangDB;
use App\Models\iCarryProductQuantityRecord as ProductQuantityRecordDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryProductPackage as ProductPackageDB;
use App\Models\iCarryProductPackageList as ProductPackageListDB;
use App\Models\iCarryProductImage as ProductImageDB;
use App\Models\iCarryProductUnitName as ProductUnitNameDB;
use App\Models\iCarryShippingMethod as ShippingMethodDB;
use App\Http\Requests\Admin\ProductsRequest;
use App\Http\Requests\Admin\ProductsLangRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;
use App\Exports\ProductPackagesExport;
use Maatwebsite\Excel\Facades\Excel;
use DB;
use App\Traits\ProductFunctionTrait;

class ProductPackagesController extends Controller
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
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menuCode = 'M4S2';
        $ctime = microtime(true); //紀錄開始時間
        $appends = $compact = [];
        $status = $vendorId = $categoryId = '';
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $productPackageTable = env('DB_ICARRY').'.'.(new ProductPackageDB)->getTable();

        $categories = CategoryDB::orderBy('is_on','desc')->get();
        $products = $this->getProductPackageData(request(),'index');

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

        foreach($products as $product){
            $product->packages = ProductPackageDB::with('lists')
            ->join($productModelTable,$productModelTable.'.id',$productPackageTable.'.product_model_id')
            ->where($productPackageTable.'.product_id',$product->id)
            ->select([
                $productPackageTable.'.*',
                $productModelTable.'.name',
                $productModelTable.'.sku',
                $productModelTable.'.digiwin_no',
                $productModelTable.'.quantity',
                $productModelTable.'.safe_quantity',
            ])->get();
        }

        $vendors = ProductModelDB::join($productTable,$productTable.'.id',$productModelTable.'.product_id')
        ->join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
        ->where($productModelTable.'.sku','like',"%BOM%")
        ->where($productTable.'.is_del',0)
        ->select([
            $vendorTable.'.*',
        ])->distinct()->orderBy($vendorTable.'.is_on','desc')->get();

        // ProductDB::whereNotNull('package_data')->join('vendor', 'product.vendor_id', '=', 'vendor.id')->select('vendor.name as name', 'vendor.id as id', 'vendor.is_on', 'vendor.categories')->groupBy('product.id')->orderBy('vendor.is_on','desc')->get();
        $compact = array_merge($compact, ['menuCode','products','categories','vendors','categoryId','vendorId','status','appends']);
        return view('admin.products.package_index',compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    /*
        匯出功能
    */
    public function export()
    {
        $exportFile = '組合商品資料匯出_'.date('YmdHis').'.xlsx';
        return Excel::download(new ProductPackagesExport, $exportFile);
    }
}
