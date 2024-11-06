<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryOldCuration as OldCurationDB;
use App\Models\iCarryCurationVendor as CurationVendorDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;

class CurationVendorsController extends Controller
{
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
        $curationId = $data['curation_id'];
        $newVendorIds = $data['vendor_id'];
        $curation = CurationDB::findOrFail($curationId);
        $oldCuration = OldCurationDB::find($curation->old_curation_id);
        if(!empty($oldCuration) && $curation->category == 'home'){
            $oldCuration->update(['select_data' => join(',',$newVendorIds)]);
        }
        $category = $curation->category;
        $category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        $oldVendorIds = CurationVendorDB::where('curation_id',$data['curation_id'])->orderBy('sort','asc')->get()->pluck('vendor_id')->all();

        $removed = array_diff($oldVendorIds,$newVendorIds); //被移除

        foreach ($removed as $key => $value) {
            CurationVendorDB::where([['curation_id',$curationId],['vendor_id',$value]])->delete();
        }

        for($i=0;$i<count($newVendorIds);$i++){
            $curationVendor = CurationVendorDB::where([['curation_id',$data['curation_id']],['vendor_id',$newVendorIds[$i]]])->first();
            if($curationVendor){
                $curationVendor = $curationVendor->update(['sort' => $i+1]);
            }else{
                $curationVendor = CurationVendorDB::create([
                    'curation_id' => $data['curation_id'],
                    'vendor_id' => $newVendorIds[$i],
                    'sort' => $i+1,
                ]);
            }
        }

        return redirect()->route('admin.'.$returnUrl.'.show',$data['curation_id'].'#vendor');
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
        $curationVendor = CurationVendorDB::with('curation')->findOrFail($id);
        $curationVendor->curation->category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        $vendorId = $curationVendor->vendor_id;
        $vendor = VendorDB::findOrFail($vendorId);
        //檔案資料
        if($request->hasFile('img_cover')){
            $request->id = $vendorId;
            $request->rowName = 'img_cover';
            $data['img_cover']=$this->storeFile($request);
        }
        if($request->hasFile('img_logo')){
            $request->id = $vendorId;
            $request->rowName = 'img_logo';
            $data['img_logo']=$this->storeFile($request);
        }
        $vendor = $vendor->update($data);
        $this->lang($vendorId,$data['langs']);

        return redirect()->route('admin.'.$returnUrl.'.show',$curationVendor->curation_id.'#vendor');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $curationVendor = CurationVendorDB::with('curation')->findOrFail($id);
        $curation = $curationVendor->curation;

        $data['curation_id'] = $curationVendor->curation_id;
        $data['style'] = $curationVendor->style;
        $curationVendor->delete();
        $this->autoSort($curationVendor->curation_id);

        $oldCuration = OldCurationDB::find($curation->old_curation_id);
        !empty($oldCuration) && $curation->category == 'home' ? $oldCuration->update(['select_data' => join(',',CurationVendorDB::where('curation_id',$curationVendor->curation_id)->orderBy('sort','asc')->get()->pluck('vendor_id')->all())]) : '';

        $curationVendor->curation->category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        return redirect()->route('admin.'.$returnUrl.'.show',$curationVendor->curation_id.'#vendor');
    }
    /*
        手動排序
    */
    public function sort(Request $request)
    {
        $ids = $request->id;
        $sorts = $request->sort;
        $curationId = $request->curation_id;
        $curation = CurationDB::findOrFail($curationId);
        $category = $curation->category;
        $category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        if(count($ids) == count($sorts)){
            for($i=0;$i<count($ids);$i++){
                CurationVendorDB::where('id',$ids[$i])->update(['sort' => $sorts[$i]]);
            }
        }
        $oldCuration = OldCurationDB::find($curation->old_curation_id);
        !!empty($oldCuration) && $curation->category == 'home' ? $oldCuration->update(['select_data' => join(',',CurationVendorDB::where('curation_id',$curation->id)->orderBy('sort','asc')->get()->pluck('vendor_id')->all())]) : '';
        return redirect()->route('admin.'.$returnUrl.'.show',$curationId.'#vendor');
    }
    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $curationVendor = CurationVendorDB::with('curation')->findOrFail($id);
        $up = ($curationVendor->sort) - 1.5;
        $curationVendor->fill(['sort' => $up]);
        $curationVendor->save();
        $this->autoSort($curationVendor->curation_id);

        $curation = $curationVendor->curation;
        $oldCuration = OldCurationDB::find($curation->old_curation_id);
        !empty($oldCuration) && $curation->category == 'home' ? $oldCuration->update(['select_data' => join(',',CurationVendorDB::where('curation_id',$curationVendor->curation_id)->orderBy('sort','asc')->get()->pluck('vendor_id')->all())]) : '';

        $curationVendor->curation->category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        return redirect()->route('admin.'.$returnUrl.'.show',$curationVendor->curation_id.'#vendor');
    }
    /*
        向下排序
    */
    public function sortdown(Request $request)
    {
        $id = $request->id;
        $curationVendor = CurationVendorDB::with('curation')->findOrFail($id);
        $up = ($curationVendor->sort) + 1.5;
        $curationVendor->fill(['sort' => $up]);
        $curationVendor->save();
        $this->autoSort($curationVendor->curation_id);

        $curation = $curationVendor->curation;
        $oldCuration = OldCurationDB::find($curation->old_curation_id);
        !empty($oldCuration) && $curation->category == 'home' ? $oldCuration->update(['select_data' => join(',',CurationVendorDB::where('curation_id',$curationVendor->curation_id)->orderBy('sort','asc')->get()->pluck('vendor_id')->all())]) : '';

        $curationVendor->curation->category == 'home' ? $returnUrl = 'curations' : $returnUrl = 'categorycurations';
        return redirect()->route('admin.'.$returnUrl.'.show',$curationVendor->curation_id.'#vendor');
    }
    /*
        排序
    */
    public function autoSort($curationId)
    {
        $curationVendors = CurationVendorDB::where('curation_id',$curationId)->orderBy('sort','asc')->get();
        $i = 1;
        foreach ($curationVendors as $curationVendor) {
            $id = $curationVendor->id;
            CurationVendorDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
    }
        /*
        語言資料
    */
    public function lang($id,$langData)
    {
        foreach($langData as $lang => $value){
            $find = VendorLangDB::where([['vendor_id',$id],['lang',$lang]])->first();
            if($find){
                $find->update([
                    'curation' => $value['curation'],
                ]);
            }else{
                $find = VendorLangDB::create([
                    'vendor_id' => $id,
                    'lang' => $lang,
                    'curation' => $value['curation'],
                ]);
            }
        }
    }
    public function storeFile($request){
        //目的目錄
        $destPath = '/upload/vendor/';
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
        if($request->rowName == 'img_cover'){
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;
        }else{
            $reSizeWidth = 540;
            $reSizeHeigh = 360;
        }
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
        return $destPath.$fileName;
    }
}
