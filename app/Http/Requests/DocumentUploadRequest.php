<?php

namespace App\Http\Requests;

use App\Models\Document;
use App\Models\KycpRequirement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Traits\QueryParamsValidator;
use App\Rules\ValidRequestKeys;

class DocumentUploadRequest extends FormRequest
{
    use QueryParamsValidator;
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $list = KycpRequirement::BP_REQUIREMENT;

        $types = config($list.'.document_types');
        $upperCasedTypes = array_map(fn ($type) => strtoupper($type), $types);
        $all = array_merge($types, $upperCasedTypes);

        return [
            "data" => ['required', 'array', new ValidRequestKeys([
                'document_type', 'owner_type', 'mapping_id'
            ])],
            'data.document_type' => ['required', Rule::in($all)],
            'data.owner_type' => ['required', Rule::in(Document::OWNER_TYPES)],
            'data.mapping_id' => ['required', 'min:1'],
            'file' => ['required', 'file', 'max:4000'],
        ];
    }
}
