<?php

namespace App\Http\Requests\Auth0;

use App\Rules\ValidRequestKeys;
use App\Traits\QueryParamsValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserListRequest extends FormRequest
{
    use QueryParamsValidator;

    public function rules()
    {
        $optionalString = 'sometimes|string';
        $optionalBoolean = 'sometimes|boolean';

        return [
            'page' => 'sometimes|numeric',
            'limit' => 'sometimes|numeric',
            'sort' => $optionalString,
            'direction' => [
                'sometimes',
                Rule::in(['asc', 'desc'])
            ],
            'filter' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'first_name', 'last_name', 'email', 'role', 'is_active'
                ])
            ],
            'filter.first_name' => $optionalString,
            'filter.last_name' => $optionalString,
            'filter.email' => $optionalString,
            'filter.role' => $optionalString,
            'filter.is_active' => $optionalBoolean
        ];
    }
}
