<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class ShippingFeesRequest extends FormRequest
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
            'product_sold_country' => 'required',
            'shipping_methods' => 'required',
            'free_shipping' => 'required|integer',
            'price' => 'required|integer',
            'tax_rate' => 'required|numeric|max:100', //numeric 可以有小數點
            'shipping_type' => 'required|string|max:5',
            'description' => 'max:250',
            'description_en' => 'max:250',
            'fill_vendor_earliest_delivery_date_tw' => 'max:250',
            'fill_vendor_earliest_delivery_date_en' => 'max:250',
            'is_on' => 'numeric|boolean'
        ];
    }
}
