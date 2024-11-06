<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\iCarryServiceMessage as ServiceMessageDB;
use carbon\carbon;
use DB;

class ShippingInfoController extends Controller
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
        // $user_id="'7785','24633','54906'";
        // $sql="SELECT ((CASE WHEN from_id = 0 THEN message ELSE '' END) admin_message,(CASE WHEN is_read = 0 THEN '' ELSE N'已讀<br />' END) is_read,create_time,(SELECT name FROM {$_SiteGLOBAL['dbtable']}.admin WHERE id=admin_id) admin_name,(SELECT name FROM {$_SiteGLOBAL['dbtable']}.users WHERE id=from_id) user_name FROM {$_SiteGLOBAL['dbtable']}.{$table} WHERE from_id IN({$user_id}) OR to_id IN({$user_id}) AND DATE_SUB(CURDATE(), INTERVAL 4 MONTH) <= DATE(create_time) ORDER BY create_time DESC ,id DESC";
        $time = Carbon::now()->subMonths(4); //4個月前資料
        $userId = ['7785','24633','54906']; //特定使用者
        $serviceMessages = ServiceMessageDB::where('from_id',0)
        ->whereIn('to_id',$userId)
        ->where('create_time','>',$time)
        ->orderBy('create_time','desc')
        ->get();
        return view('admin.shippings.info_index',compact('serviceMessages'));
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
}
