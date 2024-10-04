<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CreditCardProcessingResource extends JsonResource
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
            'currently_processing_cc_payments' => $this->currently_processing_cc_payments,
            'trading_urls' => count($this->tradingUrl->pluck('trading_urls')->toArray()) > 0 ? $this->tradingUrl->pluck('trading_urls')->toArray() : $this->tranding_urls,
            'offer_recurring_billing' => $this->offer_recurring_billing,
            'frequency_offer_billing' => $this->frequency_offer_billing ?? null,
            'if_other_offer_billing' => $this->if_other_offer_billing ?? null,
            'recurring_details' => $this->recurring_details ?? null,
            'offer_refunds' => $this->offer_refunds,
            'frequency_offer_refunds' => $this->frequency_offer_refunds ?? null,
            'if_other_offer_refunds' => $this->if_other_offer_refunds ?? null,
            'refund_details' => $this->refund_details ?? null,
            'country' => $this->country ?? null,
            'countries' => $this->countries ?? null,
            'distribution_sale_volume' => $this->distribution_sale_volume ?? null,
            'processing_account_primary_currency' => $this->processing_account_primary_currency ?? null,
            'average_ticket_amount' => $this->average_ticket_amount ?? null,
            'highest_ticket_amount' => $this->highest_ticket_amount ?? null,
            'ac_average_ticket_amount' => $this->ac_average_ticket_amount ?? null,
            'ac_highest_ticket_amount' => $this->ac_highest_ticket_amount ?? null,
            'other_alternative_payment_methods' => $this->other_alternative_payment_methods ?? null,
            'other_alternative_payment_method_used' => $this->other_alternative_payment_method_used ?? null,
            'current_mcc' => $this->current_mcc ?? null,
            'ac_current_mcc' => $this->ac_current_mcc ?? null,
            'current_descriptor' => $this->current_descriptor ?? null,
            'ac_current_descriptor' => $this->ac_current_descriptor ?? null,
            'cb_volumes_twelve_months' => $this->cb_volumes_twelve_months ?? null,
            'cc_volumes_twelve_months' => $this->cc_volumes_twelve_months ?? null,
            'refund_volumes_twelve_months' => $this->refund_volumes_twelve_months ?? null,
            'ac_cb_volumes_twelve_months' => $this->ac_cb_volumes_twelve_months ?? null,
            'ac_cc_volumes_twelve_months' => $this->ac_cc_volumes_twelve_months ?? null,
            'ac_refund_volumes_twelve_months' => $this->ac_refund_volumes_twelve_months ?? null,
            'current_acquire_psp' => $this->current_acquire_psp ?? null,
            'ac_current_acquire_psp' => $this->ac_current_acquire_psp ?? null,
            'ac_alternative_payment_methods' => $this->ac_alternative_payment_methods ?? null,
            'ac_method_currently_offered' => $this->ac_method_currently_offered ?? null,
            'alternative_payment_methods' => LookupableResource::collection($this->alternativePaymentMethods),
            'method_currently_offered' => LookupableResource::collection($this->methodCurrentlyOffered),
        ];
    }
}
