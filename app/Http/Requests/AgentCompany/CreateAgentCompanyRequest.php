<?php

namespace App\Http\Requests\AgentCompany;

use App\Traits\QueryParamsValidator;
use Illuminate\Foundation\Http\FormRequest;

class CreateAgentCompanyRequest extends FormRequest
{
    use QueryParamsValidator;

    public function rules()
    {
        return [
            'name' => 'required|regex:/^[a-zA-Z\s]+$/u|string|max:255|unique:agent_companies,name'
        ];
    }
}
