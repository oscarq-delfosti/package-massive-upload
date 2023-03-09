<?php

namespace Delfosti\Massive\Requests\MassiveUpload;

use Illuminate\Foundation\Http\FormRequest;

class UploaderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'action' => 'required',
            'file_name' => 'required',
            'items' => 'required|array',
            'user' => 'required|numeric'
        ];
    }
}
