<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\iCarryShippingVendor as ShippingVendorDB;
use App\Http\Requests\Admin\ShippingVendorsRequest;
use Session;

class ShippingVendorsController extends Controller
{
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
        $menuCode = 'M3S1';
        $compact = $appends = [];
        $vendors = ShippingVendorDB::where('is_delete',0);

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

        isset($is_foreign) ? $vendors = $vendors->where('is_foreign',$is_foreign) : '';

        if(!empty($keyword)){
            $vendors = $vendors->where('name','like',"%$keyword%")
            ->orWhere('name_en','like',"%$keyword%");
        }

        $vendors = $vendors->orderBy('sort_id','asc')->paginate($list);

        $compact = array_merge($compact, ['menuCode','vendors','appends']);
        return view('admin.shippings.vendors_index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M3S1';
        return view('admin.shippings.vendors_show',compact('menuCode'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShippingVendorsRequest $request)
    {
        //整理資料
        $data = $request->all();
        // isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        isset($data['is_foreign']) ? $data['is_foreign'] = 1 : $data['is_foreign'] = 0;
        //新增後跳轉
        $newVendor = ShippingVendorDB::create($data);

        //重新排序
        $vendors = ShippingVendorDB::where('is_delete',0)->orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($vendors as $vendor) {
            ShippingVendorDB::where('id', $vendor->id)->update(['sort_id' => $i]);
            $i++;
        }

        $message = '物流廠商 '.$request->name.' 已建立成功！';
        Session::put('success', $message);
        return redirect()->route('admin.shippingvendors.show',$newVendor->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M3S1';
        $vendor = ShippingVendorDB::findOrFail($id);
        return view('admin.shippings.vendors_show', compact('menuCode','vendor'));
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
    public function update(ShippingVendorsRequest $request, $id)
    {
        $data = $request->all();
        $data['is_foreign'] ?? $data['is_foreign'] = 0;
        ShippingVendorDB::findOrFail($id)->update($data);
        $message = '物流廠商 '.$request->name.' 修改成功！';
        Session::put('success', $message);
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
        $vendor = ShippingVendorDB::findOrFail($id)->update(['is_delete' => 1]);
        //重新排序
        $vendors = ShippingVendorDB::where('is_delete',0)->orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($vendors as $vendor) {
            ShippingVendorDB::where('id', $vendor->id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->back();
    }

    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $vendor = ShippingVendorDB::findOrFail($id);
        $up = ($vendor->sort) - 1.5;
        $vendor->fill(['sort_id' => $up]);
        $vendor->save();

        $vendors = ShippingVendorDB::where('is_delete',0)->orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($vendors as $vendor) {
            $id = $vendor->id;
            ShippingVendorDB::where('id', $id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->back();
    }
    /*
        向下排序
    */
    public function sortdown(Request $request)
    {
        $id = $request->id;
        $vendor = ShippingVendorDB::findOrFail($id);
        $up = ($vendor->sort) + 1.5;
        $vendor->fill(['sort_id' => $up]);
        $vendor->save();

        $vendors = ShippingVendorDB::where('is_delete',0)->orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($vendors as $vendor) {
            $id = $vendor->id;
            ShippingVendorDB::where('id', $id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->back();
    }
}
