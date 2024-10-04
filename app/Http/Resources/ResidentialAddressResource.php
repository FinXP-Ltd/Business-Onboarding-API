<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResidentialAddressResource extends JsonResource
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
            'street_number' => $this->street_number ?? null,
            'street_name' => $this->street_name ?? null,
            'postal_code' => $this->postal_code ?? null,
            'city' => $this->city ?? null,
            'country' => $this->country ?? null,
            'filename_proof_of_address' => $this->filename_proof_of_address ?? null,
            'filetype_proof_of_address' => $this->filetype_proof_of_address ?? null
        ];
    }
}
