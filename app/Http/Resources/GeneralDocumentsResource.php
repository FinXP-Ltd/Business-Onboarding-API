<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GeneralDocumentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $generalDocumentsList = [
          'memorandum_and_articles_of_association' => 'Memorandum and Articles of Association',
          'certificate_of_incorporation' => 'Certificate of Incorporation',
          'registry_exact' => 'Registry exact confirming the UBOs/Shareholders and Directors of the applying company - Not older 6 months.',
          'company_structure_chart' => 'Company structure chart clearly showing from Ultimate Beneficiary Owners/Shareholders (included % Held) down to controlling persons. Chart must be signed by the Director and dated.',
          'proof_of_address_document' => 'Proof of address document showing the company&apos;s address (e.g. ulitily bill/bank statement) - not older than 6 months',
          'operating_license' => 'Operating License If any'
        ];

        return  [
            $this->file_type => $this->file_name,
            "{$this->file_type}_size" => $this->file_size,
            "{$this->file_type}_label" => $generalDocumentsList[$this->file_type],
            "{$this->file_type}_loading" => false
        ];
    }
}
