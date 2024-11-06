<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryProductUnitName as ProductUnitNameDB;
use App\Http\Requests\Admin\ProductUnitNamesRequest;

class ProductUnitNamesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menuCode = 'M4S3';
        $unitNames = ProductUnitNameDB::orderBy('sort_id','asc')->get();
        return view('admin.products.unitname_index',compact('menuCode','unitNames'));
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
    public function store(ProductUnitNamesRequest $request)
    {
        $data = $request->all();
        $check = ProductUnitNameDB::where('name',$data['name'])->first();
        if($check){
            return redirect()->back()->withInput($request->nmae)->withErrors(['message' => '該名稱已經存在']);
        }
        ProductUnitNameDB::create($data);
        //重新排序
        $unitNames = ProductUnitNameDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($unitNames as $unitName) {
            $id = $unitName->id;
            ProductUnitNameDB::where('id', $id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->back();
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
    public function update(ProductUnitNamesRequest $request, $id)
    {
        $data = $request->all();
        ProductUnitNameDB::findOrFail($id)->update($data);
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
        //
    }
    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $unitName = ProductUnitNameDB::findOrFail($id);
        $up = ($unitName->sort_id) - 1.5;
        $unitName->fill(['sort_id' => $up]);
        $unitName->save();

        $unitNames = ProductUnitNameDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($unitNames as $unitName) {
            $id = $unitName->id;
            ProductUnitNameDB::where('id', $id)->update(['sort_id' => $i]);
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
        $unitName = ProductUnitNameDB::findOrFail($id);
        $up = ($unitName->sort_id) + 1.5;
        $unitName->fill(['sort_id' => $up]);
        $unitName->save();

        $unitNames = ProductUnitNameDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($unitNames as $unitName) {
            $id = $unitName->id;
            ProductUnitNameDB::where('id', $id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->back();
    }
}
