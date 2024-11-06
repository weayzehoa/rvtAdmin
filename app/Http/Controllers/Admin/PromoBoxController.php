<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryPromoBox as PromoBoxDB;
use App\Http\Requests\Admin\PromoBoxesRequest;
use File;
use Storage;
use Spatie\Image\Image;
use Carbon\Carbon;
use DB;

class PromoBoxController extends Controller
{
    /**
     * Create a new controller instance.
     * 進到這個控制器需要透過middleware檢驗是否為後台的使用者
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:admin','optimizeImages']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menuCode = 'M7S4';
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
        $promoBoxes = PromoBoxDB::orderBy('is_on','desc');
        if (isset($keyword) && $keyword) {
            $promoBoxes = $promoBoxes->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%$keyword%")
                ->orWhere('text_teaser', 'like', "%$keyword%")
                ->orWhere('text_complete', 'like', "%$keyword%")
                ->orWhere('text_title_en', 'like', "%$keyword%")
                ->orWhere('text_teaser_en', 'like', "%$keyword%")
                ->orWhere('text_complete_en', 'like', "%$keyword%")
                ->orWhere('text_title_jp', 'like', "%$keyword%")
                ->orWhere('text_teaser_jp', 'like', "%$keyword%")
                ->orWhere('text_complete_jp', 'like', "%$keyword%")
                ->orWhere('text_title_kr', 'like', "%$keyword%")
                ->orWhere('text_teaser_kr', 'like', "%$keyword%")
                ->orWhere('text_complete_kr', 'like', "%$keyword%")
                ->orWhere('text_title_th', 'like', "%$keyword%")
                ->orWhere('text_teaser_th', 'like', "%$keyword%")
                ->orWhere('text_complete_th', 'like', "%$keyword%");
            });
        }
        if (!isset($list)) {
            $list = 15;
            $compact = array_merge($compact, ['list']);
        }
        $promoBoxes = $promoBoxes->orderBy('id','desc')->paginate($list);
        $compact = array_merge($compact, ['menuCode','promoBoxes','appends']);
        return view('admin.promoboxes.index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M7S4';
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        return view('admin.promoboxes.show',compact(['menuCode','langs']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PromoBoxesRequest $request)
    {
        $data = $request->all();
        isset($data['start_time']) ? $data['start_date'] = $data['start_time'] : '';
        isset($data['end_time']) ? $data['end_date'] = $data['start_time'] : '';
        $promoBox = PromoBoxDB::create($data);
        //檔案資料
        if($request->hasFile('img_url')){
            $request->id = $promoBox->id;
            $request->rowName = 'img_url';
            $data['img_url']=$this->storeFile($request);
        }
        $promoBox->update($data);
        return redirect()->route('admin.promoboxes.show', $promoBox->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M7S4';
        $promoBox = PromoBoxDB::findOrFail($id);
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        return view('admin.promoboxes.show', compact(['promoBox','menuCode','langs']));
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
    public function update(PromoBoxesRequest $request, $id)
    {
        $data = $request->all();
        isset($data['is_on']) ? '' : $data['is_on'] = 0;
        isset($data['start_time']) ? $data['start_date'] = $data['start_time'] : '';
        isset($data['end_time']) ? $data['end_date'] = $data['start_time'] : '';
        $promoBox = PromoBoxDB::findOrFail($id);
        //檔案資料
        if($request->hasFile('img_url')){
            $request->id = $id;
            $request->rowName = 'img_url';
            $data['img_url']=$this->storeFile($request);
        }
        $promoBox->update($data);
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
        啟用或禁用
    */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        PromoBoxDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }
    /*
        檔案儲存
     */
    public function storeFile($request){
        //目的目錄
        $destPath = '/upload/promotion/';
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
        $reSizeWidth = 800;
        $reSizeHeigh = 600;
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
        return env('AWS_FILE_URL').$destPath.$fileName;
    }
}
