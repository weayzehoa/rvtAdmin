<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\iCarryVendor as VendorDB;
use App\Models\iCarryVendorAccount as VendorAccountDB;
use App\Models\iCarryVendorShop as VendorShopDB;
use App\Models\iCarryProduct as ProductDB;
use App\Models\iCarryProductModel as ProductModelDB;
use DB;
use Carbon\Carbon;

trait VendorFunctionTrait
{
    protected function getVendorData($request = null,$type = null, $name = null)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $productTable = env('DB_ICARRY').'.'.(new ProductDB)->getTable();
        $productModelTable = env('DB_ICARRY').'.'.(new ProductModelDB)->getTable();
        $userId = auth('admin')->user()->id;
        $vendors = VendorDB::with('accounts','shops','products','products.models','products.vendor');

        if(isset($request['id'])){ //指定選擇的訂單
            is_array($request['id']) ? $vendors = $vendors->whereIn($vendorTable.'.id',$request['id']) : '';
        }elseif(isset($request['con'])){ //by條件
            //將進來的資料作參數轉換
            foreach ($request['con'] as $key => $value) {
                $$key = $value;
            }
        }else{
            //將進來的資料作參數轉換
            foreach ($request->all() as $key => $value) {
                $$key = $value;
            }
        }
        $userId == 14 ? $vendors = $vendors->where('categories','like',"%17%") : '';
        isset($is_on) ?$vendors = $vendors->where('is_on',$is_on) : '';

        if(!empty($keyword)){
            $vendors = $vendors->where(function($query)use($keyword){
                $query->where('name','like',"%$keyword%")
                ->orWhere('company','like',"%$keyword%")
                ->orWhere('vat_number','like',"%$keyword%")
                ->orWhere('contact_person','like',"%$keyword%");
            });
        }

        if (!isset($list)) {
            $list = 50;
        }

        if($type == 'index'){
            $vendors = $vendors->orderBy('is_on', 'desc')->orderBy('id', 'desc')->paginate($list);
        }elseif($type == 'show'){
            $vendors = $vendors->orderBy('is_on', 'desc')->orderBy('id', 'desc')->findOrFail($request['id']);
        }else{
            $vendors = $vendors->orderBy('is_on', 'desc')->orderBy('id', 'desc')->get();
        }
        return $vendors;
    }

    protected function getVendorShopData($request = null,$type = null, $name = null)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $vendorShopTable = env('DB_ICARRY').'.'.(new VendorShopDB)->getTable();

        $shops = VendorShopDB::with('vendor')->join($vendorTable,$vendorTable.'.id',$vendorShopTable.'.vendor_id');

        if(isset($request['id'])){ //指定選擇的訂單
            is_array($request['id']) ? $shops = $shops->whereIn($vendorShopTable.'.id',$request['id']) : '';
        }elseif(isset($request['con'])){ //by條件
            //將進來的資料作參數轉換
            foreach ($request['con'] as $key => $value) {
                $$key = $value;
            }
        }else{
            //將進來的資料作參數轉換
            foreach ($request->all() as $key => $value) {
                $$key = $value;
            }
        }

        isset($is_on) ? $shops = $shops->where($vendorShopTable.'.is_on',$is_on) : '';

        if(!empty($keyword)){
            $shops = $shops->where(function($query)use($keyword,$vendorTable,$vendorShopTable){
                $query->where($vendorTable.'.name','like',"%$keyword%")
                ->orWhere($vendorShopTable.'.address','like',"%$keyword%")
                ->orWhere($vendorShopTable.'.tel','like',"%$keyword%")
                ->orWhere($vendorShopTable.'.name','like',"%$keyword%");
            });
        }

        if (!isset($list)) {
            $list = 50;
        }

        $shops = $shops->select([
            $vendorShopTable.'.*',
        ]);

        if($type == 'index'){
            $shops = $shops->orderBy($vendorShopTable.'.id', 'asc')->paginate($list);
        }elseif($type == 'show'){
            $shops = $shops->orderBy($vendorShopTable.'.id', 'asc')->findOrFail($request['id']);
        }else{
            $shops = $shops->orderBy($vendorShopTable.'.id', 'asc')->get();
        }
        return $shops;
    }

    protected function getVendorAccountData($request = null,$type = null, $name = null)
    {
        $vendorTable = env('DB_ICARRY').'.'.(new VendorDB)->getTable();
        $vendorShopTable = env('DB_ICARRY').'.'.(new VendorShopDB)->getTable();
        $vendorAccountTable = env('DB_ICARRY').'.'.(new VendorAccountDB)->getTable();
        $userId = auth('admin')->user()->id;
        $accounts = VendorAccountDB::with('vendor','shop')->join($vendorTable,$vendorTable.'.id',$vendorAccountTable.'.vendor_id');

        if(isset($request['id'])){ //指定選擇的訂單
            is_array($request['id']) ? $accounts = $accounts->whereIn($vendorAccountTable.'.id',$request['id']) : '';
        }elseif(isset($request['con'])){ //by條件
            //將進來的資料作參數轉換
            foreach ($request['con'] as $key => $value) {
                $$key = $value;
            }
        }else{
            //將進來的資料作參數轉換
            foreach ($request->all() as $key => $value) {
                $$key = $value;
            }
        }

        $userId == 14 ? $accounts = $accounts->where($vendorTable.'.categories','like','17') : '';

        isset($is_on) ? $accounts = $accounts->where($vendorAccountTable.'.is_on',$is_on) : '';

        if(!empty($keyword)){
            $accounts = $accounts->where(function($query)use($keyword,$vendorAccountTable,$vendorTable){
                $query->where($vendorTable.'.name','like',"%$keyword%")
                ->orWhere($vendorAccountTable.'.name','like',"%$keyword%")
                ->orWhere($vendorAccountTable.'.account','like',"%$keyword%")
                ->orWhere($vendorAccountTable.'.email','like',"%$keyword%");
            });
        }

        if (!isset($list)) {
            $list = 50;
        }

        $accounts = $accounts->select([
            $vendorAccountTable.'.*',
        ]);

        if($type == 'index'){
            $accounts = $accounts->orderBy($vendorAccountTable.'.id', 'asc')->paginate($list);
        }elseif($type == 'show'){
            $accounts = $accounts->orderBy($vendorAccountTable.'.id', 'asc')->findOrFail($request['id']);
        }else{
            $accounts = $accounts->orderBy($vendorAccountTable.'.id', 'asc')->get();
        }
        return $accounts;
    }
}
