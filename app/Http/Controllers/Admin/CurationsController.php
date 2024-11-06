<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorLang as VendorLangDB;
use App\Models\iCarryCuration as CurationDB;
use App\Models\iCarryOldCuration as OldCurationDB;
use App\Models\iCarryCurationVendor as CurationVendorDB;
use App\Models\iCarryCurationProduct as CurationProductDB;
use App\Models\iCarryCurationImage as CurationImageDB;
use App\Models\iCarryCurationImageLang as CurationImageLangDB;
use App\Models\iCarryCurationLang as CurationLangDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryProduct as ProductDB;
use App\Http\Requests\Admin\CurationsRequest;
use App\Http\Requests\Admin\CurationsLangRequest;
use Carbon\Carbon;
use File;
use Storage;
use Spatie\Image\Image;
use DB;

class CurationsController extends Controller
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
        $menuCode = 'M7S1';
        $appends = [];
        $compact = [];
        $totalOrders = 0;

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        $curations = CurationDB::where('category','home');

        isset($type) && $type ? $curations = $curations->where('type',$type) : $type = '';

        isset($start_time) && $start_time ? $curations = $curations->where('start_time','>=',$start_time) : '';
        isset($end_time) && $end_time ? $curations = $curations->where('end_time','<=',$end_time) : '';

        if(isset($is_on)){
            $is_on == 'on' ? $on = 1 : '';
            $is_on == 'off' ? $on = 0 : '';
            $curations = $curations->where('is_on',$on);
        }

        if (isset($keyword) && $keyword) {
            $curations = $curations->where(function ($query) use ($keyword) {
                $query->where('main_title', 'like', "%$keyword%")
                ->orWhere('sub_title', $keyword)
                ->orWhere('caption', $keyword);
            });
        }

        //在分頁之前計算數量
        $totalCurations=$curations->count();

        if (!isset($list)) {
            $list = 30;
            $compact = array_merge($compact, ['list']);
        }

        //找出最終資料
        $curations = $curations->orderBy('sort','asc')->paginate($list);
        $compact = array_merge($compact,['menuCode','curations','totalCurations']);
        return view('admin.curations.index',compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M7S1';
        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        $types = ['header' => 'Header','image' => '圖片','vendor' => '品牌','product' => '產品','event' => '活動', 'block' => '宮格', 'nowordblock' => '宮格(無字)'];
        $categories = CategoryDB::where('is_on',1)->select(['id','name'])->get();
        $vendors = VendorDB::where('is_on',1)->select(['id','name'])->get();
        $unSelectVendors = $vendors;
        return view('admin.curations.show',compact('menuCode','langs','types','unSelectVendors','categories','vendors'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CurationsRequest $request)
    {
        $data = $request->all();
        //資料處理
        isset($data['show_main_title']) ? $data['show_main_title'] = 1 : $data['show_main_title'] = 0;
        isset($data['show_main_title_background']) ? $data['show_main_title_background'] = 1 : $data['show_main_title_background'] = 0;
        isset($data['show_sub_title']) ? $data['show_sub_title'] = 1 : $data['show_sub_title'] = 0;
        isset($data['show_background_color']) ? $data['show_background_color'] = 1 : $data['show_background_color'] = 0;
        isset($data['show_background_image']) ? $data['show_background_image'] = 1 : $data['show_background_image'] = 0;
        isset($data['show_url']) ? $data['show_url'] = 1 : $data['show_url'] = 0;
        isset($data['url_open_window']) ? $data['url_open_window'] = 1 : $data['url_open_window'] = 0;
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;

        if($data['category'] == 'home' && ($data['type'] == 'vendor' || $data['type'] == 'product' || $data['type'] == 'image')){
            $type = $data['type'];
            $data['type'] == 'image' ? $type = 'photo' : '';
            //先建立舊的策展
            $oldCuration = OldCurationDB::create([
                'main_title' => $data['main_title'],
                'subtitle' => $data['sub_title'],
                'more_caption' => strip_tags($data['caption']),
                'more_caption_url' => $data['old_url'],
                'is_select' => $type,
                'layout' => $data['columns'],
                'text_layout' => $data['old_text_layout'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'sort' => $data['sort'],
                'is_on' => $data['is_on'],
            ]);
        }
        $data['category'] == 'home' && !empty($oldCuration) ? $data['old_curation_id'] = $oldCuration->id : $data['old_curation_id'] = null;
        $curation = CurationDB::create($data);

        $this->autoSort('home');

        //語言資料
        if(count($data['langs']) > 0){
            foreach($data['langs'] as $lang => $value){
                if($data['category'] == 'home' && !empty($oldCuration)){
                    $oldCuration->update([
                        'main_title_'.$lang => $value['main_title'],
                        'subtitle_'.$lang => $value['sub_title'],
                        'more_caption_'.$lang => $value['caption']
                    ]);
                }
                CurationLangDB::create([
                    'curation_id' => $curation->id,
                    'lang' => $lang,
                    'main_title' => $value['main_title'],
                    'sub_title' => $value['sub_title'],
                    'caption' => $value['caption'],
                ]);
            }
        }
        //檔案處理
        if($request->hasFile('background_image')){
            $request->id = $curation->id;
            $request->rowName = 'background_image';
            $curation->update(['background_image' => $this->storeFile($request)]);
        }
        return redirect()->route('admin.curations.show',$curation->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M7S1';
        $totalBlocks[1] = 0;
        $totalBlocks[2] = 0;
        $totalNoWordBlocks[1]=0;
        $totalNoWordBlocks[2]=0;
        $categories = CategoryDB::where('is_on',1)->select(['id','name'])->get();
        $curation = CurationDB::findOrFail($id);

        $langs = [['code'=>'en','name'=>'英文'],['code'=>'jp','name'=>'日文'],['code'=>'kr','name'=>'韓文'],['code'=>'th','name'=>'泰文']];
        if($curation->langs){
            foreach ($curation->langs as $lang) {
                for ($i=0;$i<count($langs);$i++) {
                    if ($lang->lang == $langs[$i]['code']) {
                        $langs[$i]['data'] = $lang->toArray();
                    }
                }
            }
        }
        if($curation->type == 'image'){ //圖片版型
            foreach ($curation->images as $image) {
                foreach($image->langs as $lang){
                    for($i=0;$i<count($langs);$i++){
                        if($lang->lang == $langs[$i]['code']){
                            $langs[$i]['imagedata'][$image->id] = $lang->toArray();
                        }
                    }
                }
            }
        }
        if($curation->type == 'event'){ //活動版型
            foreach ($curation->events as $event) {
                foreach($event->langs as $lang){
                    for($i=0;$i<count($langs);$i++){
                        if($lang->lang == $langs[$i]['code']){
                            $langs[$i]['eventdata'][$event->id] = $lang->toArray();
                        }
                    }
                }
            }
        }
        if($curation->type == 'block'){ //宮格版型
            foreach ($curation->blocks as $block) {
                $totalBlocks[$block->row]++;
                foreach($block->langs as $lang){
                    for($i=0;$i<count($langs);$i++){
                        if($lang->lang == $langs[$i]['code']){
                            $langs[$i]['blockdata'][$block->id] = $lang->toArray();
                        }
                    }
                }
            }
        }
        if($curation->type == 'nowordblock'){ //宮格版型(無字)
            foreach ($curation->nowordblocks as $nowordblock) {
                $totalNoWordBlocks[$nowordblock->row]++;
            }
        }
        $unSelectVendors = VendorDB::where('is_on',1);
        if($curation->type == 'vendor'){ //品牌版型
            $selectVendorIds = $curation->vendors->pluck('vendor_id')->all();
            $unSelectVendors = $unSelectVendors->whereNotIn('id',$selectVendorIds);
            foreach ($curation->vendors as $vendor) {
                foreach($vendor->langs as $lang){
                    for($i=0;$i<count($langs);$i++){
                        if($lang->lang == $langs[$i]['code']){
                            $langs[$i]['vendordata'][$vendor->id] = $lang->toArray();
                        }
                    }
                }
            }
        }
        if($curation->type == 'product'){ //產品版型
            foreach ($curation->products as $product) {
                $product->image = null;
                for($i=1; $i<=5; $i++){
                    if(!empty($product->{'new_photo'.$i})){
                        $product->image = $product->{'new_photo'.$i};
                        break;
                    }
                }
                foreach($product->langs as $lang){
                    for($i=0;$i<count($langs);$i++){
                        if($lang->lang == $langs[$i]['code']){
                            $langs[$i]['productdata'][$product->id] = $lang->toArray();
                        }
                    }
                }
            }
        }
        $unSelectVendors = $unSelectVendors->select(['id','name'])->orderBy('name','asc')->get();
        $vendors = VendorDB::where('is_on',1)->select(['id','name'])->orderBy('name','asc')->get();
        $types = ['header' => 'Header','vendor' => '品牌','product' => '產品','image' => '圖片','event' => '活動', 'block' => '宮格', 'nowordblock' => '宮格(無字)'];

        return view('admin.curations.show',compact('menuCode','langs','curation','types','totalBlocks','totalNoWordBlocks','unSelectVendors','categories','vendors'));
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
    public function update(CurationsRequest $request, $id)
    {
        $data = $request->all();
        $curation = CurationDB::findOrFail($id);
        //資料處理
        isset($data['show_main_title']) ? $data['show_main_title'] = 1 : $data['show_main_title'] = 0;
        isset($data['show_main_title_background']) ? $data['show_main_title_background'] = 1 : $data['show_main_title_background'] = 0;
        isset($data['show_sub_title']) ? $data['show_sub_title'] = 1 : $data['show_sub_title'] = 0;
        isset($data['show_background_color']) ? $data['show_background_color'] = 1 : $data['show_background_color'] = 0;
        isset($data['show_background_image']) ? $data['show_background_image'] = 1 : $data['show_background_image'] = 0;
        isset($data['show_url']) ? $data['show_url'] = 1 : $data['show_url'] = 0;
        isset($data['url_open_window']) ? $data['url_open_window'] = 1 : $data['url_open_window'] = 0;
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        $curation->type != $data['type'] ? $data['is_on'] = 0 : '';

        if($curation->category == 'home' && ($data['type'] == 'vendor' || $data['type'] == 'product' || $data['type'] == 'image')){
            $type = $data['type'];
            $data['type'] == 'image' ? $type = 'photo' : '';
            //找舊策展資料
            $oldCuration = OldCurationDB::find($curation->old_curation_id);
            if(!empty($oldCuration)){
                $oldCuration->update([
                    'main_title' => $data['main_title'],
                    'subtitle' => $data['sub_title'],
                    'more_caption' => strip_tags($data['caption']),
                    'more_caption_url' => $data['old_url'],
                    'is_select' => $type,
                    'layout' => $data['columns'],
                    'text_layout' => $data['old_text_layout'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'sort' => $data['sort'],
                    'is_on' => $data['is_on'],
                ]);
            }else{
                $oldCuration = OldCurationDB::create([
                    'main_title' => $data['main_title'],
                    'subtitle' => $data['sub_title'],
                    'more_caption' => strip_tags($data['caption']),
                    'more_caption_url' => $data['old_url'],
                    'is_select' => $type,
                    'layout' => $data['columns'],
                    'text_layout' => $data['old_text_layout'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'sort' => $data['sort'],
                    'is_on' => $data['is_on'],
                ]);
                $data['old_curation_id'] = $oldCuration->id;
            }
        }
        //語言資料
        foreach($data['langs'] as $lang => $value){
            if($curation->category == 'home' && !empty($oldCuration)){
                $oldCuration->update([
                    'main_title_'.$lang => $value['main_title'],
                    'subtitle_'.$lang => $value['sub_title'],
                    'more_caption_'.$lang => $value['caption']
                ]);
            }
            $find = CurationLangDB::where([['curation_id',$id],['lang',$lang]])->first();
            if($find){
                $find->update([
                    'main_title' => $value['main_title'],
                    'sub_title' => $value['sub_title'],
                    'caption' => $value['caption'],
                ]);
            }else{
                $find = CurationLangDB::create([
                    'curation_id' => $id,
                    'lang' => $lang,
                    'main_title' => $value['main_title'],
                    'sub_title' => $value['sub_title'],
                    'caption' => $value['caption'],
                ]);
            }
        }
        //檔案資料
        if($request->hasFile('background_image')){
            $request->id = $id;
            $request->rowName = 'background_image';
            $data['background_image']=$this->storeFile($request);
        }
        $curation->update($data);
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
        手動排序
    */
    public function sort(Request $request)
    {
        $ids = $request->id;
        $sorts = $request->sort;
        if(count($ids) == count($sorts)){
            for($i=0;$i<count($ids);$i++){
                $curation = CurationDB::with('oldCuration')->find($ids[$i]);
                !empty($curation) ? $curation->update(['sort' => $sorts[$i]]) : '';
                if(!empty($curation) &&$curation->category == 'home'){ //舊資料自動排序
                    !empty($curation->oldCuration) ? $oldCuration = $curation->oldCuration : $oldCuration = null;
                    !empty($oldCuration) ? $oldCuration->update(['sort' => $sorts[$i]]) : '';
                }
            }
        }
        return redirect()->back();
    }
    /*
        向上排序
    */
    public function sortup(Request $request)
    {
        $id = $request->id;
        $curation = CurationDB::findOrFail($id);
        $up = ($curation->sort) - 1.5;
        $curation->fill(['sort' => $up]);
        $curation->save();
        $this->autoSort($curation->category);
        return redirect()->back();
    }
    /*
        向下排序
    */
    public function sortdown(Request $request)
    {
        $id = $request->id;
        $curation = CurationDB::findOrFail($id);
        $up = ($curation->sort) + 1.5;
        $curation->fill(['sort' => $up]);
        $curation->save();
        $this->autoSort($curation->category);
        return redirect()->back();
    }
    /*
        自動排序處理
    */
    public function autoSort($category)
    {
        $curations = CurationDB::with('oldCuration')->where('category',$category)->orderBy('sort','asc')->get();
        $i = 1;
        foreach ($curations as $curation) {
            if($curation->category == 'home'){ //舊資料自動排序
                !empty($curation->oldCuration) ? $oldCuration = $curation->oldCuration : $oldCuration = null;
                !empty($oldCuration) ? $oldCuration->update(['sort' => $i]) : '';
            }
            $curation->update(['sort' => $i]);
            $i++;
        }
    }
    /*
        啟用或停用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        $curation = CurationDB::findOrFail($request->id);
        $curation->update(['is_on' => $is_on]);
        //啟用舊版策展
        if($curation->category == 'home' && ($curation->type == 'vendor' || $curation->type == 'product' || $curation->type == 'image')){
            $curation->type == 'image' ? $select = 'photo' : $select = $curation->type;
            $oldCuration = OldCurationDB::where('is_select',$select)->find($curation->old_curation_id);
            $oldCuration->update(['is_on' => $is_on]);
        }
        return redirect()->back();
    }
    /*
        檔案儲存
     */
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
        $fileName = $request->rowName.'_'.$request->id.'_'. Carbon::now()->timestamp . '.' . $ext;
        //變更尺寸寬高
        if($request->rowName == 'background_image'){
            $reSizeWidth = 1920;
            $reSizeHeigh = 1080;
        }else{
            $reSizeWidth = 1440;
            $reSizeHeigh = 760;
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
    public function getProducts(Request $request)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $products = ProductDB::join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')->whereIn($productTable.'.status',[-9,1]);
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
}
