<?php

namespace App\Http\Resources;

use App\Models\CompanyRepresentativeDocuments;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ResidentialAddress;
use App\Models\IdentityInformation;

class CompanyRepresentativeResource extends JsonResource
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
            'index' => $this->index,
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
            'phone_code_number' => $this->phone_code.$this->phone_number,
            'roles' => $this->rolesPercentOwnership,
            'residential_address' => $this->residentialAddress,
            'identity_information' => CompanyIdentityInformationResource::make($this->identityInformation),
            'company_representative_document' => CompanyRepresentativeDocumentsResource::make($this->companyRepresentativeDocument)
        ];
    }
}
