<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyCreditCardProcessingResource extends JsonResource
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
            'credit_card_processing' => [
                'currently_processing_cc_payments' => $this->companyCreditCardProcessing->currently_processing_cc_payments,
                'trading_urls' => $this->companyCreditCardProcessing->companyTradingUrl?->pluck('trading_urls')->toArray(),
                'offer_recurring_billing' => $this->companyCreditCardProcessing->offer_recurring_billing,
                'frequency_offer_billing' => $this->companyCreditCardProcessing->frequency_offer_billing ?? null,
                'if_other_offer_billing' => $this->companyCreditCardProcessing->if_other_offer_billing ?? null,
                'offer_refunds' => $this->companyCreditCardProcessing->offer_refunds,
                'frequency_offer_refunds' => $this->companyCreditCardProcessing->frequency_offer_refunds ?? null,
                'if_other_offer_refunds' => $this->companyCreditCardProcessing->if_other_offer_refunds ?? null,
                'countries' => $this->companyCreditCardProcessing->companyCountries ?? [],
                'processing_account_primary_currency' => $this->companyCreditCardProcessing->processing_account_primary_currency ?? null,
                'highest_ticket_amount' => $this->companyCreditCardProcessing->highest_ticket_amount ?? null,
                'average_ticket_amount' => $this->companyCreditCardProcessing->average_ticket_amount ?? null,
                'alternative_payment_methods' => $this->companyCreditCardProcessing->alternative_payment_methods ?? null,
                'payment_method_currently_offered' => $this->companyCreditCardProcessing->payment_method_currently_offered ?? null,
                'current_mcc' => $this->companyCreditCardProcessing->current_mcc ?? null,
                'current_descriptor' => $this->companyCreditCardProcessing->current_descriptor ?? null,
                'cb_volumes_twelve_months' => $this->companyCreditCardProcessing->cb_volumes_twelve_months ?? null,
                'sales_volumes_twelve_months' => $this->companyCreditCardProcessing->sales_volumes_twelve_months ?? null,
                'refund_twelve_months' => $this->companyCreditCardProcessing->refund_twelve_months ?? null,
                'current_acquire_psp' => $this->companyCreditCardProcessing->current_acquire_psp ?? null
            ],
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
