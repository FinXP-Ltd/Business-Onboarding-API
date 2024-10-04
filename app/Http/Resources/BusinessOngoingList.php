<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BusinessComposition;
use App\Models\CompanyRepresentative;

class BusinessOngoingList extends JsonResource
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
            'id' => $this->id,
            'name' => $this->taxInformation?->name,
            'foundation_date' => $this->foundation_date,
            'vat_number' => $this->vat_number,
            'email' => $this->email,
            'registration_number' => $this->taxInformation?->registration_number,
            'registration_type' => $this->taxInformation?->registration_type,
            'tax_identification_number' => $this->taxInformation?->tax_identification_number,
            'status' => $this->status,
            'created_by' => $this->user
        ];
    }
}
