<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SepaDirectDebitDocumentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $sepaDirectDebitList = [
            'template_of_customer_mandate' => 'Template of Customer Mandate - (Paper format/digital or recorded)',
            'processing_history_with_chargeback_and_ratios' => 'Processing History including Chargebacks and Ratios',
            'sepa_copy_of_bank_settlement' => 'Copy of Bank settlement of the account that will receive settlements',
            'product_marketing_information' => 'Product leaflets or Product marketing information'
        ];

       return  [
            $this->file_type => $this->file_name,
            "{$this->file_type}_size" => $this->file_size,
            "{$this->file_type}_label" => $sepaDirectDebitList[$this->file_type],
            "{$this->file_type}_loading" => false
        ];
    }
}
