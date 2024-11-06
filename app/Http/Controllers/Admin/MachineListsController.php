<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MachineList as MachineListDB;
use App\Models\Vendor as VendorDB;
use App\Models\VendorAccount as VendorAccountDB;
use App\Http\Requests\Admin\MachineListRequest;
use App\Exports\MachineListExport;
use Maatwebsite\Excel\Facades\Excel;


class MachineListsController extends Controller
{
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
        $menuCode = 'M10S1';
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
        $machines = MachineListDB::with('vendor','account');

        isset($company) ? $machines = $machines->whereIn('vendor_id',VendorDB::where('company','like',"%$company%")->select('id')->get()) : '';
        isset($vendor_name) ? $machines = $machines->whereIn('vendor_id',VendorDB::where('name','like',"%$vendor_name%")->select('id')->get()) : '';
        isset($account) ? $machines = $machines->whereIn('vendor_account_id',VendorAccountDB::where('account','like',"%$vendor_name%")->select('id')->get()) : '';
        isset($shop) ? $machines = $machines->where('name','like',"%$shop%") : '';
        isset($is_on) ? $machines = $machines->where('is_on',$is_on) : '';

        if (isset($sid)) {
            $id = ltrim(str_replace('C','',$sid),0);
            $machines = $machines->where('id',$id);
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }

        $machines = $machines->orderBy('id','asc')->paginate($list);;

        $compact = array_merge($compact,['menuCode','appends','machines']);
        return view('admin.acpay.machine_index',compact($compact));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M10S1';
        $appends = [];
        $compact = [];
        // $machine = MachineListDB::with('vendor','account')->findOrFail($id);
        $vendors = VendorDB::select(['id','name','is_on'])->orderBy('is_on','desc')->orderBy('created_at','desc')->get();
        // $accounts = VendorAccountDB::where('vendor_id',$machine->vendor_id)->select(['id','name','account'])->orderBy('created_at','desc')->get();
        $shippings = [
            ['name' => '機場提貨', 'shipping' => 'airport_shipping', 'box' => 'airport_box', 'base' => 'airport_base'],
            ['name' => '旅店提貨', 'shipping' => 'hotel_shipping', 'box' => 'hotel_box', 'base' => 'hotel_base'],
            ['name' => '現場提貨', 'shipping' => 'yourself_shipping', 'box' => 'yourself_box', 'base' => 'yourself_base'],
            ['name' => '寄送海外', 'shipping' => 'overseas_shipping', 'box' => 'overseas_box', 'base' => 'overseas_base'],
            ['name' => '寄送台灣', 'shipping' => 'taiwan_shipping', 'box' => 'taiwan_box', 'base' => 'taiwan_base'],
        ];
        $compact = array_merge($compact,['menuCode','appends','vendors','shippings']);
        return view('admin.acpay.machine_show',compact($compact));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MachineListRequest $request)
    {
        $data = $request->all();
        isset($data['airport_shipping']) ? $data['airport_shipping'] = 1 : $data['airport_shipping'] = 0;
        isset($data['hotel_shipping']) ? $data['hotel_shipping'] = 1 : $data['hotel_shipping'] = 0;
        isset($data['yourself_shipping']) ? $data['yourself_shipping'] = 1 : $data['yourself_shipping'] = 0;
        isset($data['overseas_shipping']) ? $data['overseas_shipping'] = 1 : $data['overseas_shipping'] = 0;
        isset($data['taiwan_shipping']) ? $data['taiwan_shipping'] = 1 : $data['taiwan_shipping'] = 0;
        isset($data['card_paying']) ? $data['card_paying'] = 1 : $data['card_paying'] = 0;
        isset($data['alipay_paying']) ? $data['alipay_paying'] = 1 : $data['alipay_paying'] = 0;
        isset($data['free_shipping']) ? $data['free_shipping'] = 1 : $data['free_shipping'] = 0;
        isset($data['can_cancel']) ? $data['can_cancel'] = 1 : $data['can_cancel'] = 0;
        isset($data['can_return']) ? $data['can_return'] = 1 : $data['can_return'] = 0;
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        // dd($data);
        $machine = MachineListDB::create($data);
        return redirect()->route('admin.acpaymachines.show',$machine->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M10S1';
        $appends = [];
        $compact = [];
        $machine = MachineListDB::with('vendor','account')->findOrFail($id);
        $vendors = VendorDB::select(['id','name','is_on'])->orderBy('is_on','desc')->orderBy('created_at','desc')->get();
        $accounts = VendorAccountDB::where('vendor_id',$machine->vendor_id)->select(['id','name','account'])->orderBy('created_at','desc')->get();
        $shippings = [
            ['name' => '機場提貨', 'shipping' => 'airport_shipping', 'box' => 'airport_box', 'base' => 'airport_base'],
            ['name' => '旅店提貨', 'shipping' => 'hotel_shipping', 'box' => 'hotel_box', 'base' => 'hotel_base'],
            ['name' => '現場提貨', 'shipping' => 'yourself_shipping', 'box' => 'yourself_box', 'base' => 'yourself_base'],
            ['name' => '寄送海外', 'shipping' => 'overseas_shipping', 'box' => 'overseas_box', 'base' => 'overseas_base'],
            ['name' => '寄送台灣', 'shipping' => 'taiwan_shipping', 'box' => 'taiwan_box', 'base' => 'taiwan_base'],
        ];
        $compact = array_merge($compact,['menuCode','appends','machine','vendors','accounts','shippings']);
        return view('admin.acpay.machine_show',compact($compact));
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
        isset($data['airport_shipping']) ? $data['airport_shipping'] = 1 : $data['airport_shipping'] = 0;
        isset($data['hotel_shipping']) ? $data['hotel_shipping'] = 1 : $data['hotel_shipping'] = 0;
        isset($data['yourself_shipping']) ? $data['yourself_shipping'] = 1 : $data['yourself_shipping'] = 0;
        isset($data['overseas_shipping']) ? $data['overseas_shipping'] = 1 : $data['overseas_shipping'] = 0;
        isset($data['taiwan_shipping']) ? $data['taiwan_shipping'] = 1 : $data['taiwan_shipping'] = 0;
        isset($data['card_paying']) ? $data['card_paying'] = 1 : $data['card_paying'] = 0;
        isset($data['alipay_paying']) ? $data['alipay_paying'] = 1 : $data['alipay_paying'] = 0;
        isset($data['free_shipping']) ? $data['free_shipping'] = 1 : $data['free_shipping'] = 0;
        isset($data['can_cancel']) ? $data['can_cancel'] = 1 : $data['can_cancel'] = 0;
        isset($data['can_return']) ? $data['can_return'] = 1 : $data['can_return'] = 0;
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        // dd($data);
        $machine = MachineListDB::findOrFail($id);
        $machine->update($data);
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
        MachineListDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }
    /*
        匯出
     */
    public function export(Request $request)
    {
        $exportFile = '機台資料匯出_'.date('YmdHis');
        return Excel::download(new MachineListExport(), $exportFile.'.xlsx');
        return redirect()->back();
    }
    /*
        商家資料
     */
    public function getVendor(Request $request)
    {
        $id = $request->id;
        $vendor = VendorDB::find($id);
        $vendor->accounts = VendorAccountDB::where([['vendor_id',$vendor->id],['pos_admin',1]])->get();
        return $vendor;
    }
}
