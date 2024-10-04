<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'finxp_products' => LookupableResource::collection($this->getLookupGroupFields('GENpweproduct')),
            'industry_key' => LookupableResource::collection($this->getLookupGroupFields('GENindustry')),
            'description' => $this->description,
            'business_purpose' => $this->business_purpose,
            'number_employees' => $this->number_employees,
            'number_of_years' => $this->number_of_years,
            'share_capital' => $this->share_capital,
            'number_shareholder' => $this->number_shareholder,
            'number_directors' => $this->number_directors,
            'previous_year_turnover' => $this->previous_year_turnover,
            'license_rep_juris' => $this->license_rep_juris,
            'country_of_license' => LookupableResource::collection($this->countryOfLicense),
            'country_juris_dealings' => LookupableResource::collection($this->countryJurisDealings),
            'business_year_count' => $this->business_year_count,
            'source_of_funds' => LookupableResource::collection($this->sourceOfFunds),
            'source_of_funds_other' => LookupableResource::collection($this->sourceOfFundsOther),
            'country_source_of_funds' => LookupableResource::collection($this->countrySourceOfFunds),
            'source_of_wealth' => LookupableResource::collection($this->sourceOfWealth),
            'source_of_wealth_other' => LookupableResource::collection($this->sourceOfWealthOther),
            'country_source_of_wealth' => LookupableResource::collection($this->countrySourceOfWealth),
            'political_person_entity' => LookupableResource::collection($this->getLookupGroupFields('GENpoliticalPersonEntity')),
            'usa_tax_liability' => LookupableResource::collection($this->getLookupGroupFields('GENusaTaxLiability')),
            'indicias' => LookupableResource::collection($this->getLookupGroupFields('GENindicias')),
            'terms_and_conditions' => $this->terms_and_conditions,
            'privacy_accepted' => $this->privacy_accepted,
            'mid' => $this->mid,
            'creditor_identifier' => $this->creditor_identifier,
            "is_part_of_group" => $this->is_part_of_group,
            "parent_holding_company" => $this->parent_holding_company,
            "parent_holding_company_other" => $this->parent_holding_company_other,
            "has_fiduciary_capacity" => $this->has_fiduciary_capacity,
            "has_constituting_documents" => $this->has_constituting_documents,
            "is_company_licensed" => $this->is_company_licensed,
            "contact_person_name" => $this->contact_person_name,
            "contact_person_email" => $this->contact_person_email,
        ];
    }
}
