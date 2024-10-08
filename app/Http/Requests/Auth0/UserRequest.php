<?php

namespace App\Http\Requests\Auth0;

use App\Rules\ValidRequestKeys;
use App\Traits\QueryParamsValidator;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    use QueryParamsValidator;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $optionalBoolean = 'sometimes|boolean';
        $rules = [
            'user' => ['required', 'array', new ValidRequestKeys([
                'first_name', 'last_name', 'email', 'is_active', 'blocked'
            ])],
            'user.first_name' => 'required|string|regex:/^[a-zA-Z\s]+$/u|min:1|max:255',
            'user.last_name' => 'required|string|regex:/^[a-zA-Z\s]+$/u|min:1|max:255',
            'user.email' => 'required|email',
            'user.is_active' => $optionalBoolean,
            'user.blocked' => $optionalBoolean,
            'roles' => 'required|array',
            'note' => 'sometimes|array',
            'company' => 'sometimes|string'
        ];

        if ($this->isMethod('put')) {
            $rules = [
                'user' => ['required', 'array', new ValidRequestKeys([
                    'email', 'is_active', 'blocked'
                ])],
                'user.email' => 'sometimes|email',
                'user.is_active' => $optionalBoolean,
                'user.blocked' => $optionalBoolean,
                'company' => 'sometimes|string'
            ];
        }

        return $rules;
    }
}
