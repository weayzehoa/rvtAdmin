<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class PayMethodRequest extends FormRequest
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
            'value' => 'required|max:30',
            'type' => 'required',
            'name' => 'required|max:30',
            'name_en' => 'required|max:50',
            'image' =>'nullable|mimes:jpeg,png,jpg,gif|dimensions:min_width=100,min_height=100,max_width=500,max_height=500',
        ];
    }
}
