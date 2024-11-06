<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\iCarryReferCode as ReferCodeDB;
use App\Models\iCarryUser as UserDB;
use Auth;
use Carbon\Carbon;
use DB;

class ReferCodesController extends Controller
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
        $menuCode = 'M7S3';
        $appends = $compact = [];

        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
        }

        //找出資料
        $referCodes = ReferCodeDB::with('users')->orderBy('end_time','desc');

        // isset($register) && $register > 0 ? $referCodes = $referCodes->where('register','>', $register) : '';
        // "SELECT refer_code,COUNT(id) AS total_count FROM users WHERE refer_code IS NOT NULL GROUP BY refer_code HAVING total_count > 100"

        if(isset($register) && $register > 0){
            $codes = UserDB::where('refer_code','!=','')->select([
                        'refer_code',
                        DB::raw('count(id) as count'),
                    ])->groupBy('refer_code')
                    ->having('count', '>', $register)
                    ->get()->pluck('refer_code')->all();
            $referCodes = $referCodes->whereIn('code',$codes);
        }

        if (isset($keyword) && $keyword) {
            $referCodes = $referCodes->where(function ($query) use ($keyword) {
                $query->where('code', 'like', "%$keyword%")
                ->orWhere('memo', 'like', "%$keyword%");
            });
        }

        if (!isset($list)) {
            $list = 15;
            $compact = array_merge($compact, ['list']);
        }

        //找出最終資料
        $referCodes = $referCodes->orderBy('create_time','desc')->paginate($list);

        $compact = array_merge($compact, ['menuCode','referCodes','appends']);
        return view('admin.refercodes.index', compact($compact));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menuCode = 'M7S3';
        return view('admin.refercodes.show',compact(['menuCode']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $referCode = ReferCodeDB::create($data);
        return redirect()->route('admin.refercodes.show', $referCode->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menuCode = 'M7S3';
        $referCode = ReferCodeDB::findOrFail($id);
        return view('admin.refercodes.show', compact(['referCode','menuCode']));
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
        isset($data['status']) ? '' : $data['status'] = 0;
        $referCode = ReferCodeDB::findOrFail($id);
        $referCode->update($data);
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
        啟用或禁用
    */
    public function active(Request $request)
    {
        isset($request->status) ? $status = $request->status : $status = 0;
        ReferCodeDB::findOrFail($request->id)->fill(['status' => $status])->save();
        return redirect()->back();
    }
}
