<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidFilter;
use App\Traits\QueryParamsValidator;


class DocumentEntityListRequest extends FormRequest
{
    use QueryParamsValidator;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
       return [
            'entity_type' => 'sometimes|string|in:UBO,SH,SH_CORPORATE,DIR,SIG,DIR_CORPORATE,B',
            'business_id' => 'sometimes|string'
        ];
    }
}
