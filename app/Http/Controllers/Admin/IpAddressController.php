<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GateIpAddress as IpAddressDB;
use App\Models\GateAdmin as AdminDB;
use DB;

class IpAddressController extends Controller
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
        $menuCode = 'M26S5';
        $appends = [];
        $compact = [];
        $disable = ['::1','127.0.0.1','60.248.153.34','60.248.153.35','60.248.153.36'];
        $adminTable = env('DB_DATABASE').'.'.(new AdminDB)->getTable();
        $ipAddressTable = env('DB_DATABASE').'.'.(new IpAddressDB)->getTable();
        //將進來的資料作參數轉換及附加到appends及compact中
        foreach (request()->all() as $key => $value) {
            $$key = $value;
            if (isset($$key)) {
                $appends = array_merge($appends, [$key => $value]);
                $compact = array_merge($compact, [$key]);
            }
            !empty($value) ? $con[$key] = $value : '';
        }
        if (!isset($list)) {
            $list = 50;
            $compact = array_merge($compact, ['list']);
        }
        $ips = IpAddressDB::select([
                '*',
                DB::raw("(CASE WHEN admin_id = 0 THEN '系統預設' ELSE (SELECT name from admins where ip_addresses.admin_id = admins.id limit 1) END) as name"),
            ])->orderBy('created_at','desc')->paginate($list);;
        $admins = AdminDB::orderBy('is_on','desc')->get();

        $compact = array_merge($compact, ['menuCode','ips','admins','disable']);
        return view('admin.settings.ipsetting', compact($compact));
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
        IpAddressDB::create($request->all());
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
        IpAddressDB::findOrFail($id)->update($request->all());
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
        IpAddressDB::findOrFail($id)->delete();
        return redirect()->back();
    }
    /*
        啟用或禁用
     */
    public function active(Request $request)
    {
        isset($request->is_on) ? $is_on = $request->is_on : $is_on = 0;
        IpAddressDB::findOrFail($request->id)->fill(['is_on' => $is_on])->save();
        return redirect()->back();
    }
}
