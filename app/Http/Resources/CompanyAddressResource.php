<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyAddressResource extends JsonResource
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
            'registered_address' => [
                'registered_street_number' => $this->companyAddress?->registered_street_number ?? null,
                'registered_street_name' => $this->companyAddress?->registered_street_name ?? null,
                'registered_postal_code' => $this->companyAddress?->registered_postal_code ?? null,
                'registered_city' => $this->companyAddress?->registered_city ?? null,
                'registered_country' => $this->companyAddress?->registered_country ?? null,
            ],
            'operational_address' => [
                'operational_street_number' => $this->companyAddress?->operational_street_number ?? null,
                'operational_street_name' => $this->companyAddress?->operational_street_name ?? null,
                'operational_postal_code' => $this->companyAddress?->operational_postal_code ?? null,
                'operational_city' => $this->companyAddress?->operational_city ?? null,
                'operational_country' => $this->companyAddress?->operational_country ?? null,
            ],
            'is_same_address'  => $this->companyAddress?->is_same_address ?? false,
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
