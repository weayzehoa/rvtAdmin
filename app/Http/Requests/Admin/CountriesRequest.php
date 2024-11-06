<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class CountriesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
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
            'name' => 'required|max:10',
            'name_en' => 'required|regex:/^[A-Za-z]+(?:[ _-][A-Za-z]+)*$/|max:100',
            'name_jp' => 'nullable|max:100',
            'name_kr' => 'nullable|max:100',
            'name_th' => 'nullable|max:100',
            'lang' => 'required|min:2|max:10',
            'code' => 'required|string|min:1|max:5',
        ];
    }
}
