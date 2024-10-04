<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\QueryParamsValidator;
class CompanyCreateRequest extends FormRequest
{
    use QueryParamsValidator;

    public function rules()
    {
        return [
           'name' => 'required|regex:/^[a-zA-Z\s0-9]+$/u'
        ];
    }
}
