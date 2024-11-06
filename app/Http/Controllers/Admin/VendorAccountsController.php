<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorAccount as VendorAccountDB;
use App\Models\iCarryVendorShop as VendorShopDB;
use App\Models\GateAdminLoginLog as AdminLoginLogDB;
use App\Http\Requests\Admin\VendorAccountsRequest;
use Validator;
use Session;
use Illuminate\Support\Str;
use App\Traits\VendorFunctionTrait;

class VendorAccountsController extends Controller
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
        $menuCode = 'M2S3';
        $compact = $appends = [];
        $accounts = $this->getVendorAccountData(request(),'index');

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

        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $vendorShopTable = env('DB_ICARRY').'.'.(new VendorShopDB)->getTable();
        $vendorAccountTable = env('DB_ICARRY').'.'.(new VendorAccountDB)->getTable();
        $userId = auth('admin')->user()->id;

        $userId == 14 ? $totalAccounts = VendorAccountDB::join($vendorTable,$vendorTable.'.id',$vendorAccountTable.'.vendor_id')->where($vendorTable.'.categories','like',"%17%")->count() : $totalAccounts = VendorAccountDB::count();
        $userId == 14 ? $totalEnable = VendorAccountDB::join($vendorTable,$vendorTable.'.id',$vendorAccountTable.'.vendor_id')->where($vendorTable.'.categories','like',"%17%")->where($vendorAccountTable.'.is_on',1)->count() : $totalEnable = VendorAccountDB::where('is_on',1)->count();
        $userId == 14 ? $totalDisable = VendorAccountDB::join($vendorTable,$vendorTable.'.id',$vendorAccountTable.'.vendor_id')->where($vendorTable.'.categories','like',"%17%")->where($vendorAccountTable.'.is_on',0)->count() : $totalDisable = VendorAccountDB::where('is_on',0)->count();
        $compact = array_merge($compact, ['menuCode','accounts','totalAccounts','totalEnable','totalDisable','appends']);
        return view('admin.vendors.accounts_index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(request()->has('vendor_id')){
            $vendorId = (INT)urldecode(request()->vendor_id);
        }else{
            return redirect()->back();
        }
        $menuCode = 'M2S3';
        $shops = VendorShopDB::where('vendor_id',$vendorId)->get();
        return view('admin.vendors.accounts_show', compact('menuCode','vendorId','shops'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VendorAccountsRequest $request)
    {
        //整理資料
        $data = $request->all();
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        isset($data['shop_admin']) ? $data['shop_admin'] = 1 : $data['shop_admin'] = 0;
        isset($data['pos_admin']) ? $data['pos_admin'] = 1 : $data['pos_admin'] = 0;
        $data['password'] = sha1($data['password']); //密碼使用舊方式sha1編碼
        //舊的密碼資料
        $data['pwd'] = $data['password'];
        $data['icarry_token'] = Str::uuid()->toString(); //跨站認證碼
        //檢查account是否存在
        if( VendorAccountDB::where('account',$data['account'])->count() > 0){
            return redirect()->back()->withInput($request->all())->withError('該帳號已存在，請重新輸入');
        }
        //新增後跳轉
        $account = VendorAccountDB::create($data);
        $message = '商家帳號 '.$request->account.' 已建立成功！';
        Session::put('success', $message);
        Session::put('vendorAccountShow','show active');
        return redirect()->route('admin.vendors.show',$data['vendor_id'].'#vendor_account');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        request()->has('from') ? $from = urlencode(request()->from) : $from = '';
        $account = VendorAccountDB::findOrFail($id);
        $shops = VendorShopDB::where('vendor_id',$account->vendor_id)->get();
        $menuCode = 'M2S3';
        return view('admin.vendors.accounts_show', compact('menuCode','account','shops','from'));
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
        //找出該筆資料
        $account = VendorAccountDB::findOrFail($id);
        //整理資料
        $data = $request->all();
        isset($data['lock_on']) ? $data['lock_on'] = 10 : $data['lock_on'] = 0;
        isset($data['is_on']) ? $data['is_on'] = 1 : $data['is_on'] = 0;
        isset($data['shop_admin']) ? $data['shop_admin'] = 1 : $data['shop_admin'] = 0;
        isset($data['pos_admin']) ? $data['pos_admin'] = 1 : $data['pos_admin'] = 0;
        $data['password'] == null ? $data['password'] = $account->password : $data['password'] = sha1($data['password']);
        //舊的密碼資料
        $data['pwd'] = $data['password'];
        //檢查是否變更帳號，若有檢查是否已經有相同的帳號存在
        if($data['account'] != $account->account){
            if(VendorAccountDB::where('account',$data['account'])->count() > 0){
                return redirect()->back()->withErrors(['account' => '該帳號已經存在，請重新輸入']);
            }
        }
        //更新
        $account->update($data);

        if(request()->from){
            return redirect()->route('admin.vendors.show',$account->vendor_id.'#'.$request->from);
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
        $account = VendorAccountDB::find($id)->delete();
        if(request()->has('from')){
            return redirect()->route('admin.vendors.show',request()->vendor_id.request()->from);
        }
        return redirect()->back();
    }
    /*
        啟用或停用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = 1 : $is_on = 0;
        VendorAccountDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        if(request()->has('from')){
            return redirect()->route('admin.vendors.show',request()->vendor_id.request()->from);
        }
        return redirect()->back();
    }

    /*
        解除帳號鎖定
     */
    public function unlock(Request $request, $id)
    {
        $adminName = auth('admin')->user()->name;
        $admin = VendorAccountDB::find($id);
        if(!empty($admin)){
            $account = $admin->account;
            $admin->update(['lock_on' => 0]);
            $log = AdminLoginLogDB::create([
                'admin_id' => auth('admin')->user()->id,
                'result' => "解鎖商家帳號 $account 成功 ( $adminName 協助解鎖)",
                'ip' => $request->ip(),
                'site' => 'iCarry後台',
            ]);
            Session::put('success',"已解除 $account 帳號鎖定");
        }
        return redirect()->back();
    }
}
