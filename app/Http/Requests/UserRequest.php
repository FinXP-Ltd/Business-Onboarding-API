<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $optionalBoolean = 'sometimes|boolean';

        $rules = [
            'firstName' => 'required|regex:/^[a-zA-Z\s]+$/u|string|min:1|max:255',
            'lastName' => 'required|regex:/^[a-zA-Z\s]+$/u|string|min:1|max:255',
            'email' => 'required|email',
            'enabled' => $optionalBoolean,
            'emailVerified' => $optionalBoolean
        ];

        if ($this->isMethod('POST')) {
            $rules = [
                'actions' => 'sometimes|array',
                'credentials' => 'required|array',
                'credentials.password' => 'required'
            ];
        }

        return $rules;
    }
}
