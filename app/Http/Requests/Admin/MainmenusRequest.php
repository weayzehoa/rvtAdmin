<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class MainmenusRequest extends FormRequest
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
            'type' => 'required|boolean|numeric',
            'name' => 'required|min:3|max:12',
            'url_type' => 'required|numeric',
            'is_on' => 'boolean|numeric',
            'open_window' => 'boolean|numeric',
        ];
    }
}
