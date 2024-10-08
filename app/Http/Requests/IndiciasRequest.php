<?php

namespace App\Http\Requests;

use App\Models\UsaTaxLiability;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndiciasRequest extends FormRequest
{

    public function rules()
    {
        $business = $this->route('business');
        $tax = UsaTaxLiability::where('company_information_id', $business->companyInformation->id)->value('tax_name');

        return [
            "indicias" => [Rule::when(in_array($tax, ["US Tax Resident"]), 'required|array|min:1')]
        ];
    }
}
