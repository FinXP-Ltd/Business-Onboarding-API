<?php

namespace App\Http\Requests;

use App\Rules\ExtensionValidation;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\SecureFileName;


class UploadDocumentRequests extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */

    public function rules()
    {
        $requiredString = 'required|string';

        return [
            'file' => ['required','max:10240', new ExtensionValidation],
            'section' => $requiredString,
            'column' => $requiredString, // memorandum_and_articles_of_association, certificate_of_incorporation
            'type' =>  $requiredString, //general_documents,iban4u_payment_account_documents,credit_card_processing_documents
            'file_name' => ['required', 'string', new SecureFileName],
        ];
    }
}
