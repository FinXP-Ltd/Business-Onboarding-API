<?php

namespace App\Http\Resources;

use App\Models\SeniorManagementOfficer;
use App\Models\SeniorManagementOfficerDocuments;
use Illuminate\Http\Resources\Json\JsonResource;

class SeniorManagementOfficerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
          return  [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'surname' => $this->surname,
            'place_of_birth' => $this->place_of_birth,
            'date_of_birth' => $this->date_of_birth,
            'nationality' => $this->nationality,
            'citizenship' => $this->citizenship,
            'email_address' => $this->email_address,
            'phone_code' => $this->phone_code,
            'phone_number' => $this->phone_number,
            'roles_in_company' => $this->roles_in_company,
            'residential_address' => $this->seniorOfficerResidentialAddress,
            'identity_information' => SeniorManagementOfficerIdentityResource::make($this->seniorOfficerIdentityInformation),
            'senior_management_officer_document' => SeniorManagementOfficerDocumentsResource::make($this->seniorManagementOfficerDocuments),
            'required_indicator' => $this->required_indicator
        ];
    }
}
