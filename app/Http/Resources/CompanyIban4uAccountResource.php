<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyIban4uAccountResource extends JsonResource
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
            'iban4u_payment_account' => [
                'business_id' => $this->business_id,
                'annual_turnover' => $this->companyIban4u->annual_turnover ?? null,
                'purpose_of_account_opening' => $this->companyIban4u->purpose_of_account_opening ?? null,
                'deposit' => [
                    'trading' => $this->companyIban4u?->deposit_type ? explode(',', $this->companyIban4u->deposit_type) : null,
                    'countries' => $this->companyIban4u?->countries->where('type', 'deposit')->where('is_selected', true)->pluck('country')->toArray() ?? [],
                    'approximate_per_month' => $this->companyIban4u->deposit_approximate_per_month ?? null,
                    'cumulative_per_month' => $this->companyIban4u->deposit_cumulative_per_month ?? null,
                ],
                'withdrawal' => [
                    'trading' => $this->companyIban4u?->withdrawal_type ? explode(',', $this->companyIban4u->withdrawal_type) : null,
                    'countries' => $this->companyIban4u?->countries->where('type', 'withdraw')->where('is_selected', true)->pluck('country')->toArray() ?? [],
                    'approximate_per_month' => $this->companyIban4u->withdrawal_approximate_per_month ?? null,
                    'cumulative_per_month' => $this->companyIban4u->withdrawal_cumulative_per_month ?? null,
                ],
                'activity' => [
                    'incoming_payments' => $this->companyIban4u?->activities->where('type', 'incoming')->toArray() ?? [],
                    'outgoing_payments' => $this->companyIban4u?->activities->where('type', 'outgoing')->toArray() ?? [],
                    'held_accounts' => $this->companyIban4u->held_accounts ?? null,
                    'held_accounts_description' => $this->companyIban4u->held_accounts_description ?? null,
                    'refused_banking_relationship' => $this->companyIban4u->refused_banking_relationship ?? null,
                    'refused_banking_relationship_description' => $this->companyIban4u->refused_banking_relationship_description ?? null,
                    'terminated_banking_relationship' => $this->companyIban4u->terminated_banking_relationship ?? null,
                    'terminated_banking_relationship_description' => $this->companyIban4u->terminated_banking_relationship_description ?? null,
                ]
            ],
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
