<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SeniorManagementOfficerDocumentsResource extends JsonResource
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
            "senior_officer_id" => $this->senior_officer_id,
            "proof_of_address" => $this->proof_of_address,
            "proof_of_address_size" => $this->proof_of_address_size,
            "identity_document" => $this->identity_document,
            "identity_document_size" => $this->identity_document_size,
            "identity_document_addt" => $this->identity_document_addt,
            "identity_document_addt_size" => $this->identity_document_addt_size,
        ];
    }
}
