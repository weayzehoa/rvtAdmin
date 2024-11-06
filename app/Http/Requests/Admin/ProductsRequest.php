<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class ProductsRequest extends FormRequest
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
        $type = request()->type;
        return [
            'vendor_id' => 'required|integer',
            'category_id' => 'required|array|min:1',
            'sub_categories' => 'nullable|array|min:1',
            // 'sub_categories' => 'required_with:category_id',
            'unit_name_id' => 'required|integer',
            'from_country_id' => 'required|integer',
            'name' => 'required|max:64',
            'export_name_en' => 'nullable|max:128',
            'brand' => 'required|max:64',
            'serving_size' => 'nullable|max:255',
            'shipping_methods' => 'nullable',
            'price' => 'integer',
            // 'price' => 'required_if:type,2,integer|required_if:type,1,integer',
            // 'price' => [
            //     function ($attribute, $value, $fail)use($type) {
            //         if($type != 3){
            //             if($value <= 0){
            //                 $fail('商品類型非贈品時，金額不可為0。');
            //             }
            //         }
            //     },
            // ],
            'gross_weight' => 'required|integer|min:0',
            'net_weight' => 'nullable|integer',
            'title' => 'nullable|max:64',
            'intro' => 'nullable|max:500',
            'eng_name' => 'nullable|max:250',
            'model_name' => 'nullable|max:32',
            'model_type' => 'required|digits_between:1,3',
            'is_tax_free' => 'required|boolean',
            'allow_country' => 'nullable',
            'vendor_earliest_delivery_date' => 'nullable|date',
            'vendor_latest_delivery_date' => 'nullable|date|after:vendor_earliest_delivery_date',
            'specification' => 'nullable|max:50000',
            'verification_reason' => 'required_if:status,-2|max:2000',
            // 'status' => 'required|integer|between:-9,2',
            'hotel_days' => 'required|integer|max:999',
            'airplane_days' => 'required|integer|max:999',
            'storage_life' => 'nullable|integer',
            'digiwin_product_category' => 'required',
            'fake_price' => 'nullable|integer',
            'TMS_price' => 'nullable|integer',
            'vendor_price' => 'nullable|regex:/^\d*(\.\d{1,4})?$/',
            'unable_buy' => 'nullable|max:100',
            'pause_reason' => 'nullable|max:100',
            'tags' => 'nullable',
            'ticket_price' => 'nullable|required_if:category_id,17|integer',
            'ticket_group' => 'nullable|string|max:40',
            'ticket_merchant_no' => 'nullable|string|max:15',
            'ticket_memo' => 'nullable|required_if:category_id,17|string|max:500',
            'trans_start_date' => 'nullable|required_with:trans_end_date,not null|date',
            'trans_end_date' => 'nullable|required_with:trans_start_date,not null|date',
        ];
    }
}
