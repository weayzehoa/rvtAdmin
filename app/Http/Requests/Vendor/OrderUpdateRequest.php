<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class OrderUpdateRequest extends FormRequest
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
            'express_way' => 'required_without:vendor_memo|max:40',
            'express_no' => 'required_with:express_way|max:40',
            'vendor_memo' => 'required_without:express_way|max:200'
        ];
    }
}
