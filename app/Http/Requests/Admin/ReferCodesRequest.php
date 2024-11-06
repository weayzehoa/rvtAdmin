<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class ReferCodesRequest extends FormRequest
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
            'code' => 'required|max:50',
            'point' => 'required|integer',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            // 'start_time' => 'required|date|after:tomorrow',
            // 'end_time' => 'required|date|after:start_time',
            'memo' => 'nullable|max:200',
            'point_type' => 'nullable|max:200',
        ];
    }
}
