<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class MachineListRequest extends FormRequest
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
            'name' => 'required|string|max:40',
            'contact_person' => 'required|string|max:40',
            'tel' => 'required|string|max:20',
            'fax' => 'nullable|string|max:20',
            'address' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'vendor_id' => 'required|numeric',
            'vendor_account_id' => 'required|numeric',
            'card_draw' => 'nullable|numeric',
            'alipay_draw' => 'nullable|numeric',
            'bank' => 'required|in:台新,環匯',
        ];
    }
}
