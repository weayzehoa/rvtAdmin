<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\LanguagePack as LanguagePackDB;

trait LanguagePack
{
    protected function translate($twText = null)
    {
        !empty(request()->lang) ? $this->lang = request()->lang : $this->lang = 'tw';
        if(!empty($twText)){
            !is_array($twText) ? $twText = [$twText] : '';
            $translate = LanguagePackDB::whereIn('tw', $twText)->get();
            if(count($translate) > 0){
                for($i=0;$i<count($twText);$i++){
                    if(!empty($this->lang)){
                        foreach ($translate as $t) {
                            if($t->tw == $twText[$i]){
                                empty($t->en) ?? $t->tw; //英文不存在以中文替換
                                empty($t->{$this->lang}) ?? $t->{$this->lang} = $t->en; //其他語言找不到以英文替換
                                $data[$twText[$i]] = $t->{$this->lang};
                            }
                        }
                        //找不到任何資料則以原本進來的中文替換
                        empty($data[$twText[$i]]) ? $data[$twText[$i]] = $twText[$i] : '';
                    }
                }
            }else{ //找不到任何資料則以原本進來的中文替換
                for ($i=0;$i<count($twText);$i++) {
                    $data[$twText[$i]] = $twText[$i];
                }
            }
            return $data;
        }else{
            return null;
        }
    }
}
