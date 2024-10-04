<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PoliticalPersonEntityRequest extends FormRequest
{
    public function rules()
    {
        return [
            "entities" => 'required|array|min:1',
        ];
    }
}
