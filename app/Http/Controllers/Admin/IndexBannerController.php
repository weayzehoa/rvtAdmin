<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryIndexBanner as IndexBannerDB;
use App\Http\Requests\Admin\indexBannerRequest;
use App\Http\Requests\Admin\indexBannerCreateRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;
class IndexBannerController extends Controller
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
        $menuCode = 'M7S8';
        $appends = $compact = [];
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }
        //找出資料
        $indexBanners = IndexBannerDB::orderBy('sort_id','asc');
        if (isset($keyword) && $keyword) {
            $indexBanners = $indexBanners->where('title', 'like', "%$keyword%");
        }
        if (!isset($list)) {
            $list = 15;
            $compact = array_merge($compact, ['list']);
        }
        $indexBanners = $indexBanners->paginate($list);
        $compact = array_merge($compact, ['menuCode','indexBanners','appends']);
        return view('admin.indexbanners.index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M7S8';
        return view('admin.indexbanners.show',compact(['menuCode']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(indexBannerCreateRequest $request)
    {
        $data = $request->all();
        $data['sort_id'] = 999;
        $indexBanner = IndexBannerDB::create($data);
        $id = $indexBanner->id;
        if($request->hasFile('img_mobile')){
            $data['img_mobile'] = $this->storeImageFile('img_mobile', $request, $indexBanner);
        }
        if($request->hasFile('img_desktop')){
            $data['img_desktop'] = $this->storeImageFile('img_desktop', $request, $indexBanner);
        }
        //重新排序
        $indexBanners = IndexBannerDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($indexBanners as $indexBanner) {
            IndexBannerDB::where('id', $indexBanner->id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->route('admin.indexBanners.show', $id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M7S8';
        $indexBanner = IndexBannerDB::findOrFail($id);
        return view('admin.indexbanners.show', compact(['indexBanner','menuCode']));
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
    public function update(indexBannerRequest $request, $id)
    {
        $data = $request->all();
        isset($data['is_on']) ? '' : $data['is_on'] = 0;
        $indexBanner = IndexBannerDB::findOrFail($id);
        $indexBanner->update($data);
        if($request->hasFile('img_mobile')){
            $data['img_mobile'] = $this->storeImageFile('img_mobile', $request, $indexBanner);
        }
        if($request->hasFile('img_desktop')){
            $data['img_desktop'] = $this->storeImageFile('img_desktop', $request, $indexBanner);
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
        $indexBanner = IndexBannerDB::findOrFail($id);
        !empty($indexBanner) ? $indexBanner->delete() : '';
        return redirect()->back();
    }
    /*
        啟用或禁用
    */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        IndexBannerDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }
        /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $indexBanner = IndexBannerDB::findOrFail($id);
        $up = ($indexBanner->sort_id) - 1.5;
        $indexBanner->update(['sort_id' => $up]);
        $indexBanners = IndexBannerDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($indexBanners as $indexBanner) {
            $id = $indexBanner->id;
            IndexBannerDB::where('id', $id)->update(['sort_id' => $i]);
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
        $indexBanner = IndexBannerDB::findOrFail($id);
        $up = ($indexBanner->sort_id) + 1.5;
        $indexBanner->update(['sort_id' => $up]);
        $indexBanners = IndexBannerDB::orderBy('sort_id','ASC')->get();
        $i = 1;
        foreach ($indexBanners as $indexBanner) {
            $id = $indexBanner->id;
            IndexBannerDB::where('id', $id)->update(['sort_id' => $i]);
            $i++;
        }
        return redirect()->back();
    }

    public function storeImageFile($columnName, $request, $indexBanner){
        if(!empty($columnName) && $request->hasFile($columnName)){
            //目的目錄
            $destPath = '/upload/index_banner/';
            //檢查本地目錄是否存在，不存在則建立
            !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
            //檢查S3目錄是否存在，不存在則建立
            !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
            //實際檔案
            $file = $request->file($columnName);
            //副檔名
            $ext = $file->getClientOriginalExtension();
            //新檔名
            $fileName = 'indexBanner_'.$columnName.'_'.$indexBanner->id.'_'. Carbon::now()->timestamp. '.' . $ext;
            //變更尺寸寬高
            if($columnName == 'img_desktop'){
                $reSizeWidth = 2480;
                $reSizeHeigh = 354;
            }else{
                $reSizeWidth = 1280;
                $reSizeHeigh = 800;
            }
            $originFileName = $columnName.'_originFileName.'.$ext;
            //將檔案搬至本地目錄
            $file->move(public_path().$destPath, $originFileName);
            //使用Spatie/image的套件Resize圖檔
            Image::load(public_path().$destPath.$originFileName)
            ->width($reSizeWidth)
            ->height($reSizeHeigh)
            ->save(public_path().$destPath.$fileName);
            //將檔案傳送至 S3
            //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
            Storage::disk('s3')->put($destPath.$fileName, file_get_contents(public_path().$destPath.$fileName) , 'public');
            //刪除本地檔案
            unlink(public_path().$destPath.$originFileName);
            unlink(public_path().$destPath.$fileName);
            $indexBanner->update([$columnName => $destPath.$fileName]);
            return $destPath.$fileName;
        }
        return null;
    }
}
