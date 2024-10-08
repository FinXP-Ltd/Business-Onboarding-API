<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IBAN4UPaymentAccountResource extends JsonResource
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
            'share_capital' => $this->share_capital,
            'annual_turnover' => $this->annual_turnover,
            'deposit' => [
                'trading' => LookupableResource::collection($this->depositTrading),
                'countries' => LookupableResource::collection($this->depositCountries),
                'approximate_per_month' => $this->deposit_approximate_per_month,
                'cumulative_per_month' => $this->deposit_cumulative_per_month,
            ],
            'withdrawal' => [
                'trading' => LookupableResource::collection($this->withdrawalTrading),
                'countries' => LookupableResource::collection($this->withdrawalCountries),
                'approximate_per_month' => $this->withdrawal_approximate_per_month,
                'cumulative_per_month' => $this->withdrawal_cumulative_per_month,
            ],
            'activity' => [
                'incoming_payments' => $this->payments->where('type','incoming')->toArray(),
                'outgoing_payments' => $this->payments->where('type','outgoing')->toArray(),
                'held_accounts' => $this->held_accounts,
                'held_accounts_description' => $this->held_accounts_description,
                'refused_banking_relationship' => $this->refused_banking_relationship,
                'refused_banking_relationship_description' => $this->refused_banking_relationship_description,
                'terminated_banking_relationship' => $this->terminated_banking_relationship,
                'terminated_banking_relationship_description' => $this->terminated_banking_relationship_description,
            ],
            'purpose_of_account_opening' => $this->purpose_of_account_opening,
            'partners_incoming_transactions' => $this->partners_incoming_transactions,
            'partners_outgoing_transactions' => $this->partners_outgoing_transactions,
            'country_origin' => LookupableResource::collection($this->countryOrigin),
            'country_remittance' => LookupableResource::collection($this->countryRemittance),
            'estimated_monthly_transactions' => $this->estimated_monthly_transactions,
            'average_amount_transaction_euro' => $this->average_amount_transaction_euro,
            'accepting_third_party_funds' => $this->accepting_third_party_funds,
        ];
    }
}
