<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use Hash;
use Session;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\GateAdmin as AdminDB;
use App\Models\GatePowerAction as PowerActionDB;
use App\Models\GateAdminPwdUpdateLog as AdminPwdUpdateLogDB;
use App\Models\GateAdminLoginLog as AdminLoginLogDB;
use App\Http\Requests\Admin\AdminsCreateRequest;
use App\Http\Requests\Admin\AdminsUpdateRequest;
use App\Http\Requests\Admin\ChangePassWordRequest;
use Illuminate\Validation\Rules\Password;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdminsDataExport;

class AdminsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 先經過 middleware 檢查
        $this->middleware('auth:admin');
        //改走cloudflare需抓x-forwareded-for
        if(!empty(request()->header('x-forwarded-for'))){
            $this->loginIp = request()->header('x-forwarded-for');
        }else{
            $this->loginIp = request()->ip();
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menuCode = 'M1S1';
        $appends = $compact = [];
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }
        if (!isset($is_on)) {
            $is_on = 2;
            $compact = array_merge($compact, ['is_on']);
        }
        if (!isset($keyword)) {
            $keyword = null;
            $compact = array_merge($compact, ['is_on']);
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }

        $is_on == 2 ? $admins = new AdminDB : $admins = AdminDB::where('is_on',$is_on);
        if(isset($keyword)){
            $admins = $admins->where('name','like',"%$keyword%")
            ->orWhere('account','like',"%$keyword%")
            ->orWhere('email','like',"%$keyword%");
        }

        $admins = $admins->select([
            '*',
            'pass_change' => AdminPwdUpdateLogDB::whereColumn('admin_pwd_update_logs.admin_id','admins.id')->select('created_at')->orderBy('created_at','desc')->limit(1),
        ]);
        $admins = $admins->orderBy('id','desc')->paginate($list);

        $totalAdmins = AdminDB::get()->count();
        $totalEnable = AdminDB::where('is_on',1)->get()->count();
        $totalDisable = AdminDB::where('is_on',0)->get()->count();
        $compact = array_merge($compact, ['menuCode','appends','admins','totalAdmins','totalEnable','totalDisable']);
        return view('admin.admins.index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M1S1';
        $powerActions = PowerActionDB::all();
        return view('admin.admins.show',compact('powerActions','menuCode'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AdminsCreateRequest $request)
    {
        $data = $request->all();
        $data['password'] = app('hash')->make($data['password']);
        $data['power'] = $request->power ? join(',',$request->power) : '';
        if(!empty($data['account'])){
            $admin = AdminDB::where('account',$data['account'])->first();
            if(empty($admin)){
                $admin = AdminDB::create($data);
                Session::put('success',$data['account']." 建立成功");
                return redirect()->route('admin.admins.show', $admin->id);
            }else{
                Session::put('error',$data['account']." 帳號已存在");
                return redirect()->back()->withInput($request->input());
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
        $menuCode = 'M1S1';
        $admin = AdminDB::findOrFail($id);
        $google2fa = new Google2FA();
        if(empty($admin->google2fa_secret)){
            $secretKey = $google2fa->generateSecretKey(32);
            $admin->update(['google2fa_secret' => $secretKey]);
        }else{
            $secretKey = $admin->google2fa_secret;
        }
        env('APP_ENV') == 'local' ? $companyName = 'iCarry TEST' : $companyName = 'iCarry Admin';
        $companyEmail = $admin->email;
        $google2faUrl = $google2fa->getQRCodeUrl($companyName,$companyEmail,$secretKey);
        $qrCodeUrl = QrCode::generate($google2faUrl);
        $powerActions = PowerActionDB::all();
        return view('admin.admins.show',compact('admin','powerActions','menuCode','qrCodeUrl'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AdminsUpdateRequest $request, $id)
    {
        // $request->validate([
        //     'password' => [
        //         'required',
        //         Password::min(8)
        //             ->mixedCase()
        //             ->letters()
        //             ->numbers()
        //             ->symbols()
        //             ->uncompromised(),
        //     ],
        // ]);

        $data = $request->all();

        //透過id找出管理者資料
        $admin = AdminDB::findOrFail($id);
        //比對密碼
        if($data['password'] == $admin->password){
            $data['password'] = $admin->password;
        }else{
            $data['password'] = app('hash')->make($request->password);
            $log = AdminPwdUpdateLogDB::create([
                'admin_id' => $admin->id,
                'password' => $data['password'],
                'ip' => $this->loginIp,
                'editor_id' => Auth::user()->id,
            ]);
        }
        $data['lock_on'] = 0;
        if($data['is_on'] == 3){
            $data['lock_on'] = $data['is_on'];
            $data['is_on'] = 1;
        }
        $admin->update($data);
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
        $admin = AdminDB::find($id)->delete();
        return redirect()->back();
    }

    /*
        啟用或禁用該帳號
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        AdminDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }

    /*
        解除帳號鎖定
     */
    public function unlock(Request $request, $id)
    {
        $admin = AdminDB::find($id);
        if(!empty($admin)){
            $name = $admin->name;
            $admin->update(['lock_on' => 0]);
            $log = AdminLoginLogDB::create([
                'admin_id' => $admin->id,
                'result' => '後台解鎖成功 ('.Auth::user()->name.'協助解鎖)',
                'ip' => $this->loginIp,
            ]);
            Session::put('success',"已解除 $name 帳號鎖定");
        }
        return redirect()->back();
    }
    /*
        搜尋姓名及帳號
    */
    // public function search(Request $request){
    //     if(!$request->has('keyword')){
    //         return redirect()->back();
    //     }
    //     $keyword = $request->keyword;
    //     $admins = AdminDB::where('name', 'LIKE', "%$keyword%")->orWhere('email', 'LIKE', "%$keyword%")->orderBy('id', 'DESC')->paginate(15);
    //     return view()->make('admin.admins.index', compact('admins', 'keyword'));
    // }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changePassWordForm()
    {
        $admin = AdminDB::find(Auth()->user()->id);
        $google2fa = new Google2FA();
        if(empty($admin->google2fa_secret)){
            $secretKey = $google2fa->generateSecretKey(32);
            $admin->update(['google2fa_secret' => $secretKey]);
        }else{
            $secretKey = $admin->google2fa_secret;
        }
        env('APP_ENV') == 'local' ? $companyName = 'iCarry TEST' : $companyName = 'iCarry Admin';
        $companyEmail = $admin->email;
        $google2faUrl = $google2fa->getQRCodeUrl($companyName,$companyEmail,$secretKey);
        $qrCodeUrl = QrCode::generate($google2faUrl);
        return view('admin.admins.change_password',compact('admin','qrCodeUrl'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changePassWord(ChangePassWordRequest $request)
    {
        $data = $request->all();
        if(!empty($data['newpass']) && !empty($data['oldpass'])){
            if(!Hash::check ($data['oldpass'], Auth()->user()->password)){
                return redirect()->back()->withErrors(['oldpass' => '舊密碼輸入錯誤']);
            }
            $data['password'] = app('hash')->make($request->newpass);
        }
        $admin = AdminDB::findOrFail(Auth::user()->id);
        $admin->update($data);
        if(!empty($data['newpass']) && !empty($data['oldpass'])){
            $log = AdminPwdUpdateLogDB::create([
                'admin_id' => $admin->id,
                'password' => $data['newpass'],
                'ip' => $this->loginIp,
                'editor_id' => Auth::user()->id,
            ]);
        }
        Session::put('success','個人資料變更成功');
        return redirect()->back();

    }

    public function export()
    {
        $exportFile = '管理員帳號匯出_'.date('YmdHis').'.xlsx';
        return Excel::download(new AdminsDataExport(), $exportFile);
    }
}
