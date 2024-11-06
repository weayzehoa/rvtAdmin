<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryPayMethod as PayMethodDB;
use File;
use Storage;
use Spatie\Image\Image;
use Carbon\Carbon;
use Session;
use App\Http\Requests\Admin\PayMethodRequest;

class PayMethodsController extends Controller
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
        $menuCode = 'M1S7';
        $payMethods = PayMethodDB::orderBy('sort','asc')->get();
        return view('admin.paymethods.index',compact('menuCode','payMethods'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M1S7';
        return view('admin.paymethods.show',compact('menuCode'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PayMethodRequest $request)
    {
        $data = $request->all();
        $data['is_on'] ?? $data['is_on'] = 0;
        $value = $data['value'];
        $payMethod = PayMethodDB::where('value',$value)->first();
        if(!empty($payMethod)){
            Session::put('error',"值(value) [ $value ] 已經存在。");
        }else{
            if(request()->hasFile('image')){
                $data['image'] = $this->storeFile($request);
            }

            $payMethod = PayMethodDB::create($data);

            //重新排序
            $payMethods = PayMethodDB::orderBy('sort','ASC')->get();
            $i = 1;
            foreach ($payMethods as $payMethod) {
                $id = $payMethod->id;
                PayMethodDB::where('id', $id)->update(['sort' => $i]);
                $i++;
            }
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
        $menuCode = 'M1S7';
        $payMethod = PayMethodDB::findOrFail($id);
        return view('admin.paymethods.show',compact('payMethod','menuCode'));
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
    public function update(PayMethodRequest $request, $id)
    {
        $data = $request->all();
        $data['is_on'] ?? $data['is_on'] = 0;
        !empty($data['value']) ? $value = $data['value'] : $value = null;
        $payMethod = PayMethodDB::findOrFail($id);
        if(request()->hasFile('image')){
            $data['image'] = $this->storeFile($request);
        }
        if($payMethod->value != $value){
            $chkPaymethod = PayMethodDB::where('value',$value)->first();
            if(!empty($chkPaymethod)){
                Session::put('error',"值(value) [ $value ] 已經存在。");
            }else{
                $payMethod->update($data);
            }
        }else{
            $payMethod->update($data);
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
        $payMethod = PayMethodDB::findOrFail($id);
        if(!empty($payMethod)){
            $payMethod->delete();
            //重新排序
            $payMethods = PayMethodDB::orderBy('sort','ASC')->get();
            $i = 1;
            foreach ($payMethods as $payMethod) {
                $id = $payMethod->id;
                PayMethodDB::where('id', $id)->update(['sort' => $i]);
                $i++;
            }
        }
        return redirect()->back();
    }
    /*
        啟用或禁用該主選單
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        PayMethodDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }
    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $payMethod = PayMethodDB::findOrFail($id);
        $up = ($payMethod->sort) - 1.5;
        $payMethod->fill(['sort' => $up]);
        $payMethod->save();

        $payMethods = PayMethodDB::orderBy('sort','ASC')->get();
        $i = 1;
        foreach ($payMethods as $payMethod) {
            $id = $payMethod->id;
            PayMethodDB::where('id', $id)->update(['sort' => $i]);
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
        $payMethod = PayMethodDB::findOrFail($id);
        $up = ($payMethod->sort) + 1.5;
        $payMethod->fill(['sort' => $up]);
        $payMethod->save();

        $payMethods = PayMethodDB::orderBy('sort','ASC')->get();
        $i = 1;
        foreach ($payMethods as $payMethod) {
            $id = $payMethod->id;
            PayMethodDB::where('id', $id)->update(['sort' => $i]);
            $i++;
        }
        return redirect()->back();
    }
    public function storeFile($request){
        //目的目錄
        $destPath = '/upload/payMethod/';
        //檢查本地目錄是否存在，不存在則建立
        !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
        //檢查S3目錄是否存在，不存在則建立
        !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
        //實際檔案
        $file = $request->file('image');
        //副檔名
        $ext = $file->getClientOriginalExtension();
        //新檔名
        $fileName = 'payMethod_'. Carbon::now()->timestamp . '.' . $ext;
        //變更尺寸寬高
        $reSizeWidth = 240;
        $reSizeHeigh = 60;
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
        //返回檔案資料
        return $destPath.$fileName;
    }
}
