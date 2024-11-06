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
use DB;
use Auth;
use Carbon\Carbon;
use App\Jobs\AdminSendSMS;
use App\Exports\OrderPickupExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Curl;

class UnpayOrdersController extends Controller
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
        // dd(request());
        //備註: 訂單的is_del是由使用者產生的，只有使用者可以刪除訂單就不顯示在前台，但後台要顯示出來。
        //後台管理者沒有刪除功能。
        $ctime = microtime(true); //紀錄開始時間
        $menuCode = 'M6S2';
        $where = [];
        $appends = [];
        $compact = [];
        $totalOrders = 0;

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        // //找出訂單資料
        $orders = OrderDB::with('user', 'shippingMethod', 'items');

        //查詢參數
        isset($status) && $status ? $orders = $orders->where('status', $status) : $orders = $orders->whereIn('status',[0,-1]);
        isset($order_number) && $order_number ? $orders = $orders->where('order_number', 'like', "%$order_number%") : '';
        isset($user_id) && $user_id ? $orders = $orders->where('user_id', $user_id) : '';
        isset($buyer_name) && $buyer_name ? $orders = $orders->where('buyer_name','like', "%$buyer_name%") : '';
        isset($buyer_phone) && $buyer_phone ? $orders = $orders->whereIn('user_id', UserDB::where('mobile','like',"%$buyer_phone%")->orWhere(DB::raw("CONCAT(nation,mobile)"),'like',"%$buyer_phone%")->select('id')->groupBy('id')->get()) : '';
        isset($created_at) && $created_at ? $orders = $orders->where('created_at', '>=', $created_at) : '';
        isset($created_at_end) && $created_at_end ? $orders = $orders->where('created_at', '<=', $created_at_end) : '';
        isset($vendor_name) && $vendor_name ? $orders = $orders->whereIn('id', OrderItemDB::where('vendor_name','like',"%$vendor_name%")->select('order_id')->groupBy('order_id')->get()) : '';
        isset($product_name) && $product_name ? $orders = $orders->whereIn('id', OrderItemDB::where('vendor_name','like',"%$product_name%")->select('order_id')->groupBy('order_id')->get()) : '';

        //在分頁之前計算數量
        $totalOrders = $orders->withTrashed()->count();

        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }

        //找出最終資料
        $orders = $orders->orderBy('created_at', 'desc')->withTrashed()->paginate($list);

        //計算及資料變更
        foreach ($orders as $order) {
            if ($order->origin_country == '日本') {
                if ($order->shipping_method == 4) {
                    $order->shipping_method = $order->shippingMethod->name.'到'.$order->ship_to;
                } else {
                    $order->shipping_method = $order->shippingMethod->name;
                }
            } else {
                if ($order->shipping_method == 4) {
                    $order->shipping_method = $order->shippingMethod->name.'到'.$order->ship_to;
                } elseif ($order->shipping_method == 5) {
                    $order->shipping_method = '寄送當地';
                } else {
                    $order->shipping_method = $order->shippingMethod->name;
                }
            }
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
            //金流支付
            $order->total_pay = $order->amount + $order->shipping_fee - $order->spend_point - $order->discount;
        }
        // dd($orders->toArray());
        // $ctime = microtime(true) - $ctime; //紀錄時間結束
        // dd($ctime);
        $compact = array_merge($compact, ['menuCode','orders','appends','totalOrders']);
        return view('admin.orders.unpay_index', compact($compact));
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

    public function modify(Request $request)
    {
        $columnName = $request->column_name;
        $ids = $request->id;
        if (count($ids) > 0) {
            $orders = OrderDB::whereIn('id',$ids);
            if ($columnName == 'cancel') {
                $request->column_data == null || $request->column_data == '' ? $request->column_data = '未輸入原因' : '';
                $orders->update(['status' => -1, 'admin_memo' => $request->column_data]);
            }else{
                $request->column_data == null || $request->column_data == '' ? $request->column_data = '清除註記' : '';
                $orders->update([$request->column_name => $request->column_data]);
            }
            $orders = $orders->get();
            foreach($orders as $order){
                $orderLog = OrderLogDB::create([
                    'order_id' => $order->id,
                    'column_name' => $request->column_name,
                    'log' => $request->column_data,
                    'admin_id' => Auth::user()->id,
                ]);
            }
            return response()->json($orders);
        }
    }
}
