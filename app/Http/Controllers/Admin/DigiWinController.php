<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DigiwinVendorsExport;
use App\Imports\DigiwinLogisticsImport;
use App\Exports\DigiwinLogisticsExport;
use App\Imports\DigiwinEc2NoImport;
use App\Exports\DigiwinEc2NoExport;
use Session;
use App\Models\Product as ProductDB;
use App\Models\Category as CategoryDB;
use App\Models\Vendor as VendorDB;
use App\Models\ProductModel as ProductModelDB;
use App\Models\ProductUpdateRecord as ProductUpdateRecordDB;

class DigiWinController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    public function logistic()
    {
        return view('admin.digiwin.logistic');
    }

    public function ec2no()
    {
        return view('admin.digiwin.ec2no');
    }

    public function product293()
    {
        $menuCode = 'M4S1';
        $appends = [];
        $compact = [];
        $vendorId = '';
        $categories = CategoryDB::orderBy('is_on','desc')->get();
        $vendors = VendorDB::orderBy('is_on','desc')->orderBy('id','desc')->get();
        $products = ProductDB::with('models')->join('vendors','vendors.id','products.vendor_id');
        $products = $products->where(function ($query) {
            $query->where('products.vendor_id', 239)->orWhere('products.name','like',"%短效品%");
        });
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            $appends = array_merge($appends, [$key => $value]);
            $compact = array_merge($compact, [$key]);
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }
        if(!empty($status)){
            $st = explode(',',$status);
            $products = $products->whereIn('status',$st);
        }
        if(!empty($shipping_methods)){
            $shipping_methods = ltrim(rtrim($shipping_methods,','),','); //去除左右邊逗點
            $sm = explode(',',$shipping_methods);
            $c = 'shipping_methods is null ';
            for($i=0;$i<count($sm);$i++){
                $c .= " OR FIND_IN_SET('$sm[$i]',shipping_methods) ";
            }
            $products = $products->where(function($query)use($c){
                $query->whereRaw($c);
            });
        }

        !empty($digiwin_no) ? $products = $products->whereIn('products.id',ProductModelDB::where('digiwin_no','like',"%$digiwin_no%")->select('product_id')->groupBy('product_id')->get()) : '';

        !empty($sku) ? $products = $products->whereIn('products.id',ProductModelDB::where('sku','like',"%$sku%")->select('product_id')->groupBy('product_id')->get()) : '';

        !empty($low_quantity) ? $products = $products->whereIn('products.id',ProductModelDB::whereRaw('product_models.quantity < product_models.safe_quantity')->select('product_id')->groupBy('product_id')->get()) : '';
        !empty($zero_quantity) ? $products = $products->whereIn('products.id',ProductModelDB::whereRaw('product_models.quantity <= 0')->select('product_id')->groupBy('product_id')->get()) : '';

        !empty($product_name) ? $products = $products->where('products.name', 'like', "%$product_name%") : '';

        !empty($created_at) ? $products = $products->where('products.created_at', '>=', $created_at) : '';
        !empty($created_at_end) ? $products = $products->where('products.created_at', '<=', $created_at_end) : '';

        !empty($pass_time) ? $products = $products->where('products.pass_time', '>=', $pass_time) : '';
        !empty($pass_time_end) ? $products = $products->where('products.pass_time', '<=', $pass_time_end) : '';

        !empty($price_update_time) ? $products = $products->whereIn('products.id', ProductUpdateRecordDB::where([['column','price'],['created_at','>=',$price_update_time]])->select('product_id')->groupBy('product_id')->get()) : '';
        !empty($price_update_time_end) ? $products = $products->whereIn('products.id', ProductUpdateRecordDB::where([['column','price'],['created_at','<=',$price_update_time_end]])->select('product_id')->groupBy('product_id')->get()) : '';

        !empty($storage_life_update_time) ? $products = $products->whereIn('products.id', ProductUpdateRecordDB::where([['column','storage_life'],['created_at','>=',$storage_life_update_time]])->select('product_id')->groupBy('product_id')->get()) : '';
        !empty($storage_life_update_time_end) ? $products = $products->whereIn('products.id', ProductUpdateRecordDB::where([['column','storage_life'],['created_at','<=',$storage_life_update_time_end]])->select('product_id')->groupBy('product_id')->get()) : '';

        !empty($gtin13_update_time) ? $products = $products->whereIn('products.id', ProductUpdateRecordDB::where([['column','gtin13'],['created_at','>=',$gtin13_update_time]])->select('product_id')->groupBy('product_id')->get()) : '';
        !empty($gtin13_update_time_end) ? $products = $products->whereIn('products.id', ProductUpdateRecordDB::where([['column','gtin13'],['created_at','<=',$gtin13_update_time_end]])->select('product_id')->groupBy('product_id')->get()) : '';

        $products = $products->select(
            'vendors.id as vendor_id',
            'vendors.name as vendor_name',
            'products.id',
            'products.category_id',
            'products.name',
            'products.model_type',
            'products.status',
            'products.serving_size',
            'products.price',
            'products.created_at',
            'products.updated_at',
            'products.pass_time',
        )->orderBy('id','desc')->paginate($list);
        $compact = array_merge($compact, ['menuCode','products','list','appends','vendorId','categories','vendors']);
        return view('admin.digiwin.product239',compact($compact));
    }

    public function vendor()
    {
        return view('admin.digiwin.vendor');
    }

    public function vendorsExport()
    {
        $exportFile = '鼎新資料處理_供應商匯出_'.date('YmdHis').'.xlsx';
        return Excel::download(new DigiwinVendorsExport, $exportFile);
    }

    public function logisticImport(Request $request)
    {
        if ($request->hasFile('filename')) {
            $file = $request->file('filename');
            $uploadedFileMimeType = $file->getMimeType();
            $mimes = array('application/excel','application/vnd.ms-excel','application/vnd.msexcel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/CDFV2','application/octet-stream');
            if(in_array($uploadedFileMimeType, $mimes)){
                $result = Excel::toArray(new DigiwinLogisticsImport, $file);
                if(count($result[0][0]) == 4){
                    $exportFile = '鼎新資料處理_物流單號匯入匯出_'.date('YmdHis').'.xlsx';
                    return Excel::download(new DigiwinLogisticsExport($result[0]), $exportFile);
                }else{
                    $message = '檔案欄位數錯誤，請檢查檔案內容是否符合範例';
                    Session::put('error', $message);
                    return redirect()->back();
                }
            } else{
                $message = '只接受 xls 或 xlsx 檔案格式';
                Session::put('error', $message);
                return redirect()->back();
            }
        }
        return redirect()->back();
    }

    public function ec2noImport(Request $request)
    {
        if ($request->hasFile('filename')) {
            $file = $request->file('filename');
            $uploadedFileMimeType = $file->getMimeType();
            $mimes = array('application/excel','application/vnd.ms-excel','application/vnd.msexcel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/CDFV2','application/octet-stream');
            if(in_array($uploadedFileMimeType, $mimes)){
                $result = Excel::toArray(new DigiwinEc2NoImport, $file);
                if(count($result[0][0]) == 2){
                    if($result[0][0][0] == 'iCarry 貨號' && $result[0][0][1] == '鼎新貨號'){
                        $exportFile = '鼎新資料處理_商品貨號轉換_'.date('YmdHis').'.xlsx';
                        return Excel::download(new DigiwinEc2NoExport($result[0]), $exportFile);
                    }else{
                        $message = '檔案欄位名稱錯誤，請檢查檔案內容是否符合範例';
                    }
                }else{
                    $message = '檔案欄位數錯誤，請檢查檔案內容是否符合範例';
                }
            } else{
                $message = '只接受 xls 或 xlsx 檔案格式';
            }
            Session::put('error', $message);
            return redirect()->back();
        }
        return redirect()->back();
    }
}
