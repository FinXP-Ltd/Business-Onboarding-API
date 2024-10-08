<?php

namespace App\Http\Requests;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\TaxInformation;
use App\Rules\ValidPaymentConfirmation;
use App\Services\LocalUser\Facades\LocalUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Validator;
use App\Traits\QueryParamsValidator;
use App\Rules\ValidRequestKeys;

class BusinessOnboardingRequest extends FormRequest
{
    use QueryParamsValidator;

    const ISO_COUNTRY = ['max:3', 'string'];
    const PHONE = 'max:15';
    const INT = 'digits_between:0,11';
    const REQUIRED_ISO_COUNTRY = ['required','max:3', 'string'];
    const REGISTRATION_NUMBER = 'tax_information.registration_number';

    public function __construct(private User $user) {}

    protected function prepareForValidation()
    {
        $this->user = LocalUser::createOrFetchLocalUser();
    }

    protected function passedValidation()
    {
        // Add the `user` property containing the local user's `id`
        // to the request input data once validation succeeds.
        $this->merge([ 'user' => $this->user->id ]);
    }

    public function rules()
    {
        $corporate_saving = $this->request->has('corporate_saving');

        $finxp_products = $this->input('business_details.finxp_products') ?? [];
        $iban4UPayload = $corporate_saving ? false : in_array(BUSINESS::IBAN4U, $finxp_products);
        $creditCardProcessingPayload = $corporate_saving ? false :  in_array(BUSINESS::CC_PROCESSING, $finxp_products);
        $isBetterPaymentProgram = $this->user?->program_id === (int)config('kycp.program_id.better_payment');

        Validator::extend('uniqueNameAndRegNumber', function () {
            $businessName = $this->input('name');
            $regNumber = $this->input(self::REGISTRATION_NUMBER);

            $taxInfo = TaxInformation::where([
                ['name', 'like', "%{$businessName}%"],
                ['registration_number', 'like', "%{$regNumber}%"],
            ]);

            $method = $this->method();

            if(in_array($method, [Request::METHOD_PUT, Request::METHOD_PATCH])
                && ($taxInfo->first()?->id)) {
                return true;
            }

            return $taxInfo->count() === 0;
        }, 'Business Name with Registration Number already exist');

        Validator::extend('uniqueRegNumber', function () {
            $regNumber = $this->input(self::REGISTRATION_NUMBER);

            $taxInfo = TaxInformation::where([
                ['registration_number', 'like', "%{$regNumber}%"],
            ]);

            $method = $this->method();

            if(in_array($method, [Request::METHOD_PUT, Request::METHOD_PATCH])
                && ($taxInfo->first()?->id)) {
                return true;
            }

            return $taxInfo->count() === 0;
        }, 'Registration Number already exist');

        return [
            "name" => 'required|uniqueNameAndRegNumber',
            "tax_information" => ['array', new ValidRequestKeys([
                'tax_country', 'registration_number', 'registration_type', 'tax_identification_number'
            ])],
            'external_identifier' => 'string',
            "IBAN" => 'string',
            "tax_information.tax_country" => [Rule::requiredIf(!$corporate_saving), self::REQUIRED_ISO_COUNTRY],
            self::REGISTRATION_NUMBER => $corporate_saving ? 'nullable' : ['max:20', 'required_unless:tax_information.tax_country,DEU','uniqueNameAndRegNumber', 'uniqueRegNumber'],
            "tax_information.registration_type" => [Rule::in(Business::REGISTRATION_TYPE)],
            "tax_information.tax_identification_number" => ['max:20'],
            "vat_number" => $corporate_saving ? 'nullable' : ['min:4', self::PHONE, 'required_unless:tax_information.tax_country,DEU'],
            "foundation_date" => [Rule::requiredIf(!$corporate_saving), 'date'],
            "telephone" => ['min:11', self::PHONE],
            "email" => 'email',
            "website" => 'url',
            "additional_website" => 'url',
            "registered_address" => ['array', new ValidRequestKeys([
                'line_1', 'line_2', 'city', 'postal_code', 'country'
            ])],
            "registered_address.line_1" => [Rule::requiredIf(!$corporate_saving)],
            "registered_address.line_2" => 'string',
            "registered_address.city" => [Rule::requiredIf(!$corporate_saving)],
            "registered_address.postal_code" => [Rule::requiredIf(!$corporate_saving)],
            "registered_address.country" => [Rule::requiredIf(!$corporate_saving), self::REQUIRED_ISO_COUNTRY],
            "operational_address" => ['array', new ValidRequestKeys([
                'line_1', 'line_2','city', 'postal_code', 'country'
            ])],
            "operational_address.line_1" => [Rule::requiredIf(!$corporate_saving)],
            "operational_address.line_2" => 'string',
            "operational_address.city" => [Rule::requiredIf(!$corporate_saving)],
            "operational_address.postal_code" => [Rule::requiredIf(!$corporate_saving)],
            "operational_address.country" => [Rule::requiredIf(!$corporate_saving), self::REQUIRED_ISO_COUNTRY],
            "contact_details" => ['required', 'array', new ValidRequestKeys([
                'first_name', 'last_name', 'position_held', 'country_code', 'mobile_no', 'email'
            ])],
            "contact_details.first_name" => [Rule::requiredIf(!$corporate_saving)],
            "contact_details.last_name" => [Rule::requiredIf(!$corporate_saving)],
            "contact_details.position_held" => [Rule::requiredIf(!$corporate_saving)],
            "contact_details.country_code" => [Rule::requiredIf(!$corporate_saving)],
            "contact_details.mobile_no" => [Rule::requiredIf(!$corporate_saving), self::PHONE],
            "contact_details.email" => [Rule::requiredIf(!$corporate_saving), 'email'],
            "business_details" => ['required', 'array', new ValidRequestKeys([
                'finxp_products', 'industry_key', 'number_employees', 'number_of_years', 'share_capital', 'number_shareholder', 'number_directors',
                'previous_year_turnover', 'license_rep_juris', 'country_of_license', 'country_juris_dealings', 'business_year_count', 'source_of_funds',
                'terms_and_conditions', 'privacy_accepted', 'mid', 'creditor_identifier', 'business_purpose'
            ])],
            "business_details.finxp_products" => [Rule::requiredIf(!$corporate_saving), 'array'],
            "business_details.industry_key" => [Rule::requiredIf(!$corporate_saving), 'array'],
            "business_details.industry_key.*" => 'string',
            "business_details.business_purpose" => 'string',
            "business_details.number_employees" => self::INT,
            "business_details.number_of_years" => self::INT,
            "business_details.share_capital" => [Rule::requiredIf(!$corporate_saving), 'numeric', 'lte:99999999999'],
            "business_details.number_shareholder" => [Rule::requiredIf(!$corporate_saving), 'gt:0'],
            "business_details.number_directors" => ['numeric', 'gt:0'],
            "business_details.previous_year_turnover" => 'date_format:Y',
            "business_details.license_rep_juris" =>  $corporate_saving ? 'nullable' : [Rule::requiredIf($isBetterPaymentProgram), Rule::in(Business::LICENSE_REP_JURIS)],
            "business_details.country_of_license" => self::REQUIRED_ISO_COUNTRY,
            "business_details.country_juris_dealings.*" => self::ISO_COUNTRY,
            "business_details.country_juris_dealings" => 'array',
            "business_details.business_year_count" => self::INT,
            "business_details.source_of_funds" => 'array',
            "business_details.source_of_funds.*" =>'string',
            "business_details.terms_and_conditions" => 'boolean',
            "business_details.privacy_accepted" => 'boolean',
            "business_details.mid" => ['max:30', 'string'],
            "business_details.creditor_identifier" => ['max:10', 'string'],
            "iban4u_payment_account" => ['array', new ValidRequestKeys([
                'purpose_of_account_opening', 'partners_incoming_transactions', 'country_origin', 'country_remittance', 'estimated_monthly_transactions', 'average_amount_transaction_euro', 'accepting_third_party_funds'
            ])],
            "iban4u_payment_account.purpose_of_account_opening" => 'string',
            "iban4u_payment_account.partners_incoming_transactions" => 'string',
            "iban4u_payment_account.country_origin" => 'array',
            "iban4u_payment_account.country_remittance" => 'array',
            "iban4u_payment_account.country_remittance.*" => self::ISO_COUNTRY,
            "iban4u_payment_account.estimated_monthly_transactions" => self::INT,
            "iban4u_payment_account.average_amount_transaction_euro" => 'numeric',
            "iban4u_payment_account.accepting_third_party_funds" => [Rule::requiredIf($iban4UPayload), Rule::in(ValidPaymentConfirmation::CONFIRM)],
            "sepa_dd_direct_debit" => ['array', new ValidRequestKeys([
                'currently_processing_sepa_dd', 'sepa_dds', 'sepa_dd_volume_per_month'
            ])],
            "sepa_dd_direct_debit.currently_processing_sepa_dd" => 'string',
            "sepa_dd_direct_debit.sepa_dds" => 'array',
            "sepa_dd_direct_debit.sepa_dd_volume_per_month" => 'numeric',
            "credit_card_processing" => ['array', new ValidRequestKeys([
                'currently_processing_cc_payments', 'offer_recurring_billing', 'offer_refunds', 'distribution_sale_volume', 'average_ticket_amount',
                'highest_ticket_amount', 'alternative_payment_methods', 'method_currently_offered', 'cb_volumes_twelve_months', 'cc_volumes_twelve_months',
                'refund_volumes_twelve_months', 'iban4u_processing', 'country', 'trading_urls','recurring_details', 'refund_details', 'processing_account_primary_currency',
                'other_alternative_payment_methods','other_alternative_payment_method_used','current_mcc', 'current_descriptor','current_acquire_psp'
            ])],
            "credit_card_processing.currently_processing_cc_payments" => [Rule::requiredIf($creditCardProcessingPayload), new ValidPaymentConfirmation()],
            "credit_card_processing.offer_recurring_billing" => [Rule::requiredIf($creditCardProcessingPayload), new ValidPaymentConfirmation()],
            "credit_card_processing.offer_refunds" => [Rule::requiredIf($creditCardProcessingPayload), new ValidPaymentConfirmation()],
            "credit_card_processing.country" => self::ISO_COUNTRY,
            "credit_card_processing.trading_urls" => 'string',
            "credit_card_processing.recurring_details" => 'string',
            "credit_card_processing.refund_details" => 'string',
            "credit_card_processing.other_alternative_payment_methods" => 'string',
            "credit_card_processing.other_alternative_payment_method_used" => 'string',
            "credit_card_processing.current_mcc" => 'string',
            "credit_card_processing.current_acquire_psp" => 'string',
            "credit_card_processing.current_descriptor" => 'string',
            "credit_card_processing.processing_account_primary_currency" => 'string',
            "credit_card_processing.distribution_sale_volume" => ['numeric', 'max:100'],
            "credit_card_processing.average_ticket_amount" => 'numeric',
            "credit_card_processing.highest_ticket_amount" => 'numeric',
            "credit_card_processing.alternative_payment_methods" => 'array',
            "credit_card_processing.alternative_payment_methods.*" => Rule::in(Business::PAYMENT_METHOD),
            "credit_card_processing.method_currently_offered" => 'array',
            "credit_card_processing.method_currently_offered.*" => Rule::in(Business::PAYMENT_METHOD),
            "credit_card_processing.cb_volumes_twelve_months" => 'numeric',
            "credit_card_processing.cc_volumes_twelve_months" => 'numeric',
            "credit_card_processing.refund_volumes_twelve_months" => self::INT,
            "credit_card_processing.iban4u_processing" => [new ValidPaymentConfirmation()]
        ];
    }
}
