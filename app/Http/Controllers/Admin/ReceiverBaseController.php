<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryReceiverBaseSetting as ReceiverBaseSettingDB;
use App\Models\iCarryReceiverBaseSet as ReceiverBaseSetDB;
use App\Models\iCarryCategory as CategoryDB;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryProduct as ProductDB;
use Carbon\Carbon;
use Auth;
use DB;

class ReceiverBaseController extends Controller
{
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
        $menuCode = 'M4S5';
        $compact = [];
        $nowMonth = date('m');
        $nowYear = date('Y');
        //將進來的資料作參數轉換
        foreach (request()->all() as $key => $value) {
            $$key = $value;
        }
        $thisMonthfirstDayWeek = date('w',strtotime($nowYear.'-'.$nowMonth.'-01')); //本月第一天是星期幾
        $firstSunday = Carbon::create($nowYear, $nowMonth, 1, 0)->subDays($thisMonthfirstDayWeek); //往回推到星期天
        $firstDay = substr($firstSunday,0,10); //往回推後的日期
        $lastDay = substr($firstSunday->addDays(34),0,10); //五周最後一天
        if(substr($lastDay,5,2) == $nowMonth){ //最後一天若還是在目前月份
            $lastDay = substr($firstSunday->addDays(7),0,10); //再加一周
        }
        //找出資料
        $receiverBases = ReceiverBaseSettingDB::whereBetween('select_date', array($firstDay, $lastDay))->orderBy('select_date','asc')->orderBy('type','asc')->get();
        //資料依照select_date日期群組(因為有每天有四筆)後，拆分5周
        $receiverBases = $receiverBases->groupBy('select_date')->chunk(7)->all();

        //關閉 datetimepicker 的日期 從2021開始
        $disablePickupDates = '';
        $dates = ReceiverBaseSettingDB::where('select_date', '>=', '2020-01-01')->orderBy('select_date','asc')->get()->groupBy('select_date');
        foreach ($dates as $date => $tmps) {
            foreach ($tmps as $tmp) {
                if($tmp->type =='pickup' && $tmp->is_ok == 0){
                    $disablePickupDates .= "'".$date."',";
                    break;
                }
            }
        }
        $disablePickupDates = rtrim($disablePickupDates,',');

        return view('admin.settings.receiverbase',compact('menuCode','receiverBases','nowYear','nowMonth','disablePickupDates'));
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
        foreach($request->is_ok as $k => $v){
            $data[$k]['is_ok'] = $v;
        }
        foreach($request->memo as $k => $v){
            $data[$k]['memo'] = $v;
        }
        $types = ['call','logistics','out','pickup'];
        if(is_array($request->date) && count($request->is_ok) == 4 && count($request->memo) == 4){
            $receiverBases = ReceiverBaseSettingDB::whereIn('select_date',$request->date)->get();
            foreach($receiverBases as $receiverBase){
                for($i=0;$i<count($types);$i++){
                    //回寫舊資料庫
                    $receiverBaseSet = ReceiverBaseSetDB::where('select_time',$receiverBase->select_date)->first();
                    if($receiverBase->type == $types[$i]){
                        if($types[$i] == 'call'){
                            $new['is_call'] = $data[$types[$i]]['is_ok'];
                            $new['call_memo'] = $data[$types[$i]]['memo'];
                        }
                        if($types[$i] == 'logistics'){
                            $new['is_logistics'] = $data[$types[$i]]['is_ok'];
                            $new['logistics_memo'] = $data[$types[$i]]['memo'];
                        }
                        if($types[$i] == 'out'){
                            $new['is_out'] = $data[$types[$i]]['is_ok'];
                            $new['out_memo'] = $data[$types[$i]]['memo'];
                        }
                        if($types[$i] == 'pickup'){
                            $new['is_extract'] = $data[$types[$i]]['is_ok'];
                            $new['extract_memo'] = $data[$types[$i]]['memo'];
                        }
                        if(!empty($receiverBaseSet)){
                            $new['update_admin_id'] = auth('admin')->user()->id;
                            $receiverBaseSet->update($new); //回寫舊資料庫
                        }else{
                            $new['select_time'] = $receiverBase->select_date;
                            $new['create_time'] = date('Y-m-d H:i:s');
                            $new['admin_id'] = auth('admin')->user()->id;
                            ReceiverBaseSetDB::create($new);
                        }
                        $receiverBase->update([
                            'is_ok' => $data[$types[$i]]['is_ok'],
                            'memo' => $data[$types[$i]]['memo'],
                            'admin_id' => Auth::user()->id,
                        ]);

                    }
                }
            }
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
        //
    }
    public function search(Request $request)
    {
        $searchType = $request->search_type;
        $orderDate = $request->order_date;
        $pickupDate = $request->pickup_date;
        $keyword = $request->keyword;
        $shippingMethod = $request->shipping_method;

        if($searchType == 'stockDays'){
            $stockDays = $this->stockDays($orderDate,$pickupDate);
        }elseif($searchType == 'productAvailable'){
            $stockDays = $this->productAvailable($orderDate,$pickupDate);
        }else{
            return null;
        }

        if($shippingMethod==1){//機場
            $stock = 'airplane_days';
        }else if($shippingMethod==2){//旅店
            $stock = 'hotel_days';
        }

        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $categoryTable = env('DB_ICARRY').'.'.(new CategoryDB)->getTable();

        $products = ProductDB::join($vendorTable,$vendorTable.'.id',$productTable.'.vendor_id')
                    ->join($categoryTable,$categoryTable.'.id',$productTable.'.category_id')
                    ->where([
                        [$productTable.'.shipping_methods','like',"%$shippingMethod%"],
                        [$productTable.'.status',1],
                        [$productTable.'.'.$stock,'<=',$stockDays],
                        [$vendorTable.'.is_on',1],
                    ]);

        if($keyword){
            $products = $products->where(function ($query) use ($keyword,$productTable,$vendorTable) {
                $query->where($productTable.'.name', 'like', "%$keyword%")
                ->orwhere($vendorTable.'.name','like',"%$keyword%");
            });
        }

        $products = $products->select([
                    $productTable.'.id',
                    DB::raw("(CASE WHEN $productTable.category_id = $categoryTable.id THEN $categoryTable.name END) as category"),
                    $productTable.'.name',
                    $productTable.'.'.$stock.' as days',
                ])->orderBy($productTable.'.'.$stock,'desc')->get();

        return response()->json($products);
    }

    public function stockDays($orderDate,$pickupDate)
    {
        $startDate = date('Y-m-d',strtotime($orderDate));
        $endDate = date('Y-m-d',strtotime($pickupDate));
        $startHour = intval(date("G",strtotime($orderDate)));//取得開始的小時 因為快閃12點前後分水嶺
        $daysBetweenPickupDateToStartDate = ((strtotime($endDate)-strtotime($startDate)) / 86400);

        $shippingMethod = 1;
        if($shippingMethod==1){//機場
            $stock = 'airport_days';
        }else if($shippingMethod==2){//旅店
            $stock = 'hotel_days';
        }
        $stockDays = 0;

        if($daysBetweenPickupDateToStartDate>0){ //避免輸入的日期相反

            //找出下單日到提貨日的設定資料
            $tmps = ReceiverBaseSettingDB::whereBetween('select_date',[$startDate,$endDate])->orderBy('select_date','asc')->get();
            $tmps = $tmps->groupBy('select_date');

            foreach($tmps as $d => $tmp){
                $everyDays[] = $d;
                foreach($tmp as $t){
                    $dates[$d][$t->type] = $t->is_ok;
                }
            }

            $tomorrow = date("Y-m-d", strtotime($startDate) + 1 * 86400);
            $theDayAfterTomorrow = date("Y-m-d", strtotime($startDate) + 2 * 86400);
            $twoDaysAfterTomorrow = date("Y-m-d", strtotime($startDate) + 3 * 86400);
            $max_n=$daysBetweenPickupDateToStartDate;

            for($i=1;$i<=1;$i++){ //用來給裡面的檢驗跳出用實際上只會跑一次
                if($daysBetweenPickupDateToStartDate >= 4){ //備貨日4天以上
                    //這迴圈必須符合有叫貨=>物流*($max_n-2)=>出貨日(檢驗)=>提貨
                    for($n=$max_n;$n>3;$n--){
                        $atLeastDays=0;
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['call']==1){
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        $needLogisticsDay=$n-3;
                        $checkLogisticsDay=0;
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['logistics']==1){
                                    $checkLogisticsDay+=1;
                                    $atLeastDays=$k;
                                    if($needLogisticsDay==$checkLogisticsDay){
                                        break;
                                    }
                                }
                            }
                        }
                        $checkOutDay = 0;
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['out']==1){
                                    $checkOutDay = 1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        if($checkOutDay == 0){ //檢查是否有out, 沒有則返回前一個迴圈
                            $n = $n-1;
                            continue;
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['pickup']==1){
                                    $atLeastDays=$k;
                                    $stockDays=$n;
                                    break 3;
                                }
                            }
                        }
                    }
                    //這迴圈須符合有叫貨=>出貨=>提貨
                    for($n=$max_n;$n>3;$n--){
                        $ifStockNeed3Days=0;
                        $atLeastDays=0;
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['call']==1){
                                    $ifStockNeed3Days+=1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['out']==1){
                                    $ifStockNeed3Days+=1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['pickup']==1){
                                    $ifStockNeed3Days+=1;
                                    $atLeastDays=$k;
                                    if($ifStockNeed3Days==3){
                                        $stockDays=3;
                                        break 3;
                                    }
                                }
                            }
                        }
                    }
                    //這迴圈檢查備貨2日的須符合有出貨=>提貨
                    for($n=$max_n;$n>3;$n--){
                        $ifStockNeed2Days=0;
                        $atLeastDays=0;
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['out']==1){
                                    $ifStockNeed2Days+=1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['pickup']==1){
                                    $ifStockNeed2Days+=1;
                                    $atLeastDays=$k;
                                    if($ifStockNeed2Days==2){
                                        $stockDays=2;
                                        $daysBetweenPickupDateToStartDate=3;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                    //這迴圈檢查備貨1日與0日的須符合有出貨=>提貨且檢查下單日的時間是否小於11點
                    for($n=$max_n;$n>3;$n--){
                        $ifStockNeed1Day=0;
                        $atLeastDays=-1;
                        if($startHour<11){
                            $atLeastDays=0;
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['out']==1){
                                    $ifStockNeed1Day+=1;
                                    $atLeastDays=$k;
                                    break;
                                }
                            }
                        }
                        foreach($everyDays as $k=>$d){
                            if($k>$atLeastDays){
                                if($dates[$d]['pickup']==1){
                                    $ifStockNeed1Day+=1;
                                    $atLeastDays=$k;
                                    if($ifStockNeed1Day==1){
                                        $stockDays=1;
                                        $daysBetweenPickupDateToStartDate=3;
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }
                if($stockDays == 0 || $daysBetweenPickupDateToStartDate==3){//備貨日為3天
                    if(isset($dates[$tomorrow]['call'])==1 && isset($dates[$theDayAfterTomorrow]['out'])==1 && isset($dates[$twoDaysAfterTomorrow]['pickup'])==1){
                        $stockDays=3;
                        break;
                    }elseif($stockDays==2){
                        break;
                    }elseif(isset($dates[$tomorrow]['out'])==1 && isset($dates[$twoDaysAfterTomorrow]['pickup'])==1){
                        $stockDays=2;
                        break;
                    }elseif(isset($dates[$theDayAfterTomorrow]['out'])==1 && isset($dates[$twoDaysAfterTomorrow]['pickup'])==1){
                        $stockDays=2;
                        break;
                    }elseif($startHour<11){
                        if(isset($dates[$tomorrow]['out'])==1 && isset($dates[$theDayAfterTomorrow]['pickup'])==1){
                            $stockDays=1;
                        }elseif(isset($dates[$twoDaysAfterTomorrow]['out'])==1 && isset($dates[$twoDaysAfterTomorrow]['pickup'])==1){
                            $stockDays=1;
                        }
                        $daysBetweenPickupDateToStartDate-=1;
                    }else{
                        $daysBetweenPickupDateToStartDate-=1;
                    }
                }
                if($stockDays == 0 || $daysBetweenPickupDateToStartDate==2){//備貨日為2天
                    if(isset($dates[$tomorrow]['out'])==1 && isset($dates[$theDayAfterTomorrow]['pickup'])==1){
                        $stockDays=2;
                        break;
                    }elseif($startHour<11 && isset($dates[$tomorrow]['out'])==1 && isset($dates[$theDayAfterTomorrow]['pickup'])==1){//符合第二天當天出貨
                        $stockDays=1;
                        break;
                    }else{
                        $daysBetweenPickupDateToStartDate-=1;
                    }
                }
                if($stockDays == 0 || $daysBetweenPickupDateToStartDate==1){//備貨日為1天
                    if($startHour<11 && isset($dates[$startDate]['out'])==1 && isset($dates[$tomorrow]['pickup'])==1){//符合當天出貨
                        $stockDays=1;
                        break;
                    }else{
                        $stockDays=0;
                        break;
                    }
                }
            }
        }
        return $stockDays;
    }
}
