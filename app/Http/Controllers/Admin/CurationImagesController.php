<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryOldCuration as OldCurationDB;
use App\Models\iCarryCurationImage as CurationImageDB;
use App\Models\iCarryCurationImageLang as CurationImageLangDB;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;

class CurationImagesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 先經過 middleware 檢查
        $this->middleware(['auth:admin','optimizeImages']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {
        $data = $request->all();
        $curation = CurationDB::findOrFail($data['curation_id']);
        isset($data['url_open_window']) ? $data['url_open_window'] = 1 : $data['url_open_window'] = 0;
        isset($data['show_main_title']) ? $data['show_main_title'] = 1 : $data['show_main_title'] = 0;
        isset($data['show_sub_title']) ? $data['show_sub_title'] = 1 : $data['show_sub_title'] = 0;
        isset($data['url']) ? $data['old_url'] = $data['url'] : '';
        $curationImage = CurationImageDB::create($data);
        $curation->category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        if($data['style'] != 'nowordblock'){
            $this->lang($curationImage->id,$data['langs']);
        }
        if($request->hasFile('image')){
            $request->id = $curationImage->id;
            $request->rowName = 'image';
            $data['image']=$this->storeFile($request);
            $curationImage->update($data);
        }
        $this->sortByStyle($data['style'],$curation->id);
        //找出圖片策展資料 並更新到舊的策展裡
        if($curation->category == 'home' && $curation->type == 'image'){
            $photoData = [];
            $oldCuration = OldCurationDB::find($curation->old_curation_id);
            $curationImages = CurationImageDB::with('langs')->where('curation_id',$curationImage->curation_id)->orderBy('sort','asc')->get();
            if(!empty($oldCuration)){
                foreach($curationImages as $image){
                    $photoImageData1 = [
                        'photo_main_title' => !empty($image->main_title) ? $image->main_title : '',
                        'photo_subtitle' => !empty($image->sub_title) ? $image->sub_title : '',
                    ];
                    $photoLangData = [];
                    foreach($image->langs as $lang){
                        $photoLang = [
                            'photo_main_title_'.$lang->lang => !empty($lang->main_title) ? $lang->main_title : '',
                            'photo_subtitle_'.$lang->lang => !empty($lang->sub_title) ? $lang->sub_title : '',
                        ];
                        $photoLangData = array_merge($photoLangData,$photoLang);
                    }
                    $photoImageData2 = [
                        'photo_url' => !empty($image->url) ? $image->url : '',
                        'photo' => !empty($image->image) ? $image->image : '',
                    ];
                    $photoData[] = array_merge($photoImageData1,$photoLangData,$photoImageData2);
                }
                $oldCuration->update(['photo_data' => json_encode($photoData)]);
            }
        }
        return redirect()->route('admin.'.$returnUrl.'.show',$data['curation_id'].'#'.$data['style']);
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
    public function update(Request $request, $id)
    {
        $data = $request->all();
        isset($data['url_open_window']) ? $data['url_open_window'] = 1 : $data['url_open_window'] = 0;
        isset($data['show_main_title']) ? $data['show_main_title'] = 1 : $data['show_main_title'] = 0;
        isset($data['show_sub_title']) ? $data['show_sub_title'] = 1 : $data['show_sub_title'] = 0;
        isset($data['url']) ? $data['old_url'] = $data['url'] : '';
        $curationImage = CurationImageDB::with('curation')->findOrFail($id);
        $curation = $curationImage->curation;
        $curation->category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        //宮格無文字不需要存語言
        if($data['style'] != 'nowordblock'){
            $this->lang($curationImage->id,$data['langs']);
        }
        //圖片處理
        if($request->hasFile('image')){
            $request->id = $id;
            $request->rowName = 'image';
            $data['image']=$this->storeFile($request);
        }
        $curationImage = $curationImage->update($data);
        $this->sortByStyle($data['style'],$curation->id);
        //找出圖片策展資料 並更新到舊的策展裡
        if($curation->category == 'home' && $curation->type == 'image'){
            $photoData = [];
            $oldCuration = OldCurationDB::find($curation->old_curation_id);
            $curationImages = CurationImageDB::with('langs')->where('curation_id',$curation->id)->orderBy('sort','asc')->get();
            if(!empty($oldCuration)){
                foreach($curationImages as $image){
                    $photoImageData1 = [
                        'photo_main_title' => !empty($image->main_title) ? $image->main_title : '',
                        'photo_subtitle' => !empty($image->sub_title) ? $image->sub_title : '',
                    ];
                    $photoLangData = [];
                    foreach($image->langs as $lang){
                        $photoLang = [
                            'photo_main_title_'.$lang->lang => !empty($lang->main_title) ? $lang->main_title : '',
                            'photo_subtitle_'.$lang->lang => !empty($lang->sub_title) ? $lang->sub_title : '',
                        ];
                        $photoLangData = array_merge($photoLangData,$photoLang);
                    }
                    $photoImageData2 = [
                        'photo_url' => !empty($image->url) ? $image->url : '',
                        'photo' => !empty($image->image) ? $image->image : '',
                    ];
                    $photoData[] = array_merge($photoImageData1,$photoLangData,$photoImageData2);
                }
                $oldCuration->update(['photo_data' => json_encode($photoData)]);
            }
        }
        return redirect()->route('admin.'.$returnUrl.'.show',$data['curation_id'].'#'.$data['style']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $curationImage = CurationImageDB::with('curation')->findOrFail($id);
        $curation = $curationImage->curation;
        $curation->category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        $curationImageLangs = CurationImageLangDB::where('curation_image_id',$curationImage->id)->delete();
        $curationImage->delete();
        $this->sortByStyle($curation->type,$curation->id);
        //找出圖片策展資料 並更新到舊的策展裡
        if($curation->category == 'home' && $curation->type == 'image'){
            $photoData = [];
            $oldCuration = OldCurationDB::find($curation->old_curation_id);
            $curationImages = CurationImageDB::with('langs')->where('curation_id',$curationImage->curation_id)->orderBy('sort','asc')->get();
            if(!empty($oldCuration)){
                foreach($curationImages as $image){
                    $photoImageData1 = [
                        'photo_main_title' => !empty($image->main_title) ? $image->main_title : '',
                        'photo_subtitle' => !empty($image->sub_title) ? $image->sub_title : '',
                    ];
                    $photoLangData = [];
                    foreach($image->langs as $lang){
                        $photoLang = [
                            'photo_main_title_'.$lang->lang => !empty($lang->main_title) ? $lang->main_title : '',
                            'photo_subtitle_'.$lang->lang => !empty($lang->sub_title) ? $lang->sub_title : '',
                        ];
                        $photoLangData = array_merge($photoLangData,$photoLang);
                    }
                    $photoImageData2 = [
                        'photo_url' => !empty($image->old_url) ? $image->old_url : '',
                        'photo' => !empty($image->image) ? $image->image : '',
                    ];
                    $photoData[] = array_merge($photoImageData1,$photoLangData,$photoImageData2);
                }
                $oldCuration->update(['photo_data' => json_encode($photoData)]);
            }
        }
        return redirect()->route('admin.'.$returnUrl.'.show',$curation->id.'#'.$curation->type);
    }
    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $curationImage = CurationImageDB::with('curation')->findOrFail($id);
        $curation = $curationImage->curation;
        $up = ($curationImage->sort) - 1.5;
        $curationImage->fill(['sort' => $up]);
        $curationImage->save();
        $this->sortByStyle($curation->type,$curation->id);
        //找出圖片策展資料 並更新到舊的策展裡
        if($curation->category == 'home' && $curation->type == 'image'){
            $photoData = [];
            $oldCuration = OldCurationDB::find($curation->old_curation_id);
            $curationImages = CurationImageDB::with('langs')->where('curation_id',$curationImage->curation_id)->orderBy('sort','asc')->get();
            if(!empty($oldCuration)){
                foreach($curationImages as $image){
                    $photoImageData1 = [
                        'photo_main_title' => !empty($image->main_title) ? $image->main_title : '',
                        'photo_subtitle' => !empty($image->sub_title) ? $image->sub_title : '',
                    ];
                    $photoLangData = [];
                    foreach($image->langs as $lang){
                        $photoLang = [
                            'photo_main_title_'.$lang->lang => !empty($lang->main_title) ? $lang->main_title : '',
                            'photo_subtitle_'.$lang->lang => !empty($lang->sub_title) ? $lang->sub_title : '',
                        ];
                        $photoLangData = array_merge($photoLangData,$photoLang);
                    }
                    $photoImageData2 = [
                        'photo_url' => !empty($image->old_url) ? $image->old_url : '',
                        'photo' => !empty($image->image) ? $image->image : '',
                    ];
                    $photoData[] = array_merge($photoImageData1,$photoLangData,$photoImageData2);
                }
                $oldCuration->update(['photo_data' => json_encode($photoData)]);
            }
        }
        $curationImage->curation->category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        return redirect()->route('admin.'.$returnUrl.'.show',$curationImage->curation_id.'#'.$curation->type);
    }
    /*
        向下排序
    */
    public function sortdown(Request $request)
    {
        $id = $request->id;
        $curationImage = CurationImageDB::with('curation')->findOrFail($id);
        $curation = CurationDB::findOrFail($curationImage->curation_id);
        $up = ($curationImage->sort) + 1.5;
        $curationImage->fill(['sort' => $up]);
        $curationImage->save();
        $this->sortByStyle($curation->type,$curation->id);
        //找出圖片策展資料 並更新到舊的策展裡
        if($curation->category == 'home' && $curation->type == 'image'){
            $photoData = [];
            $oldCuration = OldCurationDB::find($curation->old_curation_id);
            $curationImages = CurationImageDB::with('langs')->where('curation_id',$curationImage->curation_id)->orderBy('sort','asc')->get();
            if(!empty($oldCuration)){
                foreach($curationImages as $image){
                    $photoImageData1 = [
                        'photo_main_title' => !empty($image->main_title) ? $image->main_title : '',
                        'photo_subtitle' => !empty($image->sub_title) ? $image->sub_title : '',
                    ];
                    $photoLangData = [];
                    foreach($image->langs as $lang){
                        $photoLang = [
                            'photo_main_title_'.$lang->lang => !empty($lang->main_title) ? $lang->main_title : '',
                            'photo_subtitle_'.$lang->lang => !empty($lang->sub_title) ? $lang->sub_title : '',
                        ];
                        $photoLangData = array_merge($photoLangData,$photoLang);
                    }
                    $photoImageData2 = [
                        'photo_url' => !empty($image->old_url) ? $image->old_url : '',
                        'photo' => !empty($image->image) ? $image->image : '',
                    ];
                    $photoData[] = array_merge($photoImageData1,$photoLangData,$photoImageData2);
                }
                $oldCuration->update(['photo_data' => json_encode($photoData)]);
            }
        }
        $curationImage->curation->category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        return redirect()->route('admin.'.$returnUrl.'.show',$curationImage->curation_id.'#'.$curation->type);
    }
    /*
        排序
    */
    public function sortByStyle($style,$curationId)
    {
        if($style == 'block'){
            for($R = 1; $R <= 2; $R++){
                $curationImages = CurationImageDB::where([['row',$R],['style','block'],['curation_id',$curationId]])->orderBy('sort','asc')->get();
                $this->sort($curationImages);
            }
        }else{
            $curationImages = CurationImageDB::where('curation_id',$curationId)->orderBy('sort','asc')->get();
            $this->sort($curationImages);
        }
    }
    /*
        排序
    */
    public function sort($curationImages)
    {
        $i = 1;
        foreach ($curationImages as $curationImage) {
            $id = $curationImage->id;
            CurationImageDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
    }
    /*
        語言資料
    */
    public function lang($id,$langData)
    {
        foreach($langData as $lang => $value){
            !empty($value['modal_content']) ? $modalContent = $value['modal_content'] : $modalContent = null;
            $find = CurationImageLangDB::where([['curation_image_id',$id],['lang',$lang]])->first();
            if($find){
                $find->update([
                    'main_title' => $value['main_title'],
                    'sub_title' => $value['sub_title'],
                    'modal_content' => $modalContent,
                ]);
            }else{
                $find = CurationImageLangDB::create([
                    'curation_image_id' => $id,
                    'lang' => $lang,
                    'main_title' => $value['main_title'],
                    'sub_title' => $value['sub_title'],
                    'modal_content' => $modalContent,
                ]);
            }
        }
    }
    public function storeFile($request){
        //目的目錄
        $destPath = '/upload/curation/';
        //檢查本地目錄是否存在，不存在則建立
        !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
        //檢查S3目錄是否存在，不存在則建立
        !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
        //實際檔案
        $file = $request->file($request->rowName);
        //副檔名
        $ext = $file->getClientOriginalExtension();
        //新檔名
        $fileName1 = $request->rowName.'_'.$request->id.'_'. Carbon::now()->timestamp;
        $fileName = $fileName1. '.' . $ext;
        $smallFileName = $fileName1. '_s.' . $ext;
        //變更尺寸寬高
        $reSizeWidth = 750;
        $reSizeHeigh = 400;
        $originFileName = 'originFileName.'.$ext;
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

        //縮圖
        Image::load(public_path().$destPath.$originFileName)
        ->width(600)
        ->height(320)
        ->save(public_path().$destPath.$smallFileName);
        //將檔案傳送至 S3
        //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
        Storage::disk('s3')->put($destPath.$smallFileName, file_get_contents(public_path().$destPath.$smallFileName) , 'public');

        //刪除本地檔案
        unlink(public_path().$destPath.$originFileName);
        unlink(public_path().$destPath.$fileName);
        unlink(public_path().$destPath.$smallFileName);

        return $destPath.$fileName;
    }
}
