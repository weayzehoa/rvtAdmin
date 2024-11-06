<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use Auth;
use View;
use Storage;
use DB;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     * 進到這個控制器需要透過middleware檢驗是否為後台的使用者
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * 顯示 dashboard.
     * 並將 使用者的資料拋出
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $data = [];
        $data['orderNew'] = OrderDB::where('status',1)->count();
        $data['orderCollect'] = OrderDB::where('status',2)->where('is_del',0)->count();
        $data['productWait'] = ProductDB::where('status',0)->where('is_del',0)->count();
        $data['productStop'] = ProductDB::where('status',-3)->where('is_del',0)
        ->where(function($query){
            $query = $query->whereNull('pause_reason')->orWhere('pause_reason','');
        })->count();
        $data['productPause'] = ProductDB::where('status',-3)->where('is_del',0)
        ->where(function($query){
            $query = $query->whereNotNull('pause_reason')->orWhere('pause_reason','!=','');
        })->count();
        $data['productReplenishment'] = ProductDB::join('vendor','vendor.id','product.vendor_id')
            ->where('vendor.is_on',1)->whereIn('product.status',[1,-3])->whereIn('product.id',ProductModelDB::where('is_del',0)->where('quantity','<=',0)->select('product_id')->groupBy('product_id')->get())->where('product.is_del',0)->count();
        $data['productNeedReplenishment'] = ProductDB::join('vendor','vendor.id','product.vendor_id')
            ->where('vendor.is_on',1)->whereIn('product.status',[1,-3])->whereIn('product.id',ProductModelDB::where('is_del',0)->whereRaw('quantity < safe_quantity')->select('product_id')->groupBy('product_id')->get())->where('product.is_del',0)->count();
        return View::make('admin.dashboard',compact(['data']));
    }
}
