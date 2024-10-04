<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyInformationResource extends JsonResource
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
            'name' => $this->company_name ?? null,
            'registration_number' => $this->registration_number ?? null,
            'registration_type' => $this->type_of_company ?? null,
            'trading_name' => $this->company_trading_as ?? null,
            'foundation_date' => $this->date_of_incorporation ?? null,
            'tax_country' => $this->country_of_incorporation ?? null,
            'number_employees' => $this->number_of_employees ?? null,
            'number_of_years' => $this->number_of_years ?? null,
            'vat_number' => $this->vat_number ?? null,
            'tax_identification_number' => $this->tin ?? null,
            'tin_jurisdiction' => $this->tin_jurisdiction ?? null,
            'industry_type' => $this->industry_type ?? null,
            'business_activity_description' => $this->business_activity_description ?? null,
            // 'industry_description' => $this->industry_description ?? null,
            'share_capital' => $this->share_capital ?? null,
            'previous_year_turnover' => $this->previous_year_turnover ?? null,
            'email' => $this->email ?? null,
            'website' => $this->website ?? null,
            'additional_website' => $this->additional_website ?? null,
            "is_group_corporate" =>  $this->is_group_corporate ?? null,
            "parent_holding_company" => $this->parent_holding_company ?? null,
            "parent_holding_company_other" => $this->parent_holding_company_other ?? null,
            "has_fiduciary_capacity" =>  $this->company_fiduciary_capacity ?? null,
            "has_constituting_documents" => $this->allow_constituting_documents ?? null,
            "is_company_licensed" =>  $this->is_company_licensed  ?? null,
            "license_rep_juris" => $this->licensed_in ?? null,
            "contact_person_name" => $this->full_name ?? null,
            "contact_person_email" =>$this->email_address ?? null,
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}

