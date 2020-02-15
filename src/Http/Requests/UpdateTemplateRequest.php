<?php

namespace Rupalipshinde\Template\Http\Requests;
use Illuminate\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTemplateRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'title' => 'required',
            'description' => 'required',
            'subject' => 'required'
        ];
    }
    
     
    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes() {
       return [
            'title' => trans('translations.title'),
            'description' => trans('translations.description'),
            'subject' => trans('translations.subject'),
        ];
    }

}
