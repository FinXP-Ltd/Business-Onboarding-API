<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxInformationResource extends JsonResource
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
            'tax_country' => $this->tax_country,
            'registration_number' => $this->registration_number,
            'registration_type' => $this->registration_type,
            'tax_identification_number' => $this->tax_identification_number,
            'jurisdiction' => $this->jurisdiction,
        ];
    }
}
