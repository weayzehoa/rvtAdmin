<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order as OrderDB;
use App\Models\OrderItem as OrderItemDB;
use App\Models\OrderLog as OrderLogDB;
use App\Models\User as UserDB;
use App\Models\Vendor as VendorDB;
use App\Models\ShippingMethod as ShippingMethodDB;
use App\Models\ShippingVendor as ShippingVendorDB;
use App\Models\PayMethod as PayMethodDB;
use App\Models\ShopcomOrderDB as ShopcomOrderDB;
use App\Models\TradevanOrder as TradevanOrderDB;
use App\Models\OrderAsiamiles as OrderAsiamilesDB;
use App\Models\OrderShipping as OrderShippingDB;
use App\Models\OrderVendorShipping as OrderVendorShippingDB;
use App\Models\UserPoint as UserPointDB;
use App\Models\SystemSetting as SystemSettingDB;
use App\Models\Country as CountryDB;
use App\Models\ShippingFee as ShippingFeeDB;
use App\Models\CompanySetting as CompanySettingDB;
use DB;
use Auth;
use Carbon\Carbon;
use App\Jobs\AdminSendSMS;
use App\Jobs\AdminSendEmail;
use App\Jobs\AdminInvoiceJob;
use App\Exports\InvoicesExport;
use App\Imports\InvoicesImport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Curl;
use Mail;
use App\Mail\refundMail as refundMail;
use Session;

class InvoicesController extends Controller
{
    /**
     * Create a new controller instance.
     * 進到這個控制器需要透過middleware檢驗是否為後台的使用者
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menuCode = 'M6S3';
        $appends = [];
        $compact = [];
        $totalInvoices = 0;

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        // //找出訂單資料
        $orders = OrderDB::with('user', 'shippingMethod', 'items', 'shipto', 'shipfrom');

        //查詢參數
        isset($is_invoice) && $is_invoice != '' ? $orders = $orders->where('is_invoice', $is_invoice) : '';
        isset($is_invoice_no) && $is_invoice_no ? $orders = $orders->where('is_invoice_no', 'like', "%$is_invoice_no%") : '';
        isset($status) && ($status || $status == 0) ? count(explode(',', $status)) > 1 ? $orders = $orders->whereIn('status', explode(',', $status)) : $orders = $orders->where('status', explode(',', $status)) : '';
        isset($order_number) && $order_number ? $orders = $orders->where('order_number', 'like', "%$order_number%") : '';
        isset($invoice_time) && $invoice_time ? $orders = $orders->where('invoice_time', '>=', $invoice_time) : '';
        isset($invoice_time_end) && $invoice_time_end ? $orders = $orders->where('invoice_time', '<=', $invoice_time_end) : '';
        isset($created_at) && $created_at ? $orders = $orders->where('created_at', '>=', $created_at) : '';
        isset($created_at_end) && $created_at_end ? $orders = $orders->where('created_at', '<=', $created_at_end) : '';
        isset($pay_time) && $pay_time ? $orders = $orders->where('pay_time', '>=', $pay_time) : '';
        isset($pay_time_end) && $pay_time_end ? $orders = $orders->where('pay_time', '<=', $pay_time_end) : '';
        isset($shipping_method) && $shipping_method ? $orders = $orders->whereIn('shipping_method', explode(',', $shipping_method)) : '';
        isset($shipping_time) && $shipping_time ? $orders = $orders->where('shipping_time', '>=', $shipping_time) : '';
        isset($shipping_time_end) && $shipping_time_end ? $orders = $orders->where('shipping_time', '<=', $shipping_time_end) : '';
        isset($user_id) && $user_id ? $orders = $orders->where('user_id','like', $user_id) : '';
        isset($buyer_name) && $buyer_name ? $orders = $orders->where('buyer_name','like', $buyer_name) : '';
        isset($pay_method) && $pay_method !='' ? $orders = $orders->whereIn('pay_method', explode(',', $pay_method)) : '';
        isset($parcel_tax) ? strtoupper($parcel_tax) == 'X' ? $orders = $orders->where('parcel_tax','<=', 0) : $orders = $orders->where('parcel_tax','>=', 1) : '';
        isset($shipping_fee) ? strtoupper($shipping_fee) == 'X' ? $orders = $orders->where('shipping_fee','<=', 0) : $orders = $orders->where('shipping_fee','>=', 1) : '';
        isset($spend_point) ? strtoupper($spend_point) == 'X' ? $orders = $orders->where('spend_point','<=', 0) : $orders = $orders->where('spend_point','>=', 1) : '';
        isset($is_discount) ? strtoupper($is_discount) == 'X' ? $orders = $orders->where('discount','=', 0) : $orders = $orders->where('discount','!=', 0) : '';
        isset($invoice_type) && $invoice_type ? $orders = $orders->where('invoice_type', $invoice_type) : '';
        isset($invoice_number) ? strtoupper($invoice_number) == 'X' ? $orders = $orders->where('invoice_number','=','') : $orders = $orders->where('invoice_number','!=','') : '';
        isset($invoice_address) ? strtoupper($invoice_address) == 'X' ? $orders = $orders->where('invoice_address','=','') : $orders = $orders->where('invoice_address','!=','') : '';

        if (isset($keyword) && $keyword) {
            $userIds = UserDB:: where('id', $keyword)
                ->orwhere('name', $keyword)
                ->orwhere('mobile', 'like', "%$keyword%")->select('id')->distinct()->get();
            $userIds == '' || $userIds == null ? $userIds = [] : '';
            $orders = $orders->where(function ($query) use ($keyword, $userIds) {
                $query->where('orders.order_number', 'like', "%$keyword%")
                ->orWhere('receiver_name', $keyword)
                ->orWhere('receiver_phone_number', 'like', "%$keyword%")
                ->orWhere('receiver_tel', 'like', "%$keyword%")
                ->orWhereIn('user_id', $userIds);
            });
        }

        //在分頁之前計算數量
        $totalOrders = $orders->withTrashed()->count();

        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }

        //找出最終資料
        $orders = $orders->orderBy('created_at', 'desc')->withTrashed()->paginate($list);

        //其他資料
        $today = date('Y-m-d');
        $start = Carbon::now()->subDays('10');
        $end = new Carbon('last day of this month');
        $bookingDates = OrderDB::where([['book_shipping_date','>=',$start],['book_shipping_date','>=',$end]])->whereNotNull('book_shipping_date')->selectRaw('DISTINCT DATE_FORMAT(book_shipping_date,"%Y-%m-%d") as book_shipping_date')->orderBy('book_shipping_date', 'desc')->withTrashed()->get();
        $keyDates = OrderDB::where([['receiver_key_time','>=',$start],['receiver_key_time','>=',$end]])->whereNotNull('receiver_key_time')->selectRaw('DISTINCT DATE_FORMAT(receiver_key_time,"%Y-%m-%d") as receiver_key_time')->orderBy('receiver_key_time', 'desc')->withTrashed()->get();
        $shippingVendors = ShippingVendorDB::orderBy('sort', 'asc')->get();
        $payMethods = PayMethodDB::orderBy('id', 'asc')->get();

        //計算及資料變更
        foreach ($orders as $order) {
            if($order->shipping_memo){
                if( json_decode( $order->shipping_memo , true ) ){
                    $shippingMemo = collect(json_decode($order->shipping_memo));
                    foreach($shippingMemo as $sm){
                        $order->shipping_memo_vendor = $sm->express_way;
                    }
                }else{
                    $order->shipping_memo_vendor = $order->shipping_memo;
                }
            }
            $totalQty = 0;
            $totalPrice = 0;
            $totalWeight = 0;
            foreach ($order->items as $item) {
                $totalQty = $totalQty + $item->quantity;
                $totalPrice = $totalPrice + $item->price * $item->quantity;
                $totalWeight = $totalWeight + $item->gross_weight * $item->quantity;
            }
            $order->totalQty = $totalQty;
            $order->totalPrice = $totalPrice;
            $order->totalWeight = $totalWeight;
            //金流支付 (付款金額 = 商品費+跨境稅+運費-使用購物金-折扣)
            $order->total_pay = $order->amount + $order->shipping_fee + $order->parcel_tax - $order->spend_point - $order->discount;
        }

        $compact = array_merge($compact, ['menuCode','orders','appends','bookingDates','keyDates','totalOrders','shippingVendors','payMethods']);
        return view('admin.orders.invoice_index', compact($compact));
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
        //
    }

    public function print($id)
    {
        $order = OrderDB::with('user','items','items.model.product')->withTrashed()->findOrFail($id);
        $order->from_address = $this->checkNation($order->invoice_address);
        $order->totalCount = 0;
        $order->totalPrice = 0;
        foreach($order->items as $item){
            $order->totalCount += $item->quantity;
            $order->totalPrice += $item->quantity * $item->price;
        }
        $order->spend_point > 0 ? $order->totalCount++ : '';
        $order->shipping_fee > 0 ? $order->totalCount++ : '';
        $order->parcel_tax > 0 ? $order->totalCount++ : '';
        $order->totalPrice = $order->totalPrice + $order->shipping_fee + $order->parcel_tax - $order->spend_point;

        return view('admin.orders.invoice_print',compact('order'));
    }

    function checkNation($address){
        $blocks=[
            "中國","中国","北京","天津","上海","重慶","河北","山西","蒙古","遼寧","吉林",
            "黑龍江","江蘇","浙江","安徽","福建","江西","山東","河南","湖北","湖南","廣東",
            "廣西","海南","四川","貴州","雲南","陝西","甘肅","青海","西藏","寧夏","新疆",
            "重庆","辽宁","黑龙江","江苏","山东","广东","广西","贵州","云南","陕西","甘肃",
            "宁夏","台灣","澳門","新加坡","馬來西亞","香港","台湾","澳门","马来西亚",
            "HONG KONG","MACAU","Singapore","MALAYSIA"
        ];
        foreach($blocks as $b){
            if(stristr($address,$b)){
                return $b;
                break;
            }
        }
        return "台灣";
    }

    public function modify(Request $request)
    {
        $type = ['modify','multi','create','cancel']; //允許的功能類別, 否則回傳false
        if(in_array($request->type,$type)){
            $ids = $request->id;
            $invoices = $request->is_invoice_no;
            if(count($ids) > 0){
                if ($request->type == 'create' || $request->type == 'cancel') {
                    $param['id'] = $ids; //order id, 可用陣列或單一
                    $param['type'] = $request->type; //類別:開立
                    $request->type == 'cancel' ? $param['reason'] = $request->reason : ''; //取消理由
                    $param['return'] = false; //true 返回訊息 false 不返回
                    // AdminInvoiceJob::dispatch($mail); //放入隊列
                    $result = AdminInvoiceJob::dispatchNow($param); //馬上執行
                    if ($param['return']) {
                        return $result;
                    }
                }elseif ($request->type == 'modify' || $request->type == 'multi') {
                    for ($i=0;$i<count($ids);$i++) {
                        $order = OrderDB::findOrFail($ids[$i]);
                        //新舊內容不同時才做
                        if ($order->is_invoice_no != $invoices[$i]) {
                            if ($invoices[$i]) {
                                //已作廢若有修改須將狀態改回已開立, 且註記曾經作廢過.
                                //未開發票則狀態改為已開立,已開發票則註記曾經作廢過.
                                if ($order->status >= 1 && $order->is_invoice == 2) {
                                    $order->is_invoice = 1;
                                    $order->is_invoice_cancel = 1;
                                } elseif ($order->status >= 1 && $order->is_invoice == 0) {
                                    $order->is_invoice = 1;
                                } elseif ($order->status >= 1 && $order->is_invoice == 1) {
                                    $order->is_invoice_cancel = 1;
                                }
                                $order->invoice_memo = "變更發票資料 (舊:$order->is_invoice_no)";
                            } else { //進來的資料不存在,表示該發票被作廢尚未填入新的發票資料, 需註記曾經作廢過, 狀態改為未開立.
                                if ($order->status >= 1 && $order->is_invoice == 2) {
                                    $order->is_invoice = 0;
                                    $order->is_invoice_cancel = 1;
                                } elseif ($order->status >= 1 && $order->is_invoice == 1) {
                                    $order->is_invoice = 0;
                                    $order->is_invoice_cancel = 1;
                                }
                                $order->invoice_memo = "清空發票資料 (舊:$order->is_invoice_no)";
                            }
                            $order = $order->update([
                                'is_invoice' => $order->is_invoice,
                                'is_invoice_cancel' => $order->is_invoice_cancel,
                                'is_invoice_no' => $invoices[$i],
                                'invoice_memo' => $order->invoice_memo,
                            ]);
                        }
                    }
                }
                $orders = OrderDB::whereIn('id',$ids)->orderBy('created_at', 'desc')->withTrashed()->get();
                return response()->json($orders);
            }
        }
    }

    public function export(Request $request)
    {
        $param['id'] = $request->order_id;
        $exportFile = '發票資料匯出_'.date('YmdHis');
        return Excel::download(new InvoicesExport($param), $exportFile.'.xlsx');
    }

    public function import(Request $request)
    {
        if ($request->hasFile('filename')) {
            $file = $request->file('filename');
            $uploadedFileMimeType = $file->getMimeType();
            $mimes = array('application/excel','application/vnd.ms-excel','application/vnd.msexcel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/CDFV2','application/octet-stream');
            if(in_array($uploadedFileMimeType, $mimes)){
                $resulet = Excel::import(new InvoicesImport, $file);
                if($resulet){
                    $message = '匯入成功';
                    Session::put('success', $message);
                    return redirect()->back();
                }else{
                    $message = '匯入失敗';
                    Session::put('error', $message);
                    return redirect()->back();
                }
            } else{
                $message = '只接受 xls 或 xlsx 檔案格式';
                Session::put('error', $message);
                return redirect()->back();
            }
        }
        return redirect()->back();
    }
}
