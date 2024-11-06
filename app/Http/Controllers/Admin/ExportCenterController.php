<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GateAdmin as AdminDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\GateExportCenter as ExportCenterDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarryProductModel as ProductModelDB;
use DB;
use Carbon\Carbon;
use App\Jobs\AdminExportJob;
use Session;
use App\Export\ProductExport;

class ExportCenterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        $appends = [];
        $compact = [];
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }
        if (!isset($list)) {
            $list = 15;
            $compact = array_merge($compact, ['list']);
        }
        $exports = ExportCenterDB::join('admins','admins.id','export_centers.admin_id');
        $exports = $exports->where('export_centers.admin_id',auth('admin')->user()->id);
        $exports = $exports->whereBetween('export_centers.created_at',[Carbon::now()->subDays(14),Carbon::now()]);
        !empty($cate) ? $exports = $exports->where('cate',$cate) : '';
        $exports = $exports->select([
            'export_centers.id',
            'export_centers.export_no',
            'export_centers.condition',
            'export_centers.name',
            'export_centers.start_time',
            'export_centers.end_time',
            'export_centers.filename',
            'admins.id as admin_id',
            'admins.name as admin',
            DB::raw("(CASE WHEN export_centers.cate = 'orders' THEN '訂單資料' WHEN export_centers.cate = 'products' THEN '商品資料' WHEN export_centers.cate = 'vendors' THEN '商家資料' WHEN export_centers.cate = 'users' THEN '使用者資料' END) as cate"),
        ])->orderBy('export_centers.created_at','desc')->paginate($list);
        foreach($exports as $export){
            $export->condition = json_decode($export->condition,true);
            if(!empty($export->condition['id'])){
                if($export->condition['model'] == 'products'){
                    $export->skus = ProductModelDB::whereIn('product_id',$export->condition['id'])->select('sku')->get();
                }elseif($export->condition['model'] == 'orders'){
                    $export->orderNumbers = OrderDB::whereIn('id',$export->condition['id'])->select('order_number')->get();
                }
            }
            !empty($export->condition['con']) ? $export->cons = $export->condition['con'] : '';
        }
        $cates = [
            //  ['value' => 'orders', 'name' => '訂單資料'],
             ['value' => 'products', 'name' => '商品資料'],
             ['value' => 'vendors', 'name' => '商家資料'],
             ['value' => 'users', 'name' => '使用者資料'],
        ];
        $categories = CategoryDB::orderBy('is_on','desc')->get();
        $vendors = VendorDB::orderBy('is_on','desc')->orderBy('id','desc')->get();
        $compact = array_merge($compact, ['exports','list','appends','cates','categories','vendors']);
        return view('admin.exportcenter.index',compact($compact));
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($id == 0){
            $ids = request()->ids;
            $ids =  explode(',',$ids);
            if(is_array($ids)){
                $exports = ExportCenterDB::whereIn('id',$ids)->get();
                foreach($exports as $export){
                    unlink(public_path().'/exports/'.$export->filename);
                    $export->delete();
                }
            }
        }elseif(is_numeric($id) && $id > 0){
            $export = ExportCenterDB::findOrFail($id);
            unlink(public_path().'/exports/'.$export->filename);
            $export->delete();
        }
        return redirect()->back();
    }

    public function export(Request $request)
    {
        //將進來的資料作參數轉換及附加到$param中
        foreach ($request->all() as $key => $value) {
            $param[$key] = $value;
        }
        $method = null;
        $url = 'https://'.env('ADMIN_DOMAIN').'/exportcenter';
        $param['admin_id'] = auth()->user()->id;
        $param['admin_name'] = auth()->user()->name;
        $param['method'] == 'selected' ? $method = '自行勾選' : '';
        $param['method'] == 'allOnPage' ? $method = '目前頁面全選' : '';
        $param['method'] == 'byQuery' ? $method = '依查詢條件' : '';
        $param['method'] == 'allData' ? $method = '全部資料' : '';
        $param['name'] = $param['filename'].'_'.$method;
        $param['export_no'] = time();
        $param['start_time'] = date('Y-m-d H:i:s');
        $param['cate'] == 'pdf' ? $param['filename'] = $param['name'].'_'.time().'.pdf' : $param['filename'] = $param['name'].'_'.time().'.xlsx';
        $message = $param['name'].'，工作單號：'.$param['export_no'].'<br>匯出已於背端執行，請過一段時間至匯出中心下載，<br>檔案名稱：'.$param['filename'].'<br>匯出中心連結：<a href="'.$url.'" target="_blank"><span class="text-danger">'.$url.'</span></a>';
        // $param['store'] = true;

        return AdminExportJob::dispatchNow($param); //直接下載
        // //本機測試用
        // if(env('APP_ENV') == 'local'){
        //     return AdminExportJob::dispatchNow($param); //直接馬上下載則必須使用 return
        // }else{
        //     $param['store'] = true;
        //     AdminExportJob::dispatch($param); //放入隊列
        // }
        // Session::put('info', $message);
        return redirect()->back();
    }
}
