<?php

namespace App\Http\Requests\Person;

use App\Models\Person\NaturalPerson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Validator;
use App\Traits\QueryParamsValidator;
use App\Rules\ValidRequestKeys;

class NaturalRequest extends FormRequest
{
    use QueryParamsValidator;

    const SHORT_STRING = 'required|string|max:3';
    const LONG_STRING = 'required|string|max:125';
    const PEP = ['true','false', 1, 0];
    const ISO_COUNTRY = ['max:3', 'string'];
    const REQUIRED_ISO_COUNTRY = ['required','max:3', 'string'];

    public function rules()
    {
        Validator::extend('uniqueNameAndBirth', function () {
            $hashedName = NaturalPerson::getHashedValue($this->input('name'));
            $hashedDOB = NaturalPerson::getHashedValue($this->input('date_of_birth'));

            $naturalPersons = NaturalPerson::where([
                ['name_bidx', 'like', "%{$hashedName}%"],
                ['surname', 'like', "%{$this->input('surname')}%"],
                ['date_of_birth_bidx', 'like', "%{$hashedDOB}%"],
            ]);

            $method = $this->method();

            if(in_array($method, [Request::METHOD_PUT, Request::METHOD_PATCH])
                && ($naturalPersons->first()?->id === $this->route('natural')->id)) {
                return true;
            }

            return $naturalPersons->count() === 0;
        }, 'Person with the same name, surname, date_of_birth already exists');

        Validator::extend('titleOptions', function () {
            return in_array(strtoupper($this->input('title')), NaturalPerson::PERSON_TITLE);
        },'Title is invalid');

        Validator::extend('genderOptions', function () {
            return in_array(strtoupper($this->input('sex')), NaturalPerson::GENDER);
        },'Sex is invalid');

        Validator::extend('pepOptions', function () {
            return in_array($this->input('additional_info.pep'), self::PEP);
        },'Pep is invalid');

        return  [
            'name' =>'required|string|regex:/^[a-zA-Z\s]+$/u|max:50|uniqueNameAndBirth',
            'surname' => 'required|string|regex:/^[a-zA-Z\s]+$/u|max:50|uniqueNameAndBirth',
            'title' => 'required|string|titleOptions',
            'sex' => 'required|string|genderOptions',
            'date_of_birth' => 'required|date|uniqueNameAndBirth',
            'place_of_birth' => self::REQUIRED_ISO_COUNTRY,
            'email_address' => 'required|email|max:125',
            'country_code' => 'string',
            'mobile' => 'required|string|max:15',
            "address" => ['array', new ValidRequestKeys([
                'line_1', 'line_2', 'locality', 'city', 'postal_code', 'nationality', 'country'
            ])],
            'address.line_1' => 'required|string|max:150',
            'address.line_2' => 'required|string|max:80',
            'address.locality' => 'required|string|max:120',
            'address.postal_code' => 'required|string|max:5',
            'address.city' => 'string|max:150',
            'address.nationality' => self::ISO_COUNTRY,
            'address.country' => self::ISO_COUNTRY,
            "identification_document" => ['array', new ValidRequestKeys([
                'document_type', 'document_number', 'document_country_of_issue', 'document_expiry_date'
            ])],
            'identification_document.document_type' => 'required|string',
            'identification_document.document_number' => 'required|string',
            'identification_document.document_country_of_issue' => self::ISO_COUNTRY,
            'identification_document.document_expiry_date' => 'required|date',
            "additional_info" => ['array', new ValidRequestKeys([
                'occupation', 'employment', 'position', 'source_of_income', 'source_of_wealth', 'us_citizenship',
                'source_of_wealth_details', 'other_source_of_wealth_details', 'pep', 'tin', 'country_tax'
            ])],
            'additional_info.occupation' => self::LONG_STRING,
            'additional_info.employment' => self::LONG_STRING,
            'additional_info.position' => self::LONG_STRING,
            'additional_info.source_of_income' => self::LONG_STRING,
            'additional_info.source_of_wealth' => self::LONG_STRING,
            'additional_info.source_of_wealth_details' => self::LONG_STRING,
            'additional_info.other_source_of_wealth_details' => self::LONG_STRING,
            'additional_info.pep' => 'required|pepOptions',
            'additional_info.us_citizenship' => 'required|boolean',
            'additional_info.tin' => self::LONG_STRING,
            'additional_info.country_tax' => self::ISO_COUNTRY,
        ];
    }
}
