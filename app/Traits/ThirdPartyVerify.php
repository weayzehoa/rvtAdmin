<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\ApiSet as ApiSetDB;
use App\Models\User as UserDB;

trait ThirdPartyVerify
{
    protected function thirdPartyVerify()
    {
        //將進來的資料作參數轉換
        $request = request()->all();
        foreach ($request as $key => $value) {
            $$key = $value;
        }
        if(!empty($partner_id) && !empty($verify)){
            //檢查使用者id
            if(!empty($icarry_uid)){
                $user = UserDB::where('status',1)->find($icarry_uid);
                if(empty($user)){
                    return 'UidFail';
                }
            }else{
                return 'UidFail';
            }
            $apiSet = ApiSetDB::where('partner_id',$partner_id)->first();
            if(!empty($apiSet)){
                $verifyCode = $this->verifyCode($request,$apiSet->key1,$apiSet->key2);
                if($verify==$verifyCode){
                    return 'Pass';
                }else{
                    return 'Fail';
                }
            }
            return 'Fail';
        }
        return null;
    }
    private function verifyCode($request,$key1,$key2){
        unset($request['verify']);
        ksort($request);
        $str="";
        foreach($request as $k=>$v){
            $str.=$k."=".$v."&";
        };
        $str=substr($str,0,-1);
        $str=$key1.$str.$key2;
        return md5($str);
    }

}
