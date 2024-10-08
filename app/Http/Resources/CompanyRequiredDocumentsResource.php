<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyRequiredDocumentsResource extends JsonResource
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
            'general_documents' => GeneralDocumentsResource::collection($this->generalDocuments)->collection->groupBy('file_type'),
            'iban4u_payment_account_documents' => IBAN4UPaymentAccountDocumentsResource::collection($this->iban4uPaymentAccountDocuments)->collection->groupBy('file_type'),
            'credit_card_processing_documents' => CreditCardProcessingDocumentsResource::collection($this->creditCardProcessingDocuments)->collection->groupBy('file_type'),
            'sepa_direct_debit_documents' => SepaDirectDebitDocumentsResource::collection($this->sepaDirectDebitDocuments)->collection->groupBy('file_type'),
            'additional_documents' => AdditionalDocumentResource::collection($this->additionalDocuments),
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
