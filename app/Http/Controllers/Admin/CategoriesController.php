<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\CategoriesRequest;
use App\Http\Requests\Admin\CategoriesLangRequest;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryCategoryLang as CategoryLangDB;
use Auth;
use File;
use Storage;
use Spatie\Image\Image;
use Session;
use Carbon\Carbon;

class CategoriesController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menuCode = 'M4S4';
        $appends = $compact = [];

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        $categories = CategoryDB::orderBy('is_on','desc')->orderBy('sort_id','asc');

        if (!isset($list)) {
            $list = 30;
            $compact = array_merge($compact, ['list']);
        }

        //找出最終資料
        $categories = $categories->orderBy('is_on','desc')->paginate($list);

        $compact = array_merge($compact, ['menuCode','categories','appends']);
        return view('admin.products.category_index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M4S4';
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        return view('admin.products.category_show',compact('menuCode','langs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoriesRequest $request)
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
        return redirect()->route('admin.categories.show',$category->id)->withInput($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M4S4';
        $category = categoryDB::with('langs')->findOrFail($id);
        $category->logo ? $category->logo = env('AWS_FILE_URL').$category->logo : '';
        $category->cover  ? $category->cover = env('AWS_FILE_URL').$category->cover : '';
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        for($i=0;$i<count($langs);$i++){
            $getData = CategoryLangDB::where([['lang',$langs[$i]['code']],['category_id',$category->id]])->get()->toArray();
            foreach($getData as $langData){
                $langs[$i]['data'] = $langData;
            }
        }
        return view('admin.products.category_show',compact('menuCode','langs','category'));
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
    /*
        語言功能
     */
    public function lang(CategoriesLangRequest $request)
    {
        $data = $request->all();
        $data['category_id'] = $request->category_id;
        $langId = $request->langId;

        !empty($langId) ? CategoryLangDB::findOrFail($langId)->update($data) : CategoryLangDB::create($data);

        //回寫舊資料
        $category = CategoryDB::find($data['category_id']);
        if(!empty($category)){
            $category->update(['name_'.$data['lang'] => $data['name']]);
        }

        return redirect()->back();
    }
    /*
        圖檔上傳
     */
    public function upload(Request $request)
    {
        //檢查表單是否有檔案
        if(!$request->hasFile('cover') && !$request->hasFile('logo')){
            $message = "請選擇要上傳的檔案在按送出按鈕";
            Session::put('info',$message);
            return redirect()->back();
        }

        if($request->hasFile('cover')){
            $request->rowName = 'cover';
            $this->storeFile($request);
        }

        if($request->hasFile('logo')){
            $request->rowName = 'logo';
            $this->storeFile($request);
        }

        $message = "檔案上傳成功";
        Session::put('success',$message);
        return redirect()->back();
    }

    public function storeFile($request){
        //目的目錄
        $destPath = '/upload/category/';
        //檢查本地目錄是否存在，不存在則建立
        !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
        //檢查S3目錄是否存在，不存在則建立
        !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
        //實際檔案
        $file = $request->file($request->rowName);
        //副檔名
        $ext = $file->getClientOriginalExtension();
        //新檔名
        $fileName = $request->rowName.'_'.$request->id.'_'. Carbon::now()->timestamp . '.' . $ext;
        //變更尺寸寬高
        if($request->rowName == 'cover'){
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;
        }else{
            $reSizeWidth = 500;
            $reSizeHeigh = 500;
        }
        //檔案路徑名稱資料寫入資料庫
        CategoryDB::findOrFail($request->id)->update([$request->rowName => $destPath.$fileName]);
        //將檔案搬至本地目錄
        $file->move(public_path().$destPath, $fileName);
        //使用Spatie/image的套件Resize圖檔
        Image::load(public_path().$destPath.$fileName)
        ->width($reSizeWidth)
        ->height($reSizeHeigh)
        ->save(public_path().$destPath.$fileName);
        //將檔案傳送至 S3
        //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
        Storage::disk('s3')->put($destPath.$fileName, file_get_contents(public_path().$destPath.$fileName) , 'public');
        //刪除本地檔案
        unlink(public_path().$destPath.$fileName);
    }
}
