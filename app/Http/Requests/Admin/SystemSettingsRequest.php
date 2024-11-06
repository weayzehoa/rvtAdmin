<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class SystemSettingsRequest extends FormRequest
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
            'exchange_rate_RMB' => 'required|regex:/^\d{1,2}+(\.\d{1,2})?$/',
            'exchange_rate_SGD' => 'required|between:0,99.99',
            'exchange_rate_MYR' => 'required|between:0,99.99',
            'exchange_rate_HKD' => 'required|between:0,99.99',
            'exchange_rate_USD' => 'required|between:0,99.99',
            'airport_shipping_fee' => 'required|integer',
            'airport_shipping_fee_over_free' => 'required|integer',
            'shipping_fee' => 'required|integer',
            'shipping_fee_over_free' => 'required|integer',
            'pre_order_start_date' => 'nullable|date',
            'pre_order_end_date' => 'nullable|date',
        ];
    }
}
