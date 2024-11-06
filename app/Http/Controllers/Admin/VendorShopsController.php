<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendorShop as VendorShopDB;
use App\Http\Requests\Admin\VendorShopsRequest;
use Validator;
use Session;
use App\Traits\VendorFunctionTrait;

class VendorShopsController extends Controller
{
    use VendorFunctionTrait;
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
        $compact = $appends = [];
        $menuCode = 'M2S2';
        $shops = $this->getVendorShopData(request(),'index');
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
        $totalShops = VendorShopDB::get()->count();
        $totalEnable = VendorShopDB::where('is_on',1)->get()->count();
        $totalDisable = VendorShopDB::where('is_on',0)->get()->count();
        $compact = array_merge($compact, ['menuCode','shops','totalShops','totalEnable','totalDisable','appends']);
        return view('admin.vendors.shops_index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M2S2';
        if(request()->has('vendor_id')){
            $vendorId = (INT)urldecode(request()->vendor_id);
        }else{
            return redirect()->back();
        }
        return view('admin.vendors.shops_show', compact('vendorId','menuCode'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //整理資料
        $data = $request->all();
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        //新增後跳轉
        $shop = VendorShopDB::create($data);
        $message = '商家分店 '.$request->name.' 已建立成功！';
        Session::put('success', $message);
        Session::put('vendorShopShow','show active');
        return redirect()->route('admin.vendors.show',$data['vendor_id'].'#vendor_shop');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        request()->has('from') ? $from = urlencode(request()->from) : $from = '';
        $shop = VendorShopDB::findOrFail($id);
        $menuCode = 'M2S2';
        return view('admin.vendors.shops_show', compact('menuCode','shop','from'));
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
    public function update(VendorShopsRequest $request, $id)
    {
        $data = $request->all();
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        VendorShopDB::findOrFail($id)->update($data);

        if(request()->from){
            return redirect()->route('admin.vendors.show',$request->vendor_id.'#'.$request->from);
        }
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $shop = VendorShopDB::find($id)->delete();
        if(request()->has('from')){
            return redirect()->route('admin.vendors.show',request()->vendor_id.request()->from);
        }
        return redirect()->back();
    }
    /*
        啟用或停用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = 1 : $is_on = 0;
        VendorShopDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        if(request()->has('from')){
            return redirect()->route('admin.vendors.show',request()->vendor_id.request()->from);
        }
        return redirect()->back();
    }
}
