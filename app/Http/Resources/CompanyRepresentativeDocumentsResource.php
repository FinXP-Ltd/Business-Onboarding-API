<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyRepresentativeDocumentsResource extends JsonResource
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
            "company_representative_id" => $this->company_representative_id,
            "index" => $this->index,
            "order" => $this->order,
            "proof_of_address" => $this->proof_of_address,
            "proof_of_address_size" => $this->proof_of_address_size,
            "identity_document" => $this->identity_document,
            "identity_document_size" => $this->identity_document_size,
            "identity_document_addt" => $this->identity_document_addt,
            "identity_document_addt_size" => $this->identity_document_addt_size,
            "source_of_wealth" => $this->source_of_wealth,
            "source_of_wealth_size" => $this->source_of_wealth_size,
        ];
    }
}
