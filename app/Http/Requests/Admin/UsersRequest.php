<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class UsersRequest extends FormRequest
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
            'name' => 'nullable|max:20',
            'nation' => 'required|max:5',
            'mobile' => 'required|numeric',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|max:255',
            'verify_code' => 'nullable|numeric',
            'mark' => 'nullable|max:255',
        ];
    }
}
