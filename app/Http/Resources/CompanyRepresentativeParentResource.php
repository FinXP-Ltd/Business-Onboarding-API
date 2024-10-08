<?php

namespace App\Http\Resources;

use App\Models\Indicias;
use App\Models\PoliticalPersonEntity;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\ResidentialAddress;
use App\Models\IdentityInformation;
use App\Models\UsaTaxLiability;

class CompanyRepresentativeParentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $tax = UsaTaxLiability::where('company_information_id', $this->id)->value('tax_name');
        
        return  [
            'company_representative' => CompanyRepresentativeResource::collection($this->companyRepresentative),
            'senior_management_officer' => SeniorManagementOfficerResource::make($this->seniorManagementOfficer),
            'tax_name' => $tax,
            'entities' => $this->business->politicalPersonEntity->map->only(['entity_name', 'is_selected']),
            'indicias' => $this->business->indicias->map->only(['indicia_name', 'is_selected']),
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
