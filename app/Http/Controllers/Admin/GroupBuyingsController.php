<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryGroupBuying as GroupBuyingDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryCountry as CountryDB;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use Session;

class GroupBuyingsController extends Controller
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
        $menuCode = 'M6S1';
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
        $groupBuyings = new GroupBuyingDB;
        if (!isset($list)) {
            $list = 30;
            $compact = array_merge($compact, ['list']);
        }
        $groupBuyings = $groupBuyings->orderBy('create_time','desc')->paginate($list);
        $compact = array_merge($compact, ['menuCode','groupBuyings','appends']);
        return view('admin.groupbuy.index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M6S1';
        $countries = CountryDB::get();
        $categories = CategoryDB::where('is_on',1)->select(['id','name'])->get();
        $vendors = VendorDB::where('is_on',1)->select(['id','name'])->orderBy('name','asc')->get();
        return view('admin.groupbuy.show', compact(['menuCode','categories','vendors','countries']));
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
        !isset($data['is_on']) ? $data['is_on'] = 0 : '';
        $countryId = null;
        $tmp = CountryDB::where('name',$data['product_sold_country'])->first();
        if(!empty($tmp)) {
            $countryId = $tmp->id;
            if(isset($data['product_id']) && count($data['product_id']) > 0 && !empty($countryId)) {
                $vendorTable = env('DB_ICARRY') . '.' . (new VendorDB())->getTable();
                $productTable = env('DB_ICARRY') . '.' . (new ProductDB())->getTable();
                $data['product_id'] = ProductDB::join($vendorTable, $vendorTable . '.id', $productTable . '.vendor_id')
                ->where(function ($query) use ($countryId, $productTable) {
                    $query = $query->whereRaw("FIND_IN_SET('$countryId',$productTable.allow_country_ids)")->orWhereNull($productTable . '.allow_country_ids');
                })->where($vendorTable . '.is_on', 1)
                ->where($productTable . '.is_del', 0)
                ->where($productTable . '.model_type', 1)
                ->whereIn($productTable . '.status', [-3,1])
                ->whereIn($productTable . '.id', $data['product_id'])
                ->select($productTable . '.id')->get()->pluck('id')->all();
                $data['allow_products'] = join(',', $data['product_id']);
                $groupbuying = GroupBuyingDB::create($data);

                if($request->hasFile('cover')) {
                    $this->storeFile($request, 'cover', $groupbuying->id);
                }

                if($request->hasFile('logo')) {
                    $this->storeFile($request, 'logo', $groupbuying->id);
                }
            } else {
                Session::put('error', '請選擇商品');
            }
        }else{
            Session::put('error', '銷售國家錯誤');
        }
        return redirect()->route('admin.groupbuyings');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M6S1';
        $groupBuying = GroupBuyingDB::findOrFail($id);
        $products = explode(',',$groupBuying->allow_products);
        $countries = CountryDB::get();
        $groupBuying->products = ProductDB::whereIn('id',$products)->get();
        $categories = CategoryDB::where('is_on',1)->select(['id','name'])->get();
        $vendors = VendorDB::where('is_on',1)->select(['id','name'])->orderBy('name','asc')->get();
        return view('admin.groupbuy.show', compact(['menuCode','groupBuying','categories','vendors','countries']));
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
        $groupbuying = GroupBuyingDB::findOrFail($id);
        !isset($data['is_on']) ? $data['is_on'] = 0 : '';
        $countryId = null;
        $tmp = CountryDB::where('name',$data['product_sold_country'])->first();
        if(!empty($tmp)) {
            $countryId = $tmp->id;
            if(isset($data['product_id']) && count($data['product_id']) > 0 && !empty($countryId)){
                $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
                $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
                $data['product_id'] = ProductDB::join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                ->where(function($query)use($countryId,$productTable){
                    $query = $query->whereRaw("FIND_IN_SET('$countryId',$productTable.allow_country_ids)")->orWhereNull($productTable.'.allow_country_ids');
                })->where($vendorTable.'.is_on',1)
                ->where($productTable.'.is_del',0)
                ->where($productTable.'.model_type',1)
                ->whereIn($productTable.'.status',[-3,1])
                ->whereIn($productTable.'.id',$data['product_id'])
                ->select($productTable.'.id')->get()->pluck('id')->all();
                $data['allow_products'] = join(',',$data['product_id']);
                $groupbuying->update($data);

                if($request->hasFile('cover')){
                    $this->storeFile($request,'cover',$id);
                }

                if($request->hasFile('logo')){
                    $this->storeFile($request,'logo',$id);
                }
            }else{
                Session::put('error','請至少選擇一個商品。');
            }
        }else{
            Session::put('error','銷售國家錯誤。');
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
        //
    }
    /*
        啟用或停用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        $curation = GroupBuyingDB::findOrFail($request->id);
        $curation->update(['is_on' => $is_on]);
        return redirect()->back();
    }
    public function getProducts(Request $request)
    {
        $countryId = null;
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        if(!empty($request->country)){
            $tmp = CountryDB::where('name',$request->country)->first();
            if(!empty($tmp)) {
                $countryId = $tmp->id;
            }else{
                return null;
            }
        }else{
            return null;
        }
        $products = ProductDB::join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
        ->where(function($query)use($countryId,$productTable){
            $query = $query->whereRaw("FIND_IN_SET('$countryId',$productTable.allow_country_ids)")->orWhereNull($productTable.'.allow_country_ids');
        })->where($vendorTable.'.is_on',1)
        ->where($productTable.'.is_del',0)
        ->where($productTable.'.model_type',1)
        ->whereIn($productTable.'.status',[-3,1]);
        if($request->category){
            $products = $products->where($productTable.'.category_id',$request->category);
        }elseif($request->vendor){
            $products = $products->where($productTable.'.vendor_id',$request->vendor);
        }elseif($request->keyword){
            $keyword = $request->keyword;
            $products = $products->where(function ($query) use ($keyword,$productTable,$vendorTable) {
                $query->where($productTable.'.name', 'like', "%$keyword%")
                ->orWhere($vendorTable.'.name', 'like', "%$keyword%");
            });
        }else{
            return null;
        }
        //去除掉被選擇的商品
        if($request->ids){
            $products = $products->whereNotIn($productTable.'.id',$request->ids);
        }
        $products = $products->distinct()
        ->select([
            $productTable.'.id',
            $productTable.'.name',
            $productTable.'.status',
        ])->orderBy($productTable.'.status','desc')->get();

        return $products;
    }
    public function storeFile($request, $type, $id){
        //目的目錄
        $destPath = '/upload/groupBuying/';
        //檢查本地目錄是否存在，不存在則建立
        !file_exists(public_path() . $destPath) ? File::makeDirectory(public_path() . $destPath, 0755, true) : '';
        //檢查S3目錄是否存在，不存在則建立
        !Storage::disk('s3')->has($destPath) ? Storage::disk('s3')->makeDirectory($destPath) : '';
        //實際檔案
        $file = $request->file($type);
        //副檔名
        $ext = $file->getClientOriginalExtension();
        //新檔名
        $fileName = $type.'_'.$id.'_'. Carbon::now()->timestamp . '.' . $ext;
        //變更尺寸寬高
        if($type == 'cover'){
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;
        }else{
            $reSizeWidth = 540;
            $reSizeHeigh = 360;
        }
        $awsFile = env('AWS_FILE_URL');
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
        //檔案路徑名稱資料寫入資料庫
        $groupBuying = GroupBuyingDB::find($id);
        $groupBuying->update([$type => $awsFile.$destPath.$fileName]);
        //刪除本地檔案
        unlink(public_path().$destPath.$fileName);
    }
}
