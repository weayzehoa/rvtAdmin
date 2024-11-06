<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Arcanedev\NoCaptcha\Rules\CaptchaRule;
use Auth;
use App\Models\GateAdmin as AdminDB;
use App\Models\GateAdminPwdUpdateLog as AdminPwdUpdateLogDB;
use App\Models\GateAdminLoginLog as AdminLoginLogDB;
use App\Models\GateIpAddress as IpAddressDB;
use DB;
use Hash;
use Session;
use Carbon\Carbon;
use App\Jobs\AdminSendSMS;
use App\Http\Requests\Admin\PasswordChangeRequest;
use App\Traits\GenerallyFunctionTrait;
use PragmaRX\Google2FA\Google2FA;

class AdminLoginController extends Controller
{
    use GenerallyFunctionTrait;

    // 先經過 middleware 檢查
    public function __construct()
    {
        $this->middleware('guest:admin', ['except' => ['showPwdChangeForm','passwordChange','showLoginForm','logout','show2faForm']]);
        //改走cloudflare需抓x-forwareded-for
        if(!empty(request()->header('x-forwarded-for'))){
            $this->loginIp = request()->header('x-forwarded-for');
        }else{
            $this->loginIp = request()->ip();
        }
    }

    // 顯示 admin.login form 表單視圖
    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function showOtpForm()
    {
        $data = Session::get('adminData');
        $id = $data['id'];
        $otpTime = $data['otpTime'];
        $last3Code = $data['last3Code'];
        $compact = ['last3Code','otpTime','id'];
        return view('admin.otp',compact($compact));
    }

    public function otp(Request $request)
    {
        $adminUser = AdminDB::find($request->id);
        if(!empty($adminUser)){
            if($request->otp == $adminUser->otp){
                $now = date('Y-m-d H:i:s');
                if(strtotime($now) <= strtotime($adminUser->otp_time)){
                    $adminUser->update(['lock_on' => 0]);
                    Auth::guard('admin')->login($adminUser);
                    // 驗證無誤 記錄後轉入 dashboard
                    $log = AdminLoginLogDB::create([
                        'admin_id' => $adminUser->id,
                        'result' => $adminUser->name.' 登入成功',
                        'ip' => $this->loginIp,
                        'site' => 'Admin後台',
                    ]);
                    activity('後台管理')->causedBy($adminUser)->log('登入成功');
                    return redirect()->intended(route('admin.dashboard'));
                }else{
                    $message = '驗證碼已逾時，請按返回登入重新登入';
                    return redirect()->back()->withInput($request->only('id','otp','last3Code'))->withErrors(['otp' => $message, 'return' => 'yes']);
                }
            }else{
                $adminUser->lock_on < 3 ? $adminUser->increment('lock_on') : '';
                $message = '驗證碼錯誤！還剩 '.(3 - $adminUser->lock_on).' 次機會';
                $adminUser->lock_on >= 3 ? $message = '帳號已被鎖定！請聯繫管理員。' : '';
                if($adminUser->lock_on >= 3){
                    $log = AdminLoginLogDB::create([
                        'admin_id' => $adminUser->id,
                        'result' => '驗證碼輸入錯誤三次，帳號鎖定。',
                        'ip' => $this->loginIp,
                        'site' => 'Admin後台',
                    ]);
                    $message = '帳號已被鎖定！請聯繫管理員。';
                }
                return redirect()->back()->withInput($request->only('id','otp','last3Code'))->withErrors(['otp' => $message]);
            }
        }else{
            return redirect()->to('login');
        }
    }

    public function show2faForm()
    {
        $data = Session::get('adminData');
        $id = $data['id'];
        $compact = ['id'];
        return view('admin.2fa',compact($compact));
    }

    public function verify2fa(Request $request)
    {
        $adminUser = AdminDB::find($request->id);
        if(!empty($adminUser)){
            $google2fa = new Google2FA();
            $secretKey = $adminUser->google2fa_secret;
            $valid = $google2fa->verifyKey($secretKey, $request->verify);
            if($valid == true){
                $adminUser->update(['lock_on' => 0]);
                Auth::guard('admin')->login($adminUser);
                // 驗證無誤 記錄後轉入 dashboard
                $log = AdminLoginLogDB::create([
                    'admin_id' => $adminUser->id,
                    'result' => $adminUser->name.' 登入成功',
                    'ip' => $this->loginIp,
                    'site' => '中繼後台',
                ]);
                activity('後台管理')->causedBy($adminUser)->log('登入成功');
                return redirect()->intended(route('admin.dashboard'));
            }else{
                $adminUser->lock_on < 3 ? $adminUser->increment('lock_on') : '';
                $message = '驗證碼錯誤！還剩 '.(3 - $adminUser->lock_on).' 次機會';
                $adminUser->lock_on >= 3 ? $message = '帳號已被鎖定！請聯繫管理員。' : '';
                if($adminUser->lock_on >= 3){
                    $log = AdminLoginLogDB::create([
                        'admin_id' => $adminUser->id,
                        'result' => '驗證碼輸入錯誤三次，帳號鎖定。',
                        'ip' => $this->loginIp,
                        'site' => '中繼後台',
                    ]);
                    $message = '帳號已被鎖定！請聯繫管理員。';
                }
                return redirect()->back()->withInput($request->only('id'))->withErrors(['verify' => $message]);
            }
        }else{
            return redirect()->to('login');
        }
    }

    // 登入
    public function login(Request $request)
    {
        // 驗證表單資料
        $this->validate($request, [
            'account'   => 'required',
            'password' => 'required|min:6',
            'g-recaptcha-response' => ['required', new CaptchaRule],
        ]);
        $adminUser = AdminDB::where('account',$request->account)->first();
        if(!empty($adminUser)){
            if($adminUser->lock_on < 3){
                $changeLog = AdminPwdUpdateLogDB::where('admin_id',$adminUser->id)
                ->select([DB::raw("DATEDIFF(NOW(),admin_pwd_update_logs.created_at) as last_modified")])
                ->orderBy('created_at','desc')->first();
                //直接撈資料表出來比對密碼方式
                $chkPassword = Hash::check($request->password, $adminUser->password);
                //檢查變更密碼是否超過90天
                if(env('APP_ENV') != 'local' && ($adminUser->password == null || empty($changeLog) || $changeLog->last_modified >= 90)){
                    // 轉至變更密碼表單
                    return redirect()->to('passwordChange');
                }elseif($chkPassword){
                    $passedIps = IpAddressDB::where('disable',1)->select('ip')->get()->pluck('ip')->all();
                    if(in_array($this->loginIp,$passedIps)){
                        $adminUser->update(['lock_on' => 0]);
                        Auth::guard('admin')->login($adminUser);
                        // 驗證無誤 記錄後轉入 dashboard
                        $log = AdminLoginLogDB::create([
                            'admin_id' => $adminUser->id,
                            'result' => $adminUser->name.' 登入成功',
                            'ip' => $this->loginIp,
                            'site' => 'Admin後台',
                        ]);
                        activity('後台管理')->causedBy($adminUser)->log('登入成功');
                        return redirect()->intended(route('admin.dashboard'));
                    }elseif($adminUser->mobile == null){
                        $message = '尚未設定電話號碼，請聯繫管理員';
                        $log = AdminLoginLogDB::create([
                            'admin_id' => $adminUser->id,
                            'result' => '登入失敗，尚未設定電話號碼！',
                            'ip' => $this->loginIp,
                            'site' => 'Admin後台',
                        ]);
                    }elseif($adminUser->lock_on <= 2){
                        $id = $adminUser->id;
                        if($adminUser->verify_mode == 'sms'){
                            $code = rand(100000,999999);
                            $otpTime = Carbon::now()->addMinutes(5);
                            $last3Code = substr($adminUser->mobile,-3);
                            Session::put('adminData',['id'=>$id,'otpTime'=>$otpTime,'last3Code'=>$last3Code]);
                            $adminUser->update(['otp' => $code, 'otp_time' => $otpTime]);
                            $sms['return'] = true;
                            $sms['admin_id'] = $adminUser->id;
                            !empty($adminUser->sms_vendor) ? $sms['supplier'] = $adminUser->sms_vendor : '';
                            $sms['phone'] = strstr($adminUser->mobile,'+') ? $adminUser->mobile : '+886'.ltrim($adminUser->mobile,'0');
                            $sms['message'] = "iCarry 後台 OTP： $code ；若不是您本人操作卻收到此簡訊，請立即通知iCarry公司群組並標記技術部。";
                            $result = AdminSendSMS::dispatchNow($sms);
                            if($result['status'] == '傳送成功'){
                                return redirect()->to('otp');
                            }else{
                                $smsVendor = $result['sms_vendor'];
                                $message = "$smsVendor 簡訊傳送失敗，請聯繫系統管理員";
                                Session::put('error',"$smsVendor 簡訊傳送失敗，請聯繫系統管理員");
                            }
                        }else{
                            Session::put('adminData',['id'=>$id]);
                            return redirect()->to('2fa');
                        }
                    }else{
                        $message = '帳號已被鎖定！請聯繫管理員。';
                        $log = AdminLoginLogDB::create([
                            'admin_id' => $adminUser->id,
                            'result' => '登入失敗，帳號已被鎖定！',
                            'ip' => $this->loginIp,
                            'site' => 'Admin後台',
                        ]);
                    }
                }elseif($adminUser->is_on == 0){
                    $message = '帳號已被停用！';
                    $log = AdminLoginLogDB::create([
                        'admin_id' => $adminUser->id,
                        'result' => '登入失敗，帳號已被停用！',
                        'ip' => $this->loginIp,
                        'site' => 'Admin後台',
                    ]);
                }else{
                    $adminUser->lock_on < 3 ? $adminUser->increment('lock_on') : '';
                    $message = '帳號密碼錯誤！還剩 '.(3 - $adminUser->lock_on).' 次機會';
                    $adminUser->lock_on >= 3 ? $message = '帳號已被鎖定！請聯繫管理員。' : '';
                    if($adminUser->lock_on >= 3){
                        $log = AdminLoginLogDB::create([
                            'admin_id' => $adminUser->id,
                            'result' => '密碼輸入錯誤三次，帳號鎖定。',
                            'ip' => $this->loginIp,
                            'site' => 'Admin後台',
                        ]);
                    }
                }
            }else{
                $message = '帳號已被鎖定！請聯繫管理員。';
                $log = AdminLoginLogDB::create([
                    'account' => $request->account,
                    'result' => '登入失敗，帳號已被鎖定',
                    'ip' => $this->loginIp,
                    'site' => 'Admin後台',
                ]);
            }
            return redirect()->back()->withInput($request->only('account', 'remember'))->withErrors(['account' => $message]);
        }
        $log = AdminLoginLogDB::create([
            'account' => $request->account,
            'result' => '登入失敗',
            'ip' => $this->loginIp,
            'site' => 'Admin後台',
        ]);
        // 驗證失敗 返回並拋出表單內容 只拋出 account 與 remember 欄位資料
        // 訊息 [使用者名稱或密碼錯誤] 為了不讓別人知道到底帳號是否存在
        return redirect()->back()->withInput($request->only('account', 'remember'))->withErrors(['account' => trans('auth.failed')]);
    }

    // 登出
    public function logout()
    {
        // 紀錄行為
        $adminuser = AdminDB::find(Auth::guard('admin')->id());
        activity('後台管理')->causedBy($adminuser)->log('登出成功');
        $log = AdminLoginLogDB::create([
            'admin_id' => $adminuser->id,
            'result' => '登出成功',
            'ip' => $this->loginIp,
            'site' => 'Admin後台',
        ]);
        // 登出
        Auth::guard('admin')->logout();
        return redirect('/');
    }

    public function showPwdChangeForm()
    {
        return view('admin.change_password');
    }

    public function passwordChange(PasswordChangeRequest $request)
    {
        $admin = AdminDB::where([['account',$request->account],['is_on',1],['lock_on',0]])
            ->select([
                '*',
                'last_modified_pwd' => AdminPwdUpdateLogDB::whereColumn('admin_pwd_update_logs.admin_id','admins.id')
                    ->select('password')->orderBy('created_at','desc')->limit(1),
            ])->first();

            if(!empty($admin)){
                if($admin->password == null){
                    //儲存新密碼並記錄
                    $newPassWord = app('hash')->make($request->newpass);
                    $admin->update(['password' => $newPassWord]);
                    $log = AdminPwdUpdateLogDB::create([
                        'admin_id' => $admin->id,
                        'password' => $newPassWord,
                        'ip' => $this->loginIp,
                        'editor_id' => $admin->id,
                    ]);
                    Session::put('success','密碼已更新，請重新登入。');
                    return redirect('/');
                }else{
                    if(!Hash::check ($request->oldpass, $admin->password)){
                        return redirect()->back()->withInput($request->only('account'))->withErrors(['oldpass' => '舊密碼輸入錯誤']);
                    }elseif(Hash::check ($request->newpass, $admin->last_modified_pwd)){
                        return redirect()->back()->withInput($request->only('account'))->withErrors(['oldpass' => '新密碼不可與上次修改的密碼相同']);
                    }else{ //儲存新密碼並記錄
                        $newPassWord = app('hash')->make($request->newpass);
                        $admin->update(['password' => $newPassWord]);
                        $log = AdminPwdUpdateLogDB::create([
                            'admin_id' => $admin->id,
                            'password' => $newPassWord,
                            'ip' => $this->loginIp,
                            'editor_id' => $admin->id,
                        ]);
                        Session::put('success','密碼已更新，請重新登入。');
                        return redirect('/');
                    }
                }
        }
        return redirect()->back()->withErrors(['account' => '帳號不存在/禁用/鎖定。']);
    }
}
