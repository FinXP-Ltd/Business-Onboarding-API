<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CreditCardProcessingDocumentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $creditCardDocumentList = [
            'proof_of_ownership_of_the_domain' => 'Proof of ownership of the domain',
            'processing_history' => 'Processing History',
            'cc_copy_of_bank_settlement' => 'Copy of Bank settlement of the account that will receive settlements',
            'company_pci_certificate' => 'Company PCI certificate - Or PCI self-assessment questionnaire'
        ];

       return  [
            $this->file_type => $this->file_name,
            "{$this->file_type}_size" => $this->file_size,
            "{$this->file_type}_label" => $creditCardDocumentList[$this->file_type],
            "{$this->file_type}_loading" => false
        ];
    }
}
