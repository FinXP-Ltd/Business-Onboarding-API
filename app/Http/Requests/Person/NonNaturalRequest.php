<?php

namespace App\Http\Requests\Person;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use App\Traits\QueryParamsValidator;
use App\Rules\ValidRequestKeys;

class NonNaturalRequest extends FormRequest
{
    use QueryParamsValidator;

    const SHORT_STRING = 'required|string|max:3';
    const LONGER_STRING = 'required|string|max:150';
    const ISO_COUNTRY = ['max:3', 'string'];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules =   [
            'name'=> 'required|string',
            'tax_country'=> self::ISO_COUNTRY,
            'date_of_incorporation' => 'required|date',
            'country_of_incorporation' => self::ISO_COUNTRY,
            'name_of_shareholder_percent_held' => self::LONGER_STRING,
            "address" => ['array', new ValidRequestKeys([
                'line_1', 'line_2', 'locality', 'postal_code', 'country'
            ])],
            'address.line_1' => 'required|string|max:150',
            'address.line_2' => 'string|max:80',
            'address.locality' => 'required|string|max:120',
            'address.postal_code' => 'required|string|max:5',
            'address.country' => self::ISO_COUNTRY,
            "registration_address" => ['array', new ValidRequestKeys([
                'line_1', 'line_2', 'locality',  'postal_code', 'licensed_reputable_jurisdiction'
            ])],
            'registration_address.line_1' => 'string|max:150',
            'registration_address.line_2' => 'string|max:80',
            'registration_address.locality' => 'string|max:120',
            'registration_address.postal_code' => 'string|max:5',
            'registration_address.licensed_reputable_jurisdiction' => 'string'
        ];
        switch ($this->method()) {
            case Request::METHOD_POST:
                $rules['name'] = 'required|string|max:125|unique:non_natural_persons,name';
                $rules['registration_number']  = 'required|string|max:125|unique:non_natural_persons,registration_number';
            break;

            case Request::METHOD_PUT:
            case Request::METHOD_PATCH:
                $rules['name'] =  'required|string|max:125|unique:non_natural_persons,name,'.$this->route('nonNatural')->id;
                $rules['registration_number']  = 'required|string|max:125|unique:non_natural_persons,registration_number,'.$this->route('nonNatural')->id;
            break;
            default:
            break;
        }
        return $rules;
    }
}
