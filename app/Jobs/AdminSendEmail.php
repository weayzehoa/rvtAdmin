<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\AdminSendEmailForQueuing;
use App\Mail\refundMail;
use Mail;

use Aws\Ses\SesClient;
use Aws\Exception\AwsException;
use Aws\CloudWatchLogs\CloudWatchLogsClient;

use App\Models\CompanySetting as CompanySettingDB;
use App\Models\SystemSetting as SystemSettingDB;
use App\Models\MailLog as MailLogDB;

class AdminSendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $details;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->company = CompanySettingDB::first();
        $this->data = $this->details['data'];

        // $email = new AdminSendEmailForQueuing($this->details);
        // $Mail = Mail::to($this->details['to']);
        // $this->details['cc'] ? $Mail = $Mail->cc($this->details['cc']) : '';
        // $this->details['bcc'] ? $Mail = $Mail->bcc($this->details['bcc']) : '';
        // $Mail = $Mail->send($email);

        /*
         * 信件傳送後似乎無法找到 AWS 回傳的真正 message-id 只能找到傳出去的 message-id
         * 故改直接使用 AWS 的 SDK 方式去傳送信件. (目前暫時直接使用AWS SDK傳送)
        */

        // //env檔案中 TEST_MAIL_ACCOUNT = null 就會關閉此判斷, 若 TEST_MAIL_ACCOUNT 有信箱則會將信件寄到此信箱
        // env('TEST_MAIL_ACCOUNT') != null ? $this->details['to'] = [env('TEST_MAIL_ACCOUNT')] : '';

        env('APP_ENV') != 'production' ? $this->details['to'] = [env('TEST_MAIL_ACCOUNT')] : '';

        isset($this->details['to']) ? '' : $this->details['to'] = [];
        isset($this->details['cc']) ? '' : $this->details['cc'] = [];
        isset($this->details['bcc']) ? '' : $this->details['bcc'] = [];

        //空資料直接返回不執行
        if(count($this->details['to']) <=0 || empty($this->details['subject']) || empty($this->details['data']) || empty($this->details['type'])){
            return false;
        }

        //判斷信件種類
        if($this->details['type']=='refund'){
            $this->body = $this->refund();
        }elseif($this->details['type']=='asiamilesCertificate'){
            $this->body = $this->asiamilesCertificate();
        }elseif($this->details['type']=='orderShipToAirPortNotice'){
            $this->body = $this->orderShipToAirPortNotice();
        }elseif($this->details['type']=='orderShipOutNotice'){
            $this->body = $this->orderShipOutNotice();
        }elseif($this->details['type']=='alipayNotice'){
            $this->body = $this->alipayNotice();
            if(empty($this->body)){
                return false;
            }
        }elseif($this->details['type']=='test'){
            $this->body = $this->details['data'];
        }else{
            return false;
        }

        //寄出信件
        $messageId = null;
        $setting = SystemSettingDB::first()->email_supplier;
        if(strtolower($setting) == 'aws'){
            $messageId = $this->sendToSES();
        }else{
            return false;
        }

        //紀錄
        !empty($messageId) ? $status = '傳送成功' : $status = '傳送失敗';
        $mailLog = MailLogDB::create([
            'message_id' => $messageId,
            'email_supplier' => $setting,
            'type' => $this->details['type'],
            'to' => join(';',$this->details['to']),
            'subject' => $this->details['subject'],
            'status' => $status,
            'admin_id' => $this->details['admin_id'],
        ]);

        if(!empty($messageId)){
            return true;
        }else{
            return false;
        }
    }

    public function sendToSES(){
        $SesClient = new SesClient([
            'region' => env('AWS_SES_DEFAULT_REGION'),
            'version' => '2010-12-01',
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);
        try {
            $result = $SesClient->sendEmail([
                'Destination' => [
                    "ToAddresses" => $this->details['to'],
                    'BccAddresses' => $this->details['bcc'],
                    'CcAddresses' => $this->details['cc'],
                ],
                'ReplyToAddresses' => [env('MAIL_FROM_ADDRESS')],
                'Source' => env('APP_NAME').'<'.env('MAIL_FROM_ADDRESS').'>',
                'Message' => [
                    'Body' => [
                        'Html' => [
                            'Charset' => 'UTF-8',
                            'Data' => $this->body,
                        ],
                    ],
                    'Subject' => [
                        'Charset' => 'UTF-8',
                        'Data' => $this->details['subject'],
                    ],
                ],
            ]);
            return $result['MessageId'];
        } catch (AwsException $e) {
            // return $e->getMessage() .", ". $e->getAwsErrorMessage();
            return false;
        }
    }

    public function asiamilesCertificate()
    {
        return $this->mailHeader().$this->orderShipOut().$this->orderInfo().$this->asiamilesCertificateInfo().$this->mailFooter();
    }

    public function asiamilesCertificateInfo()
    {
        return '<br />
                    <table align="center" style="width:820px; border:1px #000000 solid;">
                    <tr style="background-color:#DDDDDD;"  ><td align="center" style="border:1px #000000 solid;">Asiamiles 訂購注意事項</td></tr>
                    <tr><td>感謝您使用 Asiamiles 里數訂購商品，提供給您商品購買憑証如下：<a target="_blank" href="https://icarry.me/asiamiles-print.php?o='.$this->data->am_md5.'">https://icarry.me/asiamiles-print.php?o='.$this->data->am_md5.'</a></td></tr>
                    <tr><td></td></tr>
                    <tr><td>若有任何問題，請不吝聯絡我們，您可以於本信件最下方找到聯絡方式，感謝您的訂購。</td></tr>
                </table>';
    }

    public function orderShipToAirPortNotice()
    {
        return $this->mailHeader().$this->orderShipToAirPort().$this->orderInfo().$this->mailFooter();
    }

    public function orderShipToAirPort()
    {
        $this->data->receiver_time = substr($this->data->receiver_key_time,0,10);
        $this->data->receiver_time = str_replace("-","/",$this->data->receiver_time);

        if($this->data->receiver_address=="桃園機場/第一航廈出境大廳門口"){
            $this->data->receiver_address = "第一航廈-台灣宅配通櫃檯：位於 1 樓出境大廳（近 12 號報到櫃檯）";
        }else if($this->data->receiver_address=="桃園機場/第二航廈出境大廳門口"){
            $this->data->receiver_address = "第二航廈-台灣宅配通櫃檯：位於 3 樓出境大廳（近 19 號報到櫃檯）";
        }else if($this->data->receiver_address=="松山機場/第一航廈台灣宅配通（E門旁）"){
            $this->data->receiver_address = "第一航廈-台灣宅配通櫃檯：位於 1 樓入境大廳內";
        }else if($this->data->receiver_address=="花蓮航空站/挪亞方舟旅遊"){
            $this->data->receiver_address = "諾亞方舟旅遊位於 1 樓國際線入境大廳出口處";
        }

        return '<tr><td>您的訂購的【訂單編號/'.$this->data->order_number.'】已經出貨，可在【'.$this->data->receiver_time.'】於【'.$this->data->receiver_address.'】,</td></tr>
                    <tr><td>您可以透過<a href="https://icarry.me/about-QA.php">常見問答</a>Q21查看詳細位置。</td></tr>
                    <tr><td></br></td></tr>
                    <tr><td>提貨地點:'.$this->data->receiver_address.'</td></tr>
                    <tr><td>提貨時間:'.$this->data->receiver_time.'</td></tr>
                    <tr><td>商品取貨號:'.$this->data->shipping_number.'</td></tr>
                    <tr><td>取件人:'.$this->data->receiver_name.'</td></tr>
                </table>
                <br />';
    }

    public function orderShipOutNotice()
    {
        return $this->mailHeader().$this->orderShipOut().$this->orderInfo().$this->mailFooter();
    }

    public function orderShipOut()
    {
        return '    <tr><td>您的訂單【'.$this->data->order_number.'】已經出貨，您可以透過<a href="https://icarry.me/">iCarry官方網站</a>「會員中心」-「歷史訂單」中作查詢物流狀態。</td></tr>
                    <tr><td>提醒您！</td></tr>
                    <tr><td>本通知函為已出貨之通知，並不代表訂單已配達或完成。</td></tr>
                </table>
                <br />';
    }
    public function orderInfo()
    {
        return '<table align="center" style="width:820px; border:1px #000000 solid;">
                    <tr style="background-color:#DDDDDD;"  ><td align="center" style="border:1px #000000 solid;">訂單資訊</td></tr>
                    <tr><td>訂單編號：【'.$this->data->order_number.'】</td></tr>
                    <tr><td>訂購日期：【'.$this->data->created_at.'】</td></tr>
                    <tr><td>付款日期：【'.$this->data->pay_time.'】</td></tr>
                    <tr><td>訂單明細：請於<a href="'.$this->company->url.'">iCarry官方網站</a>「會員中心」-「歷史訂單」中作查詢。</td></tr>
                </table>';
    }

    public function refund(){
        return $this->mailHeader().'<tr><td>您的訂單【'.$this->data->order_number.'】已為您申請退款 '.number_format($this->data->refund).' 元。</td></tr>
                </table>
                <br />
                <table align="center" style="width:820px; border:1px #000000 solid;">
                    <tr style="background-color:#DDDDDD;"  ><td align="center" style="border:1px #000000 solid;">訂單資訊</td></tr>
                    <tr><td>訂單編號：【'.$this->data->order_number.'】</td></tr>
                    <tr><td>訂購日期：【'.$this->data->created_at.'】</td></tr>
                    <tr><td>付款日期：【'.$this->data->pay_time.'】</td></tr>
                    <tr><td>退款日期：【'.date('Y-m-d H:i:s').'】</td></tr>
                    <tr><td>訂單明細：請於<a href="'.$this->company->url.'">iCarry官方網站</a>「會員中心」-「歷史訂單」中作查詢。</td></tr>
                </table>'.$this->mailFooter();
    }

    public function mailHeader()
    {
        return '
        <html>
            <body style="font-family: Microsoft JhengHei;">
                <table align="center" style="width:820px; border:1px #000000 solid;">
                    <tr style="background-color:#DDDDDD;"  ><td align="center" style="border:1px #000000 solid;">訂單通知</td></tr>
                    <tr><td>親愛的 顧客 您好：</td></tr>
                    <tr><td></br></td></tr>';
    }

    public function mailFooter()
    {
        return '<br />
                <table align="center" style="width:820px; border:1px #000000 solid;background-color:#DDDDDD;">
                    <tr><td>※ 此信件為系統發出信件，請勿直接回覆。若您有訂單方面問題請洽詢線上客服，</td></tr>
                    <tr><td>或撥打'.$this->company->service_tel.'，將會有專人為您服務。</td></tr>
                    <tr><td></br></td></tr>
                    <tr><td>iCarry官方網站：'.$this->company->url.'</td></tr>
                    <tr><td>公司名稱：'.$this->company->name.'</td></tr>
                    <tr><td>客服電話：'.$this->company->service_tel.'</td></tr>
                </table>
                <div align="center">Copyright © '.date('Y').' icarry.me '.$this->company->name.'｜'.$this->company->address.'</div>
                <br />
                <div align="center"><img src="https://api.icarry.me/image/logo_test.png" style="width:200px;"></div>
            </body>
        </html>';
    }

    public function alipayNotice()
    {
        $html = null;
        $order = $this->details['data'];
        $orderItems = OrderItemDB::where('order_id',$order->id)->get();
        if(!empty($orderItems)){
            $html='您好，感谢您在iCarry订购商品，<br />以下是您的订单数据，订单编号为'.$order->order_number.'<br /><br /><table style="width:100%;max-width:800px" border="1"><tr><th><strong>购买品项</strong></th><th><strong>新台币</strong></th><th><strong>购买数量</strong></th></tr>';
            foreach($orderItems as $item){
                $html.='<tr><th>'.$item->name.'</th><th>'.number_format($item->price).'</th><th>'.$item->quantity.'</th></tr>';
            }
            if ($order->shipping_fee > 0) {
                $html.='<tr><th>运费</th><th>100</th><th>－</th></tr>';
            } else {
                $html.='<tr><th>运费</th><th>0</th><th>符合免运费条件</th></tr>';
            }
            if ($order->shipping_method == 1) {
                $html.='</table><br /><br />您的提货方式为「机场提货」，离境班机号码「'.$order->receiver_keyword.'」，提货时间「'.substr($order->receiver_key_time, 0, -3).'」，提货地点「'.$order->receiver_address.'」。<br /><br />';
            } elseif ($order->shipping_method == 2) {
                $html.='</table><br /><br />您的提货方式为「旅店提货」，旅店名称「'.$order->receiver_keyword.'」，提货日期「'.substr($order->receiver_key_time, 0, 10).'」，旅店地点「'.$order->receiver_address.'」。<br /><br />';
            } elseif ($order->shipping_method == 4) {
                $html.='</table><br /><br />您的提货方式为「指定地址宅配」，地址「'.$order->receiver_address.'」。<br /><br />';
            } elseif ($order->shipping_method == 5) {
                $html.='</table><br /><br />您的提货方式为「旅店提货」，旅店名称「'.$order->receiver_keyword.'」，提货日期「'.substr($order->receiver_key_time, 0, 10).'」，旅店地点「'.$order->receiver_address.'」。<br /><br />';
            }
            $html.='提货人为「'.$order->receiver_name.'」，电话「'.$order->receiver_tel.'」，EMail「'.$order->receiver_email.'」。';
            $html.='<br /><br />再次感谢您使用「iCarry我来寄」的服务，祝您旅途愉快，如您有任何问题，欢迎随时联系iCarry。<br /><br />';
            $html.='联络邮件 <a href="mailto:icarry@icarry.me">icarry@icarry.me</a> 或电话 <a href="tel:+886-906-486688">+886-906-486688</a><br />服务时间 : 周一 ~ 周五 AM 9:00 ~ PM 6:00';
        }
        return $html;
    }
}
