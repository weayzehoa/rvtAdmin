<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Auth;

class PromoBoxesRequest extends FormRequest
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
            'title' => 'required|max:150',
            'text_teaser' => 'required|max:300',
            'text_complete' => 'nullable|max:2000',
            'title_en' => 'nullable|max:150',
            'text_teaser_en' => 'nullable|max:300',
            'text_complete_en' => 'nullable|max:2000',
            'title_jp' => 'nullable|max:150',
            'text_teaser_jp' => 'nullable|max:300',
            'text_complete_jp' => 'nullable|max:2000',
            'title_kr' => 'nullable|max:150',
            'text_teaser_kr' => 'nullable|max:300',
            'text_complete_kr' => 'nullable|max:2000',
            'title_th' => 'nullable|max:150',
            'text_teaser_th' => 'nullable|max:300',
            'text_complete_th' => 'nullable|max:2000',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
        ];
    }
}
