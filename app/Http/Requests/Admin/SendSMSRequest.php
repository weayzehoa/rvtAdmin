<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class SendSMSRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    //必須登入才可以使用這個Request
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'vendor'=>'required',
            // 'ncode'=>'required',
            // 'phone'=>'required|regex:/[0-9]{9,11}/',
            'phones'=>'required|string|max:140',
            // 'phone'=>'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10|max:15',
            'content'=>'required|string|max:75',
        ];
    }
}
