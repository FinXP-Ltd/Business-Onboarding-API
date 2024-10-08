<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UsaTaxLiability;

class CompanyDefaultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $tax = UsaTaxLiability::where('company_information_id', $this->id)->value('tax_name');

        return [
            //company products
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),

            //company info
            'company_details' => [
                'name' => $this->company_name ?? null,
                'registration_number' => $this->registration_number ?? null,
                'registration_type' => $this->type_of_company ?? null,
                'trading_name' => $this->company_trading_as ?? null,
                'foundation_date' => $this->date_of_incorporation ?? null,
                'tax_country' => $this->country_of_incorporation ?? null,
                'number_employees' => $this->number_of_employees ?? null,
                'number_of_years' => $this->number_of_years ?? null,
                'vat_number' => $this->vat_number ?? null,
                'tax_identification_number' => $this->tin ?? null,
                'tin_jurisdiction' => $this->tin_jurisdiction ?? null,
                'industry_type' => $this->industry_type ?? null,
                'business_activity_description' => $this->business_activity_description ?? null,
                // 'industry_description' => $this->industry_description ?? null,
                'share_capital' => $this->share_capital ?? null,
                'previous_year_turnover' => $this->previous_year_turnover ?? null,
                'email' => $this->email ?? null,
                'website' => $this->website ?? null,
                'additional_website' => $this->additional_website ?? null,
                "is_group_corporate" =>  $this->is_group_corporate ?? null,
                "parent_holding_company" => $this->parent_holding_company ?? null,
                "parent_holding_company_other" => $this->parent_holding_company_other ?? null,
                "has_fiduciary_capacity" =>  $this->company_fiduciary_capacity ?? null,
                "has_constituting_documents" => $this->allow_constituting_documents ?? null,
                "is_company_licensed" =>  $this->is_company_licensed  ?? null,
                "license_rep_juris" => $this->licensed_in ?? null,
                "contact_person_name" => $this->full_name ?? null,
                "contact_person_email" =>$this->email_address ?? null,
            ],

            //company address
            'registered_address' => [
                'registered_street_number' => $this->companyAddress?->registered_street_number ?? null,
                'registered_street_name' => $this->companyAddress?->registered_street_name ?? null,
                'registered_postal_code' => $this->companyAddress?->registered_postal_code ?? null,
                'registered_city' => $this->companyAddress?->registered_city ?? null,
                'registered_country' => $this->companyAddress?->registered_country ?? null,
            ],
            'operational_address' => [
                'operational_street_number' => $this->companyAddress?->operational_street_number ?? null,
                'operational_street_name' => $this->companyAddress?->operational_street_name ?? null,
                'operational_postal_code' => $this->companyAddress?->operational_postal_code ?? null,
                'operational_city' => $this->companyAddress?->operational_city ?? null,
                'operational_country' => $this->companyAddress?->operational_country ?? null,
            ],
            'is_same_address' => $this->companyAddress?->is_same_address,
             //company sources
            'company_sources' => [
                'source_of_wealth' => $this->companySource->where('type', 'wealth')->where('is_selected', true)->pluck('source_name')->toArray(),
                'source_of_wealth_other' => $this->companySource->where('type', 'wealth')->where('source_name', 'Other')->first()->other_value ?? null,
                'source_of_funds' => $this->source_of_funds ?? null,
                'country_source_of_funds' => $this->companySourceCountry->where('type', 'fund')->where('is_selected', true)->pluck('country')->toArray(),
                'country_source_of_wealth' => $this->companySourceCountry->where('type', 'wealth')->where('is_selected', true)->pluck('country')->toArray(),
            ],

            //company representative
            'company_representative' => CompanyRepresentativeResource::collection($this->companyRepresentative),
            'senior_management_officer' => SeniorManagementOfficerResource::make($this->seniorManagementOfficer),
            'entities' => $this->business->politicalPersonEntity->map->only(['entity_name', 'is_selected']),
            'indicias' => $this->business->indicias->map->only(['indicia_name', 'is_selected']),
            'tax_name' => $tax,

            //data protection marketing
            'data_protection_marketing' => $this->companyDataProtectionMarketing,

            //declaration
            'declaration_agreement' => [
                'file_name' => $this->companyDeclaration?->file_name ?? null,
                'file_type' => $this->companyDeclaration?->file_type ?? null,
                'size' => $this->companyDeclaration?->size ?? null,
            ],

            //required documents
            'general_documents' => GeneralDocumentsResource::collection($this->generalDocuments)->collection->groupBy('file_type'),
            'iban4u_payment_account_documents' => IBAN4UPaymentAccountDocumentsResource::collection($this->iban4uPaymentAccountDocuments)->collection->groupBy('file_type'),
            'credit_card_processing_documents' => CreditCardProcessingDocumentsResource::collection($this->creditCardProcessingDocuments)->collection->groupBy('file_type'),
            'sepa_direct_debit_documents' => SepaDirectDebitDocumentsResource::collection($this->sepaDirectDebitDocuments)->collection->groupBy('file_type'),
            'additional_documents' => AdditionalDocumentResource::collection($this->additionalDocuments),

            //iban4u
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
                    'trading' => $this->companyIban4u?->withdrawal_type ? explode(' ', $this->companyIban4u->withdrawal_type) : null,
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
            //sepa dd
            'sepa_direct_debit' => [
                'processing_sepa_dd' => $this->CompanySepaDd->processing_sepa_dd ?? null,
                'expected_global_mon_vol' => $this->CompanySepaDd->expected_global_mon_vol ?? null,
                'sepa_dd_products' => $this->CompanySepaDd->sepaProducts,
            ],
            //credit card
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
                'payment_method_currently_offered' => $this->companyCreditCardProcessing->ayment_method_currently_offered ?? null,
                'current_mcc' => $this->companyCreditCardProcessing->current_mcc ?? null,
                'current_descriptor' => $this->companyCreditCardProcessing->current_descriptor ?? null,
                'cb_volumes_twelve_months' => $this->companyCreditCardProcessing->cb_volumes_twelve_months ?? null,
                'sales_volumes_twelve_months' => $this->companyCreditCardProcessing->sales_volumes_twelve_months ?? null,
                'refund_twelve_months' => $this->companyCreditCardProcessing->refund_twelve_months ?? null,
                'current_acquire_psp' => $this->companyCreditCardProcessing->current_acquire_psp ?? null
            ],

            //default
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
