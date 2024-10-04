<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDataProtectionMarketingResource extends JsonResource
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
            'data_protection_marketing' => $this->companyDataProtectionMarketing,
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
