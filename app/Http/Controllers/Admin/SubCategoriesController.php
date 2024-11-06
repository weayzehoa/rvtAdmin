<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarrySubCategory as CategoryDB;
use App\Models\iCarryCategory as MainCategoryDB;
use App\Http\Requests\Admin\SubCategoriesRequest;

class SubCategoriesController extends Controller
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
        $menuCode = 'M4S6';
        $appends = $compact = [];

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        $subCategories = CategoryDB::with('mainCate')->orderBy('sort_id','asc');

        if (!isset($list)) {
            $list = 30;
            $compact = array_merge($compact, ['list']);
        }

        //找出最終資料
        $subCategories = $subCategories->orderBy('is_on','desc')->paginate($list);

        $compact = array_merge($compact, ['menuCode','subCategories','appends']);
        return view('admin.products.sub_category_index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M4S6';
        $mainCategories = MainCategoryDB::orderBy('is_on','desc')->get();
        return view('admin.products.sub_category_show',compact('menuCode','mainCategories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SubCategoriesRequest $request)
    {
        $data = $request->all();
        $data['is_on'] ?? $data['is_on'] = 0;
        $category = CategoryDB::create($data);
        //重新排序
        $categorys = categoryDB::orderBy('sort_id','ASC')->get();
        $i = 0;
        foreach ($categorys as $category) {
            $id = $category->id;
            categoryDB::where('id', $id)->update(['sort_id' => $i+1]);
            $i++;
        }
        return redirect()->route('admin.subCategories.show',$category->id)->withInput($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M4S6';
        $category = categoryDB::findOrFail($id);
        $mainCategories = MainCategoryDB::orderBy('is_on','desc')->get();
        return view('admin.products.sub_category_show',compact('menuCode','category','mainCategories'));
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
    public function update(SubCategoriesRequest $request, $id)
    {
        $data = $request->all();
        $data['is_on'] ?? $data['is_on'] = 0;
        $category = CategoryDB::findOrFail($id)->update($data);
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
        $category = categoryDB::findOrFail($id);
        $up = ($category->sort_id) - 1.5;
        $category->fill(['sort_id' => $up]);
        $category->save();

        $categorys = categoryDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($categorys as $category) {
            $id = $category->id;
            categoryDB::where('id', $id)->update(['sort_id' => $i]);
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
        $category = categoryDB::findOrFail($id);
        $up = ($category->sort_id) + 1.5;
        $category->fill(['sort_id' => $up]);
        $category->save();

        $categorys = categoryDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($categorys as $category) {
            $id = $category->id;
            categoryDB::where('id', $id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->back();
    }
    /*
        啟用或禁用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        categoryDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }
}
