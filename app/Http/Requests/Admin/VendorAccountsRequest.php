<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class VendorAccountsRequest extends FormRequest
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
            'account' => 'required|min:3|max:20',
            'password' => 'nullable|min:3',
            'email' => 'nullable|email|max:100',
            // 'vendor_shop_id' => 'required|numeric',
            'vendor_id' => 'required|numeric',
        ];
    }
}
