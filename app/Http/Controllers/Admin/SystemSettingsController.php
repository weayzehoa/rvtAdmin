<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\GateSystemSetting as SystemSettingDB;
use App\Models\iCarryOrder as OrderDB;
use App\Models\iCarrySiteSetup as SiteSetupDB;
use App\Http\Requests\Admin\SystemSettingsRequest;
use DB;

class SystemSettingsController extends Controller
{
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
        $menuCode = 'M1S5';
        $siteSetup = SiteSetupDB::findOrFail(1);
        $system = SystemSettingDB::with('admin')->findOrFail(1); //只有一筆
        $twpayUsed = OrderDB::where('promotion_code','TWPAY')->select([
            DB::raw('SUM(discount) as discount')
        ])->first()->discount;
        return view('admin.settings.system', compact('menuCode','system','siteSetup','twpayUsed'));
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
    public function update(SystemSettingsRequest $request, $id)
    {
        $data = $request->all();
        $data['admin_id'] = Auth::user()->id;
        $data['exchange_rate'] = $data['exchange_rate_RMB'];
        $system = SystemSettingDB::with('admin')->findOrFail($id)->update($data);
        $siteSetup = SiteSetupDB::findOrFail($id)->update($data);
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
}
