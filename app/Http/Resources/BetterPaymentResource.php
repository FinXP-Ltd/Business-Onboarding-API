<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BusinessComposition;

class BetterPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $business_composition_list = BusinessComposition::where('business_id', $this->id)->get();

        return [
            'id' => $this->id,
            'name' => $this->taxInformation?->name,
            'trading_name' => $this->trading_name,
            'foundation_date' => $this->foundation_date,
            'vat_number' => $this->vat_number,
            'telephone' => str_replace('+', '', $this->telephone),
            'email' => $this->email,
            'website' => $this->website,
            'additional_website' => $this->additional_website,
            'IBAN' => $this->IBAN,
            'external_identifier' => $this->external_identifier,
            'tax_information' => TaxInformationResource::make($this->taxInformation),
            'registered_address' => BusinessAddressResource::make($this->registeredAddress),
            'operational_address' => BusinessAddressResource::make($this->operationalAddress),
            'contact_details' => ContactDetailResource::make($this->contactDetails),
            'business_details' => BusinessDetailResource::make($this->businessDetails),
            'iban4u_payment_account' => IBAN4UPaymentAccountResource::make($this->iban4uPaymentAccount),
            'sepa_dd_direct_debit' => SepaDdDirectDebitResource::make($this->sepaDdDirectDebit),
            'credit_card_processing' => CreditCardProcessingResource::make($this->creditCardProcessing),
            'business_composition' => BusinessCompositionResource::collection($business_composition_list),
            'disabled' => ($this->status == "PRESUBMIT")? true: false,
            'created_by' => $this->user,
        ];
    }
}
