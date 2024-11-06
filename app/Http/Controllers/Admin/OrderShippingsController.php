<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order as OrderDB;
use App\Models\Vendor as VendorDB;
use App\Models\ShippingVendor as ShippingVendorDB;
use App\Models\PayMethod as PayMethodDB;
use App\Models\OrderShipping as OrderShippingDB;

use Carbon\Carbon;

use App\Jobs\AdminOrderStatusJob;

use App\Exports\OrderShippingsExport;
use Maatwebsite\Excel\Facades\Excel;

use Session;

class OrderShippingsController extends Controller
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
        $menuCode = 'M6S4';
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
        $orders = OrderDB::with('user', 'shippingMethod', 'items', 'shipto', 'shipfrom','shippings','vendorShippings');

        //查詢參數
        isset($order_number) && $order_number ? $orders = $orders->where('order_number', 'like', "%$order_number%") : '';
        isset($book_shipping_date) && $book_shipping_date ? $orders = $orders->where('book_shipping_date', '>=', $book_shipping_date) : '';
        isset($book_shipping_date_end) && $book_shipping_date_end ? $orders = $orders->where('book_shipping_date', '<=', $book_shipping_date_end) : '';
        isset($status) && ($status || $status == 0) ? count(explode(',', $status)) > 1 ? $orders = $orders->whereIn('status', explode(',', $status)) : $orders = $orders->where('status', explode(',', $status)) : $orders = $orders->where('status','>=', 2);
        isset($shipping_method) && $shipping_method ? $orders = $orders->whereIn('shipping_method', explode(',', $shipping_method)) : '';
        isset($no_is_null) ? strtoupper($no_is_null) == 'X' ? $orders = $orders->where('shipping_memo','') : $orders = $orders->where('shipping_memo','!=', '') : '';
        isset($user_id) && $user_id ? $orders = $orders->where('user_id', $user_id) : '';
        isset($buyer_name) && $buyer_name ? $orders = $orders->where('buyer_name','like', "%$buyer_name%") : '';
        isset($receiver_name) && $receiver_name ? $orders = $orders->where('receiver_name','like', "%$receiver_name%") : '';
        isset($receiver_tel) && $receiver_tel ? $orders = $orders->where('receiver_tel','like', "%$receiver_tel%") : '';
        isset($receiver_address) && $receiver_address ? $orders = $orders->where('receiver_address','like', "%$receiver_address%") : '';
        isset($admin_memo) && $admin_memo ? $orders = $orders->where('admin_memo','like', "%$admin_memo%") : '';
        isset($shipping_number) && $shipping_number ? $orders = $orders->where('shipping_number','like', "%$shipping_number%") : '';
        isset($express_way) && $express_way ? $orders = $orders->where('shipping_memo','like',"%$express_way%") : '';

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

        $compact = array_merge($compact, ['menuCode','orders','appends','bookingDates','keyDates','totalOrders','shippingVendors','payMethods']);
        return view('admin.orders.shipping_index', compact($compact));
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
        $orderShipping = OrderShippingDB::findOrFail($id)->delete();
        return 'success';
    }

    public function modify(Request $request)
    {
        $ids = $request->id;
        $shippingId = $request->shipping_id;
        $expressWay = $request->express_way;
        $expressNo = $request->express_no;
        $adminMemo = $request->admin_memo;
        $status = $request->status;
        $oldStatus = [];
        $oldShipping = [];

        for($i=0;$i<count($ids);$i++){
            $oldShipping[$i] = OrderShippingDB::where('order_id',$ids[$i])->select(['id','express_way','express_no'])->get(); //更新前取得物流資料
            //物流單號有資料，更新或新增物流單資料
            if($expressNo[$i] || $expressWay[$i] == '廠商發貨'){
                $shippingId[$i] ? $orderShipping = OrderShippingDB::findOrFail($shippingId[$i])->update(['express_way' => $expressWay[$i], 'express_no' => $expressNo[$i]]) : $orderShipping = OrderShippingDB::create(['order_id' => $ids[$i],'express_way' => $expressWay[$i], 'express_no' => $expressNo[$i]]);
            }else{
                $shippingId[$i] ? $orderShipping = OrderShippingDB::findOrFail($shippingId[$i])->delete() : '';
            }
        }
        //找出相關訂單
        for($i=0;$i<count($ids);$i++){
            $order = OrderDB::findOrFail($ids[$i]);
            $oldStatus[$i] = $order->status; //找出舊的狀態

            //找出相關物流單
            $tmps = OrderShippingDB::where('order_id',$ids[$i])->get();
            $shippingNumber = '';
            $shippingMemo = '';
            if(count($tmps) > 0){
                $orderShippings = [];
                foreach($tmps as $tmp){
                    $orderShippings[] = [
                        'create_time' => date('Y-m-d H:i:s'),
                        'express_way' => $tmp->express_way,
                        'express_no' => $tmp->express_no
                    ];
                    $shippingNumber .= ','.$tmp->express_no;
                }
                $shippingNumber = ltrim($shippingNumber,','); //將物流單號填入
                $shippingMemo = json_encode($orderShippings,JSON_UNESCAPED_UNICODE); //回寫到shipping_memo欄位
            }
            //更新
            $order = $order->update([
                'status' => $status[$i],
                'admin_memo' => $adminMemo[$i],
                'shipping_number' => $shippingNumber,
                'shipping_memo' => $shippingMemo,
            ]);
        }

        //訂單狀態變更處理Job
        $param['ids'] = $ids; //order id, 使用陣列方式
        $param['oldStatus'] = $oldStatus; //舊狀態, 使用陣列方式
        $param['oldShippingStatus'] = $oldShipping; //舊的物流資料, 使用陣列方式
        $param['return'] = false; //true 返回訊息 false 不返回
        $result = AdminOrderStatusJob::dispatch($param); //放入隊列
        // $result = AdminOrderStatusJob::dispatchNow($param); //馬上執行
        if ($param['return']) {
            return $result;
        }

        return redirect()->back();
    }

    public function export(Request $request)
    {
        $request->expressWay ? $param['express_way'] = $request->expressWay : $param['express_way'] = '';
        $request->created_at ? $param['created_at'] = $request->created_at.' 00:00:00' : $param['created_at'] = '';
        $request->created_at_end ? $param['created_at_end'] = $request->created_at_end.' 23:59:59' : $param['created_at_end'] = '';
        $exportFile = '物流單查詢_'.date('YmdHis');
        return Excel::download(new OrderShippingsExport($param), $exportFile.'.xlsx');
    }
}
