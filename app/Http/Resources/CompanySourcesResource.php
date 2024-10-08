<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanySourcesResource extends JsonResource
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
            'source_of_wealth' => $this->companySource->where('type', 'wealth')->where('is_selected', true)->pluck('source_name')->toArray(),
            'source_of_wealth_other' => $this->companySource->where('type', 'wealth')->where('source_name', 'Other')->first()->other_value ?? null,
            'source_of_funds' => $this->business->companyInformation->source_of_funds,
            'country_source_of_funds' => $this->companySourceCountry->where('type', 'fund')->where('is_selected', true)->pluck('country')->toArray(),
            'country_source_of_wealth' => $this->companySourceCountry->where('type', 'wealth')->where('is_selected', true)->pluck('country')->toArray(),
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
