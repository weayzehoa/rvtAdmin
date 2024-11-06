<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use App\Models\iCarryProductModel as ProductModelDB;
use App\Models\iCarryProductPackage as ProductPackageDB;

use App\Models\OldVendorLangEn as OldVendorLangEnDB;
use App\Models\OldVendorLangJp as OldVendorLangJpDB;
use App\Models\OldVendorLangKr as OldVendorLangKrDB;
use App\Models\OldVendorLangTh as OldVendorLangThDB;

use App\Http\Requests\Admin\VendorsRequest;
use App\Http\Requests\Admin\VendorsLangRequest;
use App\Http\Requests\Admin\VendorShopsRequest;
use App\Http\Requests\Admin\VendorAccountsUpdateRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;
use App\Exports\VendorsExport;
use Maatwebsite\Excel\Facades\Excel;

use App\Traits\VendorFunctionTrait;

class VendorsController extends Controller
{
    use VendorFunctionTrait;
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
        $compact = $appends = [];
        $userId = auth('admin')->user()->id;
        $menuCode['Vendors'] = 'M2S1';
        $menuCode['Shops'] = 'M2S2';
        $menuCode['Accounts'] = 'M2S3';
        $menuCode['Products'] = 'M4S1';
        $vendors = $this->getVendorData(request(),'index');

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            $appends = array_merge($appends, [$key => $value]);
            $compact = array_merge($compact, [$key]);
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }
        foreach($vendors as $vendor){
            $iCarryServiceFee = null;
            $serviceFee = $this->serviceFee($vendor->service_fee);
            if(is_array($serviceFee)){
                for($i=0;$i<count($serviceFee);$i++){
                    if($serviceFee[$i]->name == 'iCarry'){
                        $iCarryServiceFee = $serviceFee[$i]->percent;
                    }
                }
            }
            $vendor->iCarryServiceFee = $iCarryServiceFee;
        }
        $userId == 14 ? $totalVendors = VendorDB::where('categories','like',"%17%")->count() : $totalVendors = VendorDB::count();
        $userId == 14 ? $totalEnable = VendorDB::where('is_on',1)->where('categories','like',"%17%")->count() : $totalEnable = VendorDB::where('is_on',1)->count();
        $userId == 14 ? $totalDisable = VendorDB::where('is_on',0)->where('categories','like',"%17%")->count() : $totalDisable = VendorDB::where('is_on',0)->count();
        //清除頁籤用的session
        Session::forget(['vendorAccountShow','vendorShopShow','vendorOrderShow','vendorProductShow']);
        $compact = array_merge($compact, ['menuCode','vendors','totalVendors','totalEnable','totalDisable','appends']);
        return view('admin.vendors.index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode['Vendors'] = 'M2S1';
        $menuCode['Shops'] = 'M2S2';
        $menuCode['Accounts'] = 'M2S3';
        $menuCode['Products'] = 'M4S1';
        $categories = CategoryDB::where('is_on',1)->get();
        $serviceFees = $this->serviceFee();
        return view('admin.vendors.show',compact('menuCode','categories','serviceFees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VendorsRequest $request)
    {
        $data = $request->all();
        $data['is_on'] ?? $data['is_on'] = 0;
        $data['use_sf'] ?? $data['use_sf'] = 0;
        $data['shipping_self'] ?? $data['shipping_self'] = 0;
        $data['service_fee'] = $this->serviceFee($data['service_fee']);
        !empty($data['categories']) ? $data['categories'] = rtrim(join(',',$data['categories']),',') : $data['categories'] = '';
        if($data['use_sf'] == 1 && empty($data['factory_address'])){
            Session::put('error','使用順豐運單取號功能時，工廠地址必填不可為空值。');
            return redirect()->back();
        }
        $vendor = VendorDB::create($data);
        return redirect()->route('admin.vendors.show',$vendor->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode['Vendors'] = 'M2S1';
        $menuCode['Shops'] = 'M2S2';
        $menuCode['Accounts'] = 'M2S3';
        $menuCode['Products'] = 'M4S1';
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        $categories = CategoryDB::where('is_on',1)->get();
        request()->request->add(['id' => $id]);
        $vendor = $this->getVendorData(request(),'show');
        !empty($vendor->new_cover) ? $vendor->new_cover = env('AWS_FILE_URL').$vendor->new_cover : '';
        !empty($vendor->new_logo) ? $vendor->new_logo = env('AWS_FILE_URL').$vendor->new_logo : '';
        !empty($vendor->new_site_cover) ? $vendor->new_site_cover = env('AWS_FILE_URL').$vendor->new_site_cover : '';
        $serviceFees = $this->serviceFee($vendor->service_fee);
        for($i=0;$i<count($langs);$i++){
            $getData = VendorLangDB::where([['lang',$langs[$i]['code']],['vendor_id',$vendor->id]])->get()->toArray();
            foreach($getData as $langData){
                $langs[$i]['data'] = $langData;
            }
        }
        return view('admin.vendors.show',compact('menuCode','langs','categories','vendor','serviceFees'));
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
        $vendor = VendorDB::findOrFail($id);
        $data['is_on'] ?? $data['is_on'] = 0;
        $data['use_sf'] ?? $data['use_sf'] = 0;
        if($data['use_sf'] == 1 && empty($data['factory_address'])){
            Session::put('error','使用順豐運單取號功能時，工廠地址必填不可為空值。');
            return redirect()->back();
        }
        $data['shipping_self'] ?? $data['shipping_self'] = 0;
        !empty($data['service_fee']) ? $data['service_fee'] = $this->serviceFee($data['service_fee']) : $data['service_fee']=null;
        !empty($data['categories']) ? $data['categories'] = rtrim(join(',',$data['categories']),',') : $data['categories'] = null;
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i"; //檢驗email規則
        $mails = $notifyMails = $billMails = $newBillMails = $newMails = $newNotifyMails = [];
        if(!empty($data['email'])){
            $data['email'] = str_replace([' ','/',':',';','|','，','；'],['',',',',',',',',',',',','],$data['email']);
            $mails = explode(',',$data['email']);
            if(count($mails) > 0){
                $mail = null;
                for($i=0;$i<count($mails);$i++){
                    $mail = strtolower($mails[$i]);
                    if(preg_match($pattern,$mail)){
                        $newMails[] = $mail;
                    };
                }
                if(count($newMails) > 0){
                    $newMails = array_unique($newMails);
                    $data['email'] = join(',',$newMails);
                }else{
                    $data['email'] = null;
                }
            }
        }
        if(!empty($data['notify_email'])){
            $data['notify_email'] = str_replace([' ','/',':',';','|','，','；'],['',',',',',',',',',',',','],$data['notify_email']);
            $notifyMails = explode(',',$data['notify_email']);
            if(count($notifyMails) > 0){
                $mail = null;
                for($i=0;$i<count($notifyMails);$i++){
                    $mail = strtolower($notifyMails[$i]);
                    if(preg_match($pattern,$mail)){
                        $newNotifyMails[] = $mail;
                    };
                }
                if(count($newNotifyMails) > 0){
                    $newNotifyMails = array_unique($newNotifyMails);
                    $data['notify_email'] = join(',',$newNotifyMails);
                }else{
                    $data['notify_email'] = null;
                }
            }
        }
        if(!empty($data['bill_email'])){
            $data['bill_email'] = str_replace([' ','/',':',';','|','，','；'],['',',',',',',',',',',',','],$data['bill_email']);
            $billMails = explode(',',$data['bill_email']);
            if(count($billMails) > 0){
                $mail = null;
                for($i=0;$i<count($billMails);$i++){
                    $mail = strtolower($billMails[$i]);
                    if(preg_match($pattern,$mail)){
                        $newBillMails[] = $mail;
                    };
                }
                if(count($newBillMails) > 0){
                    $newBillMails = array_unique($newBillMails);
                    $data['bill_email'] = join(',',$newBillMails);
                }else{
                    $data['bill_email'] = null;
                }
            }
        }
        $vendor->update($data);
        //語言資料
        foreach($data['langs'] as $lang => $value){
            $find = VendorLangDB::where([['vendor_id',$id],['lang',$lang]])->first();
            if(!empty($find)){
                $find->update([
                    'name' => $value['name'],
                    'summary' => $value['summary'],
                    'description' => $value['description'],
                ]);
            }else{
                $find = VendorLangDB::create([
                    'vendor_id' => $id,
                    'lang' => $lang,
                    'name' => $value['name'],
                    'summary' => $value['summary'],
                    'description' => $value['description'],
                ]);
            }
            $langData['name'] = $value['name'];
            $langData['summary'] = $value['summary'];
            $langData['description'] = $value['description'];
            //舊語言資料更新
            if($lang == 'en'){
                $oldLangEnDB = OldVendorLangEnDB::find($id);
                empty($oldLangEnDB) ? $oldLangEnDB = OldVendorLangEnDB::create($vendor->toArray()) : '';
                $oldLangEnDB->update($langData);
            }
            if($lang == 'jp'){
                $oldLangJpDB = OldVendorLangEnDB::find($id);
                empty($oldLangJpDB) ? $oldLangJpDB = OldVendorLangEnDB::create($vendor->toArray()) : '';
                $oldLangJpDB->update($langData);
            }
            if($lang == 'kr'){
                $oldLangKrDB = OldVendorLangKrDB::find($id);
                empty($oldLangKrDB) ? $oldLangKrDB = OldVendorLangKrDB::create($vendor->toArray()) : '';
                $oldLangKrDB->update($langData);
            }
            if($lang == 'th'){
                $oldLangThDB = OldVendorLangThDB::find($id);
                empty($oldLangThDB) ? $oldLangThDB = OldVendorLangThDB::create($vendor->toArray()) : '';
                $oldLangThDB->update($langData);
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
        $vendor = VendorDB::findOrFail($id)->delete();
        return redirect()->back();
    }
    /*
        啟用或停用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        VendorDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }
    /*
        匯出功能
    */
    public function export()
    {
        $exportFile = '商家管理資料匯出_'.date('YmdHis').'.xlsx';
        return Excel::download(new VendorsExport, $exportFile);
    }
    /*
        整理Servce_fee資料
        1. 檢驗是否存在
        2. 檢驗是否為陣列
        3. 轉換percent空值為0
    */
    private function serviceFee($input = ''){
        if($input == ''){
            $serviceFees = json_decode('[{"name":"天虹","percent":0},{"name":"閃店","percent":0},{"name":"iCarry","percent":0},{"name":"現場提貨","percent":0}]');
        }elseif(is_array($input)){
            for($i=0;$i<count($input['name']);$i++){
                $serviceFees[$i]['name'] = $input['name'][$i];
                $serviceFees[$i]['percent'] = $input['percent'][$i];
            }
            $serviceFees = json_encode($serviceFees);
        }else{
            $serviceFees = json_decode(str_replace('"percent":}','"percent":0}',$input));
        }
        return $serviceFees;
    }
    /*
        語言功能 (已整併到update)
     */
    public function lang(VendorsLangRequest $request)
    {
        $data = $request->all();
        $data['vendor_id'] = $request->vendor_id;
        $langId = $request->langId;
        $langId ? VendorLangDB::findOrFail($langId)->update($data) : VendorLangDB::create($data);
        return redirect()->back();
    }
    /*
        圖檔上傳
     */
    public function upload(Request $request)
    {
        //先檢查vendor是否存在
        $id = $request->id;
        $vendor = VendorDB::findOrFail($id);
        if(!empty($vendor)){
            //檢查表單是否有檔案
            if(!$request->hasFile('new_cover') && !$request->hasFile('new_logo') && !$request->hasFile('new_site_cover')){
                $message = "請選擇要上傳的檔案在按送出按鈕";
                Session::put('info',$message);
                return redirect()->back();
            }

            if($request->hasFile('new_cover')){
                $request->rowName = 'new_cover';
                $this->storeFile($request);
            }

            if($request->hasFile('new_logo')){
                $request->rowName = 'new_logo';
                $this->storeFile($request);
            }

            if($request->hasFile('new_site_cover')){
                $request->rowName = 'new_site_cover';
                $this->storeFile($request);
            }

            $message = "檔案上傳成功";
            Session::put('success',$message);
        }
        return redirect()->back();
    }

    public function storeFile($request){
        $id = $request->id;
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
        $time = Carbon::now()->timestamp;
        $fileName = $request->rowName.'_'.$request->id.'_'. $time . '.' . $ext;
        $sfileName = $request->rowName.'_'.$request->id.'_'. $time . '_s.' . $ext;
        //變更尺寸寬高
        if($request->rowName == 'new_cover' || $request->rowName == 'new_site_cover'){
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;
        }else{
            $reSizeWidth = 540;
            $reSizeHeigh = 360;
        }
        //新的檔案名稱
        $request->rowName == 'new_cover' ? $columnName = 'img_cover' : '';
        $request->rowName == 'new_logo' ? $columnName = 'img_log' : '';
        $request->rowName == 'new_site_cover' ? $columnName = 'img_site' : '';
        //檔案路徑名稱資料寫入資料庫
        $vendor = VendorDB::find($id);
        $vendor->update([$columnName => $destPath.$fileName, $request->rowName => $destPath.$fileName]);
        $vendorData = $vendor->toArray();
        //檢查舊的語言資料是否存在, 建立或更新
        $oldLangEnDB = OldVendorLangEnDB::find($id);
        empty($oldLangEnDB) ? $oldLangEnDB = OldVendorLangEnDB::create($vendorData) : $oldLangEnDB->update($vendorData);
        $oldLangJpDB = OldVendorLangEnDB::find($id);
        empty($oldLangJpDB) ? $oldLangJpDB = OldVendorLangEnDB::create($vendorData) : $oldLangJpDB->update($vendorData);
        $oldLangKrDB = OldVendorLangKrDB::find($id);
        empty($oldLangKrDB) ? $oldLangKrDB = OldVendorLangKrDB::create($vendorData) : $oldLangKrDB->update($vendorData);
        $oldLangThDB = OldVendorLangThDB::find($id);
        empty($oldLangThDB) ? $oldLangThDB = OldVendorLangThDB::create($vendorData) : $oldLangThDB->update($vendorData);
        //將檔案搬至本地目錄
        $file->move(public_path().$destPath, $fileName);
        //使用Spatie/image的套件Resize圖檔
        Image::load(public_path().$destPath.$fileName)
        ->width($reSizeWidth)
        ->height($reSizeHeigh)
        ->save(public_path().$destPath.$fileName);
        //使用Spatie/image的套件Resize圖檔
        Image::load(public_path().$destPath.$fileName)
        ->width($reSizeWidth/4)
        ->height($reSizeHeigh/4)
        ->save(public_path().$destPath.$sfileName);
        //將檔案傳送至 S3
        //加上 public 讓檔案是 Visibility 不然該檔案是無法被看見的
        Storage::disk('s3')->put($destPath.$fileName, file_get_contents(public_path().$destPath.$fileName) , 'public');
        Storage::disk('s3')->put($destPath.$sfileName, file_get_contents(public_path().$destPath.$fileName) , 'public');
        //刪除本地檔案
        unlink(public_path().$destPath.$fileName);
    }

    /*
        showOld 舊function參考用
    */
    public function showOld()
    {
        $vendor = VendorDB::with('products','products.models')->withTrashed()->findOrFail($id);
        //上面使用with方式可以省略下面這段, 減少對資料庫的查詢
        foreach($vendor->products as $product){
            if($product->model_type == 3){
                $models = ProductPackageDB::join('product_models','product_models.id','product_packages.product_model_id')
                ->where('product_packages.product_id',$product->id)
                ->select(
                    'product_models.id',
                    'product_models.name',
                    'product_models.sku',
                    'product_models.quantity',
                    'product_models.safe_quantity',
                )->get();
            }else{
                $models = ProductModelDB::where('product_id',$product->id)
                ->select(
                    'id',
                    'name',
                    'sku',
                    'quantity',
                    'safe_quantity',
                )->get();
            }
            $product->models = $models;
        }
    }
}
