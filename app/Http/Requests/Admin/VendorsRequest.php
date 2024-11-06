<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class VendorsRequest extends FormRequest
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
            'name' => 'required|max:40',
            'company' => 'required|max:64',
            'VAT_number' => 'required|numeric|digits:8',
            'boss' => 'required|max:20',
            'digiwin_vendor_no' => 'nullable|string|max:20',
            'contact_person' => 'required|max:20',
            'tel' => 'required|string|max:20',
            'fax' => 'nullable|max:20',
            'email' => 'required|max:500',
            'notify_email' => 'required|max:500',
            'bill_email' => 'required|max:500',
            'categories' => 'required|array|min:1',
            'address' => 'required|max:255',
            'shipping_setup' => 'required|numeric|max:99999999',
            'shipping_verdor_percent' => 'required|numeric|max:100',
            'summary' => 'nullable|max:500',
            'description' => 'nullable|max:1000',
            'factory_address' => 'required|max:255',
            'product_sold_country' => 'required',
            'pause_start_date' => 'nullable|date',
            'pause_end_date' => 'nullable|date',
            // 'service_fee.percent' => 'numeric|max:100',
        ];
    }
}
