<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\iCarryPriceChangeRecord as PriceChangeDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryPriceChangeRecord as PriceChangeRecordDB;
use DateTime;
use Session;

class PriceChangeController extends Controller
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
        $menuCode = 'M4S7';
        $appends = $compact = [];

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }

        $priceChanges = new PriceChangeDB;
        isset($product_id) ? $priceChanges = $priceChanges->where('product_id',$product_id) : '';
        isset($colF) ? $priceChanges = $priceChanges->where('colF','>=',$colF) : '';
        isset($colG) ? $priceChanges = $priceChanges->where('colG','<=',$colG) : '';
        $priceChanges = $priceChanges->orderBy('id', 'desc')->paginate($list);

        $compact = array_merge($compact, ['menuCode','priceChanges','appends']);
        return view('admin.products.price_change',compact($compact));
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
        $message = null;
        $success = $fail = 0;
        if(isset($request->data)){
            $data = $request->data;
            if(count($data) > 0){
                for($i=0;$i<count($data);$i++){
                    $colA = $data[$i]['product_id'];
                    $product = ProductDB::find($data[$i]['product_id']);
                    if(!empty($product)){
                        $chk = PriceChangeRecordDB::where([['product_id',$data[$i]['product_id']],['is_disabled',0]])
                            ->where('colG','>',$this->timeTranslate($data[$i]['colG']))->first();
                        if(empty($chk)){
                            PriceChangeRecordDB::create([
                                'product_id' => $data[$i]['product_id'],
                                'colA' => $product->name,
                                'colB' => "https://icarry.me/product-item/".$product->id,
                                'colC' => $data[$i]['colC'],
                                'colD' => $data[$i]['colD'],
                                'colE' => $data[$i]['colE'],
                                'colF' => !empty($data[$i]['colF']) ? $this->timeTranslate(str_replace(['-',':'],['',''],$data[$i]['colF'])) : null,
                                'colG' => !empty($data[$i]['colG']) ? $this->timeTranslate(str_replace(['-',':'],['',''],$data[$i]['colG'])) : null,
                                'admin_id' => auth('admin')->user()->id,
                                'create_time' => date('Y-m-d H:i:s'),
                            ]);
                            $success++;
                        }else{
                            $message .= "$colA 商品ID已存在相同變動記錄。<br>";
                            $fail++;
                        }
                    }else{
                        $message .= "$colA 商品ID不存在資料庫。<br>";
                        $fail++;
                    }

                    Session::put('info',"已完成 $success 筆， $fail 筆失敗。<br> $message");

                }
            }else{
                Session::put('error',"未填寫資料。");
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
        $data = $request->all();
        $priceChange = PriceChangeDB::findOrFail($id);
        $priceChange->update($data);
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
        $account = PriceChangeDB::find($id)->delete();
        return redirect()->back();
    }
    /*
        啟用或停用
     */
    public function active(Request $request)
    {
        isset($request->is_disabled) ? $is_disabled = 0 : $is_disabled = 1;
        PriceChangeDB::findOrFail($request->id)->fill(['is_disabled' => $is_disabled])->save();
        return redirect()->back();
    }

    private function timeTranslate($number){
        // $number.="0000";
        $d=new DateTime($number);
        return $d->format('Y-m-d H:00:00');
    }
}
