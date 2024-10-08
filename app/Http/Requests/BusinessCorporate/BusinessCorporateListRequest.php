<?php

namespace App\Http\Requests\BusinessCorporate;

use App\Traits\QueryParamsValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessCorporateListRequest extends FormRequest
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
            'ongoing' => 'sometimes|string|' . Rule::in(['true', 'false']),
            'type' => 'sometimes|' . Rule::in(['in-progress', 'submitted'])
        ];
    }
}
