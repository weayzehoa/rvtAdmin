<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryUser as UserDB;
use App\Models\iCarryUserPoint as UserPointDB;
use App\Models\GateSmsLog as SmsLogDB;
use App\Models\iCarryServiceMessage as ServiceMessageDB;
use App\Models\ShoppingCart as ShoppingCartDB;
use App\Models\GateSystemSetting as SystemSettingDB;
use App\Models\iCarryShippingMethod as ShippingMethodDB;
use App\Models\iCarryCountry as CountryDB;

use App\Jobs\AdminSendSMS;
use App\Jobs\UserPointsImportJob;
use App\Http\Requests\Admin\UsersRequest;
use Session;
use Carbon\Carbon;
use Twilio\Rest\Client as Twilio;
use AWS;
use Nexmo;
use DB;

use App\Traits\UserFunctionTrait;

class UsersController extends Controller
{
    use UserFunctionTrait;
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
        $menuCode = 'M5S1';
        $appends = [];
        $compact = [];
        $totalUsers = 0;

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        //找出資料
        $users = $this->getUserData(request(),'index');

        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }

        $compact = array_merge($compact, ['menuCode','users','appends']);
        return view('admin.users.index', compact($compact));
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
        $menuCode['user']='M5S1';
        $user = UserDB::with('shoppingCarts','smsLogs')->findOrFail($id);
        request()->request->add(['id' => $id]);
        $user = $this->getUserData(request(),'show');
        $points = $user->pointLogs()->orderBy('create_time','desc')->paginate(15);
        $systemSetting = SystemSettingDB::first();
        $shoppingCarts = [];
        //計算及資料變更
        $totalWeights = $totalPrice = $totalQtys = 0;
        if(count($user->shoppingCarts) > 0){
            $shoppingCarts = $user->shoppingCarts;
            foreach ($shoppingCarts as $shoppingCart) {
                $shoppingCart->gross_weight = $shoppingCart->gross_weight * $systemSetting->gross_weight_rate;
                $totalWeights += $shoppingCart->gross_weight * $shoppingCart->quantity;
                $totalPrice += $shoppingCart->quantity * $shoppingCart->price;
                $totalQtys += $shoppingCart->quantity;
            }
            $shoppingCarts->totalWeights = $totalWeights;
            $shoppingCarts->totalPrice = $totalPrice;
            $shoppingCarts->totalQtys = $totalQtys;
        }
        return view('admin.users.show',compact('menuCode','user','points','shoppingCarts'));
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
    public function update(UsersRequest $request, $id)
    {
        $data = $request->all();
        $user = UserDB::findOrFail($id);
        // isset($data['status']) ? $data['status'] = 1 : $data['status'] = -1;
        if(!empty($data['mobile'])){
            $data['mobile'] = substr($data['mobile'],0,20);
            $key = env('APP_AESENCRYPT_KEY');
            $mobile = $data['mobile'];
            $data['mobile'] = DB::raw("AES_ENCRYPT('$mobile', '$key')");
        }
        $user = $user->update($data);
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
        isset($request->status) ? $status = $request->status : $status = -1;
        UserDB::findOrFail($request->id)->update(['status' => $status]);
        return redirect()->back();
    }
    /*
        標記或取消標記
     */
    public function mark(Request $request)
    {
        isset($request->is_mark) ? $is_mark = $request->is_mark : $is_mark = 0;
        UserDB::findOrFail($request->id)->update(['is_mark' => $is_mark]);
        return redirect()->back();
    }
    /*
        推薦人資料
     */
    public function getIntro(Request $request)
    {
        $id = (int)request()->id;
        $user = userDB::findOrFail($id);
        $intros = userDB::where('refer_id',$id)->get();
        $total = $intros->count();
        $data = collect(['intros' => $intros, 'user' => $user, 'total' => $total]);
        return response($data);
    }
    /*
        新增購物金
     */
    public function addPoints(Request $request)
    {
        $id = (int)request()->id;
        $data = $request->all();
        $points = (int)$request->points;
        $reason = $request->point_type;
        $user = UserDB::findOrFail($id);

        if(!$request->point_type || $request->points == 0){
            $message = '請填寫原因(必填)及購物金數量(不可為0)';
            Session::put('error', $message);
            return redirect()->back();
        }
        $data['user_id'] = $user->id;
        $data['balance'] = $user->points + $points;
        if($request->points > 0){
            $data['dead_time'] = Carbon::now()->addMonth(6);
        }else{
            $data['dead_time'] = NULL;
        }
        UserPointDB::create($data);
        $user->update(['points'=>$data['balance']]);
        return redirect()->back();
    }
    /*
        發送簡訊
     */
    public function sendSms(Request $request)
    {
        $id = (int)request()->id;
        $data = $request->all();
        $user = $this->getUserData(request(),'show');
        $data['admin_id'] = auth('admin')->user()->id;
        $data['user_id'] = $id;
        $data['message'] = $request->message;
        $data['sms_id'] = time();
        $data['phone'] = $user->nation.$user->mobile;
        if(!empty($data['message'])){
            AdminSendSMS::dispatchNow($data);
            $message = "簡訊已傳送給 $user->nation$user->mobile";
            Session::put('success', $message);
            return redirect()->route('admin.users.show',$id.'#user-sms');
        }else{
            $message = "簡訊內容不能為空值";
            Session::put('error', $message);
            return redirect()->route('admin.users.show',$id.'#user-sms')->withErrors(['message' => '簡訊內容不能為空值']);
        }
    }

    public function import(Request $request)
    {
        $message = null;
        if ($request->hasFile('filename')) {
            $file = $request->file('filename');
            $uploadedFileMimeType = $file->getMimeType();
            $excelMimes = ['application/excel','application/vnd.ms-excel','application/vnd.msexcel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            if(!in_array($uploadedFileMimeType, $excelMimes)){
                $message = "檔案格式錯誤，$request->type 只接受 Excel 檔案格式。";
            }
            if(!empty($message)){
                Session::put('error', $message);
                return redirect()->back();
            }else{
                UserPointsImportJob::dispatchNow($request); //直接馬上處理
                Session::put('success', '匯入購物金已完成。');
            }
        }
        return redirect()->back();
    }

}
