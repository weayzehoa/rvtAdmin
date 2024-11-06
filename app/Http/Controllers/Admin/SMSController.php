<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\SendSMSRequest;
use App\Jobs\AdminSendSMS;
use Session;

class SMSController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * 顯示 SMS 表單.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendSMSForm()
    {
        $menuCode = 'M15S2';
        return view('admin.sms.SendSMSForm',compact('menuCode'));
    }
    /**
     * 傳送 SMS
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendSMS(SendSMSRequest $request)
    {
        $phones = explode(',',$request->phones);
        !empty($request->vendor) ? $sms['supplier'] = $request->vendor : '';
        $sms['message'] = $request->content;
        $sms['admin_id'] = auth('admin')->user()->id;
        for($i=0;$i<count($phones);$i++){
            if(!empty($phones[$i])){
                $sms['phone'] = '+'.str_replace(['+'],[''],$phones[$i]);
                AdminSendSMS::dispatchNow($sms);
            }
        }
        Session::put('success', '簡訊已傳送。');
        return redirect()->back();
    }
}
