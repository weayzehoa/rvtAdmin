<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\iCarryShippingFee as ShippingFeeDB;
use App\Models\ShippingLocal as ShippingLocalDB;
use App\Models\iCarryCountry as CountryDB;
use App\Http\Requests\Admin\ShippingFeesRequest;
use Session;
use DB;

class ShippingFeesController extends Controller
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
        $menuCode = 'M3S2';
        $appends = $compact = [];

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        $fees = ShippingFeeDB::orderBy('product_sold_country','asc')->orderBy('is_on','desc');
        isset($is_on) ? $fees = $fees->where('is_on', $is_on) : '';
        !empty($product_sold_country) ? $fees = $fees->where('product_sold_country', $product_sold_country) : '';
        !empty($shipping_methods) ? $fees = $fees->where('shipping_methods', $shipping_methods) : '';

        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }

        $totals = $fees->count();
        $fees = $fees->paginate($list);

        $origins = ShippingFeeDB::select('product_sold_country')->groupBy('product_sold_country')->orderBy('product_sold_country','desc')->get();
        $countries = CountryDB::all();

        $compact = array_merge($compact, ['menuCode','fees','appends','countries','origins','totals']);

        return view('admin.shippings.fees_index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M3S2';
        $countries = CountryDB::all();
        $origins = ShippingFeeDB::select('product_sold_country')->groupBy('product_sold_country')->orderBy('product_sold_country','desc')->get();
        return view('admin.shippings.fees_show',compact('menuCode','countries','origins'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ShippingFeesRequest $request)
    {
        $data = $request->all();
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        $data['shipping_type'] == 'base' ? $data['shipping_base_price'] = $data['price'] : '';
        $data['shipping_type'] == 'base' ? $data['shipping_kg_price'] = 0 : '';
        $data['shipping_type'] == 'kg' ? $data['shipping_kg_price'] = $data['price'] : '';
        $data['shipping_type'] == 'kg' ? $data['shipping_base_price'] = 0 : '';
        $find = ShippingFeeDB::where([['product_sold_country',$request->product_sold_country],['shipping_methods',$request->shipping_methods]])->first();
        if(empty($find)){
            $fee = ShippingFeeDB::create($data);
            $message = '物流運費 '.$request->name.' 已建立成功！';
            Session::put('success', $message);
            return redirect()->route('admin.shippingfees.show',$fee->id);
        }else{
            $message = '該設定已經存在';
            Session::put('error', $message);
            return redirect()->back()->withInput($request->all());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ShippingFee  $shippingFee
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M3S2';
        $fee = ShippingFeeDB::findOrFail($id);
        $countries = CountryDB::all();
        $origins = ShippingFeeDB::select('product_sold_country')->groupBy('product_sold_country')->orderBy('product_sold_country','desc')->get();
        return view('admin.shippings.fees_show', compact('menuCode','fee','countries','origins'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ShippingFee  $shippingFee
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
     * @param  \App\Models\ShippingFee  $shippingFee
     * @return \Illuminate\Http\Response
     */
    public function update(ShippingFeesRequest $request, $id)
    {
        $data = $request->all();
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        $data['shipping_type'] == 'base' ? $data['shipping_base_price'] = $data['price'] : '';
        $data['shipping_type'] == 'base' ? $data['shipping_kg_price'] = 0 : '';
        $data['shipping_type'] == 'kg' ? $data['shipping_kg_price'] = $data['price'] : '';
        $data['shipping_type'] == 'kg' ? $data['shipping_base_price'] = 0 : '';
        ShippingFeeDB::findOrFail($id)->update($data);
        $message = '物流運費 '.$request->name.' 修改成功！';
        Session::put('success', $message);
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ShippingFee  $shippingFee
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $fee = ShippingFeeDB::findOrFail($id);
        $fee->delete();
        return redirect()->back();
    }

    /*
        啟用或禁用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = 1 : $is_on = 0;
        ShippingFeeDB::findOrFail($request->id)->update(['is_on' => $is_on]);
        return redirect()->back();
    }
}
