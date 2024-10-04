<?php

namespace App\Http\Requests;

use App\Models\Business;
use App\Traits\QueryParamsValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessProductRequest extends FormRequest
{
    use QueryParamsValidator;

    public function rules()
    {
        $finxp_products = [Business::IBAN4U, Business::CC_PROCESSING, Business::SEPADD];

        return [
            "products" => ['required','array', 'min:1', Rule::in($finxp_products)],
            "corporate_saving" => ['sometimes','boolean'],
            "section" => ['sometimes', 'string'],
            "name" => ['sometimes', 'string']
        ];
    }
}
