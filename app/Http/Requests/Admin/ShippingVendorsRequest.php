<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class ShippingVendorsRequest extends FormRequest
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
            'name' => 'required|max:20',
            'name_en' => 'nullable|regex:/^[A-Za-z0-9]+(?:[ _-][A-Za-z0-9]+)*$/|max:50',
            'tel' => 'alpha_dash',
        ];
    }
}
