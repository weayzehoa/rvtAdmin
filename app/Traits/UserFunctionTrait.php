<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\iCarryUser as UserDB;
use DB;
use Carbon\Carbon;

trait UserFunctionTrait
{
    protected function getUserData($request = null,$type = null, $name = null)
    {
        $aesKey = env('APP_AESENCRYPT_KEY');
        $userTable = env('DB_ICARRY').'.'.(new UserDB)->getTable();
        $users = new UserDB;

        if(isset($request['id'])){ //指定選擇的訂單
            is_array($request['id']) ? $users = $users->whereIn($userTable.'.id',$request['id']) : '';
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
        isset($referCode) && $referCode != '' ? $users = $users->where($userTable.'.refer_code',$referCode) : '';
        isset($status) && $status != null ? $users = $users->where('status',$status) : '';
        isset($user_id) ? $users = $users->where('id',$user_id) : '';
        isset($mobile) ? $users = $users->whereRaw(" AES_DECRYPT($userTable.mobile,'$aesKey') like '%$mobile%' ") : '';
        if(!empty($keyword)){
            $users = $users->where(function($query)use($keyword,$userTable,$aesKey){
                $query->where($userTable.'.name','like',"%$keyword%")
                ->orwhere($userTable.'.address','like',"%$keyword%")
                ->orwhere($userTable.'.email','like',"%$keyword%")
                ->orwhere($userTable.'.refer_id','like',"%$keyword%")
                ->orwhere($userTable.'.refer_code','like',"%$keyword%");
            });
        }
        if(!empty($search)){
            $users = $users->where(function($query)use($search,$userTable){
                $query->where($userTable.'.name','like',"%$search%")
                ->orwhere($userTable.'.address','like',"%$search%")
                ->orwhere($userTable.'.email','like',"%$search%")
                ->orwhere($userTable.'.refer_id','like',"%$search%")
                ->orwhere($userTable.'.refer_code','like',"%$search%");
            });
        }

        if (!isset($list)) {
            $list = 50;
        }

        $users = $users->select([
            $userTable.'.*',
            DB::raw("IF($userTable.mobile IS NULL,'',AES_DECRYPT($userTable.mobile,'$aesKey')) as mobile"),
        ]);

        if($type == 'index'){
            $users = $users->orderBy($userTable.'.is_mark','desc')->orderBy($userTable.'.status', 'desc')->orderBy($userTable.'.id', 'desc')->paginate($list);
        }elseif($type == 'show'){
            $users = $users->findOrFail($request['id']);
        }else{
            $users = $users->orderBy($userTable.'.status', 'desc')->orderBy($userTable.'.id', 'desc')->get();
        }
        return $users;
    }
}
