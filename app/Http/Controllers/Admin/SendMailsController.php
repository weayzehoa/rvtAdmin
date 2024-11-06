<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use View;
use Mail;
use Session;
use Notification;
use App\Mail\AdminSendMail;
use App\Http\Requests\Admin\SendMailRequest;

use App\Http\Requests\Admin\SendNoteRequest;
use App\Notifications\Admin\SendNoteNotification;

use App\Http\Requests\Admin\SendQueuesRequest;
use App\Jobs\AdminSendEmail;

class SendMailsController extends Controller
{
    public function __construct(){
        $this->middleware('auth:admin');
    }

    /**
     * Display the Mail Form.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminSendMailForm()
    {
        $menuCode = 'M15S1';
        return view('admin.mails.adminsendmailform',compact('menuCode'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendmail(SendMailRequest $request)
    {
        // dd($request);
        try{
            Mail::to($request->email)
            // ->cc($moreUsers)
            // ->bcc($evenMoreUsers)
            ->send(new AdminSendMail($request));
        }
        catch(Exception $e){
            $message = "信件寄出失敗";
            Session::put('error',$message);
        }

        $message = "信件已寄出給 $request->email";
        Session::put('success',$message);

        return redirect()->back();
    }
    /**
     * Notifications 測試
     */
    public function sendnote(SendNoteRequest $request)
    {
        // dd($request);
        try{
            /**
             * 發送 notification 到一個沒有存在 database 中, 特定的接收方時,
             * 可以使用 Notification facade 的 method 來指定 channel
             * 這邊的route('admin.mail')指的是通道
             */
            Notification::route('admin.mail', $request->email)
                        ->notify(new SendNoteNotification($request));
        }
        catch(Exception $e){
            $message = "信件寄出失敗";
            Session::put('error',$message);
        }
        $message = "通知已寄出給 $request->email";
        Session::put('success',$message);
        return redirect()->back();
    }
    /**
     * Queues 測試
     */
    public function sendqueues(SendQueuesRequest $request)
    {
        // dd($request);
        $mail['type'] = 'test'; //信件類別
        count(explode(',',$request->email)) > 1 ? $mail['to'] = explode(',',$request->email) : $mail['to'] = [explode(',',$request->email)];
        $mail['cc'] = [];
        $mail['bcc'] = [];
        $mail['subject'] = $request->subject;
        $mail['data'] = $request->content;
        try{
            /**
             * 不能直接將 Closure 的 $request 塞進去
             */
            // AdminSendEmail::dispatch($mail); //放入隊列
            AdminSendEmail::dispatchNow($mail); //馬上執行
        }
        catch(Exception $e){
            $message = "信件寄出失敗";
            Session::put('error',$message);
        }
        $message = "信件已寄出給 $request->email";
        Session::put('success',$message);
        return redirect()->back();
    }
}
