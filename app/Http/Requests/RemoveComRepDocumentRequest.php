<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RemoveComRepDocumentRequest extends FormRequest
{
    const COM_REP_TYPES = [
        'identity_document',
        'proof_of_address',
        'source_of_wealth',
        'identity_document_addt'
    ];

    public function rules()
    {
        return [
            'type' => 'required|' . Rule::in(self::COM_REP_TYPES),
            'index' => 'required|numeric',
            'file_name' => 'required|string'
        ];
    }
}
