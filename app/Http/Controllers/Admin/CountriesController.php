<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryCountry as CountryDB;
use App\Models\GateSystemSetting as SystemSettingDB;
use Session;

class CountriesController extends Controller
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
        $menuCode = 'M1S3';
        request()->has('list') ? $list = (INT)urldecode(request()->list) : $list = 50;
        request()->has('keyword') ? $keyword = urldecode(request()->keyword) : $keyword = '';
        if($keyword){
            // $countries = CountryDB::search($keyword)->orderBy('sort','asc')->paginate($list);
            // $totals = count(CountryDB::search($keyword)->orderBy('sort','asc')->get());
            $countries = CountryDB::where('name','like',"%$keyword%")
                        ->orWhere('name_en','like',"%$keyword%")
                        ->orWhere('lang','like',"%$keyword%")
                        ->orWhere('code','like',"%$keyword%")
                        ->orderBy('sort','asc')->paginate($list);
            $totals = CountryDB::where('name','like',"%$keyword%")
                                ->orWhere('name_en','like',"%$keyword%")
                                ->orWhere('lang','like',"%$keyword%")
                                ->orWhere('code','like',"%$keyword%")
                                ->get();
            $totals = count($totals);
        }else{
            $countries = CountryDB::orderBy('sort','asc')->paginate($list);
            $totals = CountryDB::orderBy('sort','asc')->count();
        }
        $mitakePoints = SystemSettingDB::find(1)->mitake_points;
        return view('admin.countries.index', compact('countries','menuCode','totals','list','keyword','mitakePoints'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M1S3';
        $mitakePoints = SystemSettingDB::find(1)->mitake_points;
        return view('admin.countries.show', compact('menuCode','mitakePoints'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        !empty($data['code']) && $data['code'] != 'o' && is_numeric(str_replace('+','',$data['code'])) ? $data['code'] = '+'.str_replace('+','',$data['code']) : '';
        $country = CountryDB::where('name',$data['name'])->orWhere('lang',$data['lang'])->orWhere('code',$data['code'])->first();
        if(!empty($country)){
            Session::put('error',"$country->name 已經存在。");
            return redirect()->back()->withInput($request->all());
        }else{
            $country = CountryDB::create($data);
            //重新排序
            $countries = CountryDB::orderBy('sort','ASC')->get();
            $i = 1;
            foreach ($countries as $sort) {
                $id = $sort->id;
                CountryDB::where('id', $id)->update(['sort' => $i]);
                $i++;
            }
            return redirect()->route('admin.countries.show',$country->id);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M1S3';
        $country = CountryDB::findOrFail($id);
        $mitakePoints = SystemSettingDB::find(1)->mitake_points;
        return view('admin.countries.show', compact('country','menuCode','mitakePoints'));
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
        $data = $request->all();
        !empty($data['code']) && $data['code'] != 'o' && is_numeric(str_replace('+','',$data['code'])) ? $data['code'] = '+'.str_replace('+','',$data['code']) : '';
        $country = CountryDB::findOrFail($id);

        if($data['name'] == $country->name && $data['code'] == $country->code && $data['lang'] == $country->lang ){
            $country->update($data);
        }else{
            $chkCountry = CountryDB::where('name',$data['name'])->orWhere('lang',$data['lang'])->orWhere('code',$data['code'])->first();
            if(!empty($chkCountry)){
                Session::put('error',"國家名稱/國家代碼/國際碼其中一個已經存在。(三者必須唯一)");
                return redirect()->back()->withInput($request->all());
            }else{
                $country->update($data);
            }
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
        $country = CountryDB::findOrFail($id);
        if(!empty($country)){
            $country->delete();
            //重新排序
            $countries = CountryDB::orderBy('sort','ASC')->get();
            $i = 1;
            foreach ($countries as $sort) {
                $id = $sort->id;
                CountryDB::where('id', $id)->update(['sort' => $i]);
                $i++;
            }
        }
        return redirect()->back();
    }
    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $country = CountryDB::findOrFail($id);
        $up = ($country->sort) - 1.5;
        $country->fill(['sort' => $up]);
        $country->save();

        $countries = CountryDB::orderBy('sort','ASC')->get();
        $i = 1;
        foreach ($countries as $country) {
            $id = $country->id;
            CountryDB::where('id', $id)->update(['sort' => $i]);
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
        $country = CountryDB::findOrFail($id);
        $up = ($country->sort) + 1.5;
        $country->fill(['sort' => $up]);
        $country->save();

        $countries = CountryDB::orderBy('sort','ASC')->get();
        $i = 1;
        foreach ($countries as $country) {
            $id = $country->id;
            CountryDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
        return redirect()->back();
    }
}
