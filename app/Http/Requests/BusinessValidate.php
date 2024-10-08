<?php

namespace App\Http\Requests;

use App\Enums\Product;
use App\Enums\Section;
use App\Models\Auth\User;
use App\Models\Business;
use App\Models\SeniorManagementOfficer;
use App\Rules\ValidPaymentConfirmation;
use App\Rules\ValidRequestKeys;
use App\Services\LocalUser\Facades\LocalUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\Boolean;
use App\Models\CompanyRepresentative;
use App\Models\RolesPercentOwnership;
use App\Traits\QueryParamsValidator;

class BusinessValidate extends FormRequest
{
    use QueryParamsValidator;

    const ISO_COUNTRY = ['max:3', 'string'];
    const PHONE = 'max:15';

    const MAX_20 = 'max:20';

    const REQUIRED_MAX_40 = 'required|max:40';
    const REQUIRED_MAX_200 = 'required|max:200';
    const INT = 'digits_between:0,11';
    const REQUIRED_ISO_COUNTRY = ['required','max:3', 'string'];
    const ARRAY = 'array|min:1';
    const REQUIRED_STRING = 'required|string';
    const NULLABLE_STRING = 'nullable|string';
    const REQUIRED_NUMBER = 'required|integer';
    const REQUIRED_ARRAY = 'required|array';
    const REQUIRED_INT = 'required|integer';
    const REQUIRED_NUMERIC = 'required|numeric';
    const REQUIRED_UNLESS_DEU = 'required_unless:tax_information.tax_country,DEU';

    const REQUIRED_UNLESS_SHAREHOLDER = 'required_unless:company_representative.*.roles_in_company,Authorised Signatory';
    const REQUIRED_OPTIONS = ['required', 'in:YES,NO'];

    const REQUIRED_TRADING = ['required','array'];
    const REQUIRED_BOOLEAN = 'required|boolean';
    const OPTIONAL_BOOLEAN = 'sometimes|boolean';
    const REQUIRED_DATE = ['required', 'date_format:Y-m-d'];

    const BUSINESS_SOURCE_OF_FUNDS = 'business_details.source_of_funds';
    const IBAN4U_PAYMENT_ACCOUNT = 'iban4u_payment_account';
    const IBAN4U_PAYMENT_ACCOUNT_ACTIVITY = 'iban4u_payment_account.activity.';
    const BUSINESS_INDUSTRY_KEY = 'business_details.industry_key';

    const PRESENT_ARRAY = 'present|array';

    const CHECKBOX = 'required|array|min:1';

    const REQUIRED_FILE = 'file|max:4000';

    const PROCESSING_CC_PAYMENTS = 'credit_card_processing.currently_processing_cc_payments';
    const PROCESSING_SEPADD_PAYMENTS = 'processing_sepa_dd';
    const RECURRING_BILLING = 'credit_card_processing.offer_recurring_billing';

    const ROLES_IN_COMPANY = 'company_representative.*.roles.*.roles_in_company';
    const OFFER_REFUNDS = 'credit_card_processing.offer_refunds';

    const IBAN4U_PRODUCT = 'IBAN4U Payment Account';

    const US_TAX_RESIDENT = 'US Tax Resident';

    const CC_PROCESSING_PRODUCT = 'Credit Card Processing';

    const SEPADD_PRODUCT = 'SEPA Direct Debit';

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
        $business = $this->route('business');

        $businessId = $business->id;
        $companyRepresentativeId = $business?->companyInformation?->companyRepresentative->first()?->id;
        $corporate_saving = $this->input('corporate_saving') ?? false;
        $section = $this->input('section');

        switch ($section) {
            case 'company-products':
                $tab = [
                    'products' => $corporate_saving ? 'nullable' : ['required', 'array'],
                    "products.*" => $corporate_saving ? 'nullable' : [
                        'required',
                        'string',
                        Rule::in(Product::values())
                    ]
                ];
            break;
            case 'company-address':
                $tab = $this->companyAddressRules($corporate_saving);
            break;
            case 'company-details':
                $tab = $this->companyDetailsRules($corporate_saving, $businessId);
            break;
            case 'company-sources':
                $tab = $this->companySourceRules($corporate_saving);
            break;
            case 'company-sepa-dd':
                $tab = $this->sepaDDRules($corporate_saving);
            break;
            case 'data-protection-and-marketing':
                $tab = $this->companyDataProtectionMarketing($corporate_saving);
            break;
            case 'acquiring-services':
                $tab = $this->acquiringServicesRules($corporate_saving, $businessId);
            break;
            case 'iban4u-payment-account':
                $tab = $this->iban4uPaymentAccount($corporate_saving);
            break;
            case 'declaration':
                $tab = $this->declarationRules($corporate_saving);
            break;
            case 'required_documents':
                $tab = $this->requiredDocumentRules($corporate_saving);
            break;
            case 'company_representatives':
                $tab = $this->companyRepresentatives($corporate_saving, $businessId, $companyRepresentativeId);
            break;
            default:
                $tab = $this->defaultRules($corporate_saving);
            break;
        }

        $rules = array_merge($tab, [
            'section' => [
                'required',
                'string',
                Rule::in(Section::values())
            ],
            'corporate_saving' => self::OPTIONAL_BOOLEAN,
            'disabled' => self::OPTIONAL_BOOLEAN
        ]);

        if (!in_array('product', array_keys($tab))) {
            $rules = array_merge($rules, [
                'products' => 'sometimes|array',
                'products.*' => $corporate_saving
                    ? 'nullable'
                    : [
                        'sometimes',
                        Rule::in(Product::values())
                    ]
                ]);
        }

        return $rules;
    }
    function defaultRules($corporate_saving)
    {
        $finxp_products = $this->input('business_details.finxp_products') ?? [];
        $iban4UPayload = $corporate_saving ? false : in_array(BUSINESS::IBAN4U, $finxp_products);
        $creditCardProcessingPayload = $corporate_saving ? false :  in_array(BUSINESS::CC_PROCESSING, $finxp_products);
        $sepaDD = $corporate_saving ? false : in_array(BUSINESS::SEPADD, $finxp_products);
        $isBetterPaymentProgram = $this->user?->program_id === (int)config('kycp.program_id.better_payment');

        return [
            "name" => 'required',
            'tax_information' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'tax_country',
                    'registration_number',
                    'registration_type',
                    'tax_identification_number'
                ])
            ],
            "tax_information.tax_country" => self::REQUIRED_ISO_COUNTRY,
            "tax_information.registration_number" => [self::MAX_20, self::REQUIRED_UNLESS_DEU],
            "tax_information.registration_type" => [Rule::in(Business::REGISTRATION_TYPE)],
            "tax_information.tax_identification_number" => self::MAX_20,
            "vat_number" => ['min:4', self::PHONE, self::REQUIRED_UNLESS_DEU],
            "foundation_date" => ['required', 'date'],
            "telephone" => ['min:11', self::PHONE],
            "email" => 'email',
            "website" => 'url',
            'registered_address' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'line_1',
                    'city',
                    'postal_code',
                    'country'
                ])
            ],
            "registered_address.line_1" => 'required',
            "registered_address.city" => 'required',
            "registered_address.postal_code" => 'required',
            "registered_address.country" => self::REQUIRED_ISO_COUNTRY,
            'contact_details' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'first_name',
                    'last_name',
                    'position_held',
                    'country_code',
                    'mobile_no',
                    'email'
                ])
            ],
            "contact_details.first_name" => 'required',
            "contact_details.last_name" => 'required',
            "contact_details.position_held" => 'required',
            "contact_details.country_code" => 'string',
            "contact_details.mobile_no" => ['required', self::PHONE],
            "contact_details.email" => ['required', 'email'],
            'business_details' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'finxp_products',
                    'industry_key',
                    'number_employees',
                    'share_capital',
                    'number_shareholder',
                    'number_directors',
                    'previous_year_turnover',
                    'license_rep_juris',
                    'country_of_license',
                    'country_juris_dealings',
                    'business_year_count',
                    'source_of_funds',
                    'terms_and_conditions',
                    'privacy_accepted',
                    'mid',
                    'creditor_identifier'
                ])
            ],
            "business_details.finxp_products" => ['required', 'array'],
            self::BUSINESS_INDUSTRY_KEY => ['required', 'array'],
            "business_details.industry_key.*" => 'alpha_dash',
            "business_details.number_employees" => self::INT,
            "business_details.number_of_years" => self::INT,
            "business_details.share_capital" => ['required', 'numeric', 'lte:99999999999'],
            "business_details.number_shareholder" => ['required', 'gt:0'],
            "business_details.number_directors" => ['numeric', 'gt:0'],
            "business_details.previous_year_turnover" => 'date_format:Y',
            "business_details.license_rep_juris" =>  [Rule::requiredIf($isBetterPaymentProgram), Rule::in(Business::LICENSE_REP_JURIS)],
            "business_details.country_of_license" => self::ISO_COUNTRY,
            "business_details.country_juris_dealings" => 'array',
            "business_details.country_juris_dealings.*" => self::ISO_COUNTRY,
            "business_details.business_year_count" => self::INT,
            self::BUSINESS_SOURCE_OF_FUNDS => 'array',
            "business_details.source_of_funds.*" => 'string',
            "business_details.terms_and_conditions" => 'boolean',
            "business_details.privacy_accepted" => 'boolean',
            "business_details.mid" => ['max:30', 'string'],
            "business_details.creditor_identifier" => ['max:10', 'string'],
            'iban4u_payment_account' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'country_origin',
                    'country_remittance',
                    'estimated_monthly_transactions',
                    'average_amount_transaction_euro',
                    'accepting_third_party_funds',
                    'annual_turnover',
                    'deposit',
                    'withdrawal',
                    'activity'
                ])
            ],
            "iban4u_payment_account.country_origin" => 'array',
            "iban4u_payment_account.country_origin.*" => self::ISO_COUNTRY,
            'iban4u_payment_ac' => [
                'required',
                'array',
                new ValidRequestKeys(['country_remittance'])
            ],
            "iban4u_payment_ac.country_remittance" => 'array',
            "iban4u_payment_ac.country_remittance.*" => self::ISO_COUNTRY,
            "iban4u_payment_account.estimated_monthly_transactions" => self::INT,
            "iban4u_payment_account.average_amount_transaction_euro" => 'numeric',
            "iban4u_payment_account.accepting_third_party_funds" => [Rule::requiredIf($iban4UPayload), Rule::in(ValidPaymentConfirmation::CONFIRM)],
            "iban4u_payment_account.annual_turnover" => self::REQUIRED_NUMBER,
            'iban4u_payment_account.deposit' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'trading',
                    'countries',
                    'approximate_per_month',
                    'cumulative_per_month'
                ])
            ],
            "iban4u_payment_account.deposit.trading" => self::REQUIRED_TRADING,
            "iban4u_payment_account.deposit.countries" => self::REQUIRED_STRING,
            "iban4u_payment_account.deposit.approximate_per_month" => self::REQUIRED_STRING,
            "iban4u_payment_account.deposit.cumulative_per_month" => self::REQUIRED_STRING,
            'iban4u_payment_account.withdrawal' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'trading',
                    'countries',
                    'approximate_per_month',
                    'cumulative_per_month'
                ])
            ],
            "iban4u_payment_account.withdrawal.trading" =>self::REQUIRED_TRADING,
            "iban4u_payment_account.withdrawal.countries" =>  self::REQUIRED_STRING,
            "iban4u_payment_account.withdrawal.approximate_per_month" => self::REQUIRED_STRING,
            "iban4u_payment_account.withdrawal.cumulative_per_month" => self::REQUIRED_STRING,
            'iban4u_payment_account.activity' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'incoming_payments',
                    'outgoing_payments',
                    'held_accounts',
                    'refused_banking_relationship',
                    'terminated_banking_relationship'
                ])
            ],
            "iban4u_payment_account.activity.incoming_payments" => self::PRESENT_ARRAY,
            "iban4u_payment_account.activity.outgoing_payments" => self::PRESENT_ARRAY,
            "iban4u_payment_account.activity.held_accounts" => self::REQUIRED_STRING,
            "iban4u_payment_account.activity.refused_banking_relationship" =>self::REQUIRED_STRING,
            "iban4u_payment_account.activity.terminated_banking_relationship" =>  self::REQUIRED_STRING,
            self::PROCESSING_SEPADD_PAYMENTS => self::REQUIRED_STRING,
            'sepa_dd_direct_debit' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'sepa_dds',
                    'sepa_dd_volume_per_month'
                ])
            ],
            "sepa_dd_direct_debit.sepa_dds" => 'array',
            "sepa_dd_direct_debit.sepa_dd_volume_per_month" => 'numeric',
            'credit_card_processing' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'currently_processing_cc_payments',
                    'offer_recurring_billing',
                    'offer_refunds',
                    'country',
                    'distribution_sale_volume',
                    'average_ticket_amount',
                    'highest_ticket_amount',
                    'alternative_payment_methods',
                    'method_currently_offered',
                    'cb_volumes_twelve_months',
                    'refund_volumes_twelve_months',
                    'iban4u_processing'
                ])
            ],
            self::PROCESSING_CC_PAYMENTS => [Rule::requiredIf($creditCardProcessingPayload), new ValidPaymentConfirmation()],
            self::RECURRING_BILLING => [Rule::requiredIf($creditCardProcessingPayload), new ValidPaymentConfirmation()],
            self::OFFER_REFUNDS => [Rule::requiredIf($creditCardProcessingPayload), new ValidPaymentConfirmation()],
            "credit_card_processing.country" => self::ISO_COUNTRY,
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
            "credit_card_processing.iban4u_processing" => [new ValidPaymentConfirmation()],
            'data_protection_marketing' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'data_protection_notice',
                    'receive_messages_from_finxp',
                    'receive_market_research_survey'
                ])
            ],
            "data_protection_marketing.data_protection_notice" => ['required',new Boolean()],
            "data_protection_marketing.receive_messages_from_finxp" => self::REQUIRED_OPTIONS,
            "data_protection_marketing.receive_market_research_survey" => self::REQUIRED_OPTIONS,
            'declaration' => [
                'required',
                'array',
                new ValidRequestKeys(['file_name'])
            ],
            'declaration_agreement.file_name' => 'required',
        ];
    }

    function acquiringServicesRules($corporate_saving, $businessId)
    {
        // Helper function to choose the rule based on $corporate_saving
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        return [
            'trading_urls' => $ruleOrNullable('sometimes|array'),
            'trading_urls.*' => 'string',
            'credit_card_processing' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'currently_processing_cc_payments',
                    'offer_recurring_billing',
                    'offer_refunds',
                    'frequency_offer_billing',
                    'if_other_offer_billing',
                    'frequency_offer_refunds',
                    'if_other_offer_refunds',
                    'trading_urls',
                    'countries',
                    'processing_account_primary_currency',
                    'average_ticket_amount',
                    'highest_ticket_amount',
                    'alternative_payment_methods',
                    'method_currently_offered',
                    'current_mcc',
                    'current_descriptor',
                    'current_acquire_psp',
                    'cb_volumes_twelve_months',
                    'sales_volumes_twelve_months',
                    'refund_twelve_months',
                    'payment_method_currently_offered'
                ])
            ],
            self::PROCESSING_CC_PAYMENTS => $ruleOrNullable(self::REQUIRED_OPTIONS),
            self::RECURRING_BILLING=> $ruleOrNullable(self::REQUIRED_OPTIONS),
            self::OFFER_REFUNDS => $ruleOrNullable(self::REQUIRED_OPTIONS),
            "credit_card_processing.frequency_offer_billing" => [
                $ruleOrNullable(
                    Rule::requiredIf($this->input(self::RECURRING_BILLING) == "YES"),
                    Rule::in(Business::FREQUENCY)
                ),
            ],
            "credit_card_processing.if_other_offer_billing" => $ruleOrNullable([Rule::requiredIf($this->input('credit_card_processing.frequency_offer_billing') == "OTHER")]),
            "credit_card_processing.frequency_offer_refunds" => [
                $ruleOrNullable(
                    Rule::requiredIf($this->input(self::OFFER_REFUNDS) == "YES"),
                    Rule::in(Business::FREQUENCY)
                ),
            ],
            "credit_card_processing.if_other_offer_refunds" => $ruleOrNullable([Rule::requiredIf($this->input('credit_card_processing.frequency_offer_refunds') == "OTHER")]),
            "credit_card_processing.trading_urls" => $ruleOrNullable(self::REQUIRED_ARRAY),
            "credit_card_processing.trading_urls.*" => $ruleOrNullable(self::REQUIRED_STRING),
            "credit_card_processing.countries" => $ruleOrNullable(self::REQUIRED_ARRAY),
            'credit_card_processing.countries.*' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'countries_where_product_offered',
                    'distribution_per_country'
                ])
            ],
            "credit_card_processing.countries.*.countries_where_product_offered" => $ruleOrNullable(self::REQUIRED_ISO_COUNTRY),
            "credit_card_processing.countries.*.distribution_per_country" => $ruleOrNullable(self::REQUIRED_INT),
            "credit_card_processing.processing_account_primary_currency" => $ruleOrNullable(self::REQUIRED_STRING),
            "credit_card_processing.average_ticket_amount" => $ruleOrNullable(self::REQUIRED_STRING),
            "credit_card_processing.highest_ticket_amount" => $ruleOrNullable(self::REQUIRED_STRING),
            "credit_card_processing.alternative_payment_methods" => $ruleOrNullable(self::NULLABLE_STRING),
            'credit_card_processing.payment_method_currently_offered' => $ruleOrNullable(self::NULLABLE_STRING),
            "credit_card_processing.method_currently_offered" => $ruleOrNullable(self::NULLABLE_STRING),
            "credit_card_processing.current_mcc" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "credit_card_processing.current_descriptor" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "credit_card_processing.current_acquire_psp" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "credit_card_processing.cb_volumes_twelve_months" => $ruleOrNullable(self::REQUIRED_NUMERIC),
            "credit_card_processing.sales_volumes_twelve_months" => $ruleOrNullable(self::REQUIRED_NUMERIC),
            "credit_card_processing.refund_twelve_months" => $ruleOrNullable(self::REQUIRED_NUMERIC)
        ];
    }


    function companyDetailsRules($corporate_saving, $businessId)
    {
        // Helper function to choose the rule based on $corporate_saving
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        return [
            "name" => $ruleOrNullable(self::REQUIRED_STRING),
            "registration_number" => $ruleOrNullable([self::MAX_20, self::REQUIRED_UNLESS_DEU]),
            "registration_type" => $ruleOrNullable([Rule::in(Business::AC_REGISTRATION_TYPE)]),
            'trading_name' => $ruleOrNullable(self::REQUIRED_STRING),
            "foundation_date" => $ruleOrNullable(['required', 'date_format:Y-m-d']),
            "tax_country" => $ruleOrNullable(self::REQUIRED_STRING),
            "number_employees" => $ruleOrNullable(['required', self::INT]),
            "number_of_years" => $ruleOrNullable(['required', self::INT]),
            "vat_number" => self::PHONE,
            "tax_identification_number" => $corporate_saving ? 'nullable' : self::MAX_20,
            'jurisdiction' =>  $corporate_saving ? 'nullable' : self::REQUIRED_ISO_COUNTRY,
            "industry_key" => $ruleOrNullable(self::REQUIRED_STRING),

            'business_activity_description' => $ruleOrNullable(self::REQUIRED_MAX_200),
            "share_capital" => $ruleOrNullable(['required', 'numeric', 'lte:99999999999']),
            "previous_year_turnover" => $ruleOrNullable(self::REQUIRED_STRING),
            "email" => 'nullable|email',
            "website" => 'nullable|regex:/((https?):\/\/)?(www.)?[a-z0-9]+(\.[a-z]{2,}){1,3}(#?\/?[a-zA-Z0-9#]+)*\/?(\?[a-zA-Z0-9-_]+=[a-zA-Z0-9-%]+&?)?$/',
            "additional_website" => 'nullable|regex:/((https?):\/\/)?(www.)?[a-z0-9]+(\.[a-z]{2,}){1,3}(#?\/?[a-zA-Z0-9#]+)*\/?(\?[a-zA-Z0-9-_]+=[a-zA-Z0-9-%]+&?)?$/',
            'is_part_of_group' => $ruleOrNullable([
                'required',
                'string',
                Rule::in(['YES', 'NO'])
            ]),
            "parent_holding_company" => [Rule::requiredIf($this->input('business_details.is_part_of_group') == 'YES'), 'nullable', 'string'],
            "parent_holding_company_other" => [Rule::requiredIf($this->input('business_details.parent_holding_company') == 'Other'), 'nullable', 'string'],
            "has_fiduciary_capacity" =>  $ruleOrNullable(self::REQUIRED_STRING),
            "has_constituting_documents" => $ruleOrNullable(self::REQUIRED_STRING),
            "is_company_licensed" =>  $ruleOrNullable(self::REQUIRED_STRING),
            "license_rep_juris" => $ruleOrNullable(Rule::requiredIf($this->input('is_company_licensed') == 'YES')),
            "contact_person_name" => $ruleOrNullable(self::REQUIRED_STRING),
            "contact_person_email" => $ruleOrNullable('required|email'),
        ];
    }

    function iban4uPaymentAccount($corporate_saving)
    {
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        return [
            'iban4u_payment_account' => [
                'required',
                'array',
                new ValidRequestKeys([
                    'annual_turnover',
                    'trading',
                    'countries',
                    'approximate_per_month',
                    'cumulative_per_month',
                    'purpose_of_account_opening',
                    'deposit',
                    'withdrawal',
                    'activity'
                ])
            ],
            "iban4u_payment_account.annual_turnover" => $ruleOrNullable(self::REQUIRED_NUMBER),
            'iban4u_payment_account.purpose_of_account_opening' => $ruleOrNullable(self::REQUIRED_STRING),
            'iban4u_payment_account.deposit' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'trading',
                    'countries',
                    'approximate_per_month',
                    'cumulative_per_month'
                ])
            ],
            "iban4u_payment_account.deposit.trading" => $ruleOrNullable(self::REQUIRED_TRADING),
            "iban4u_payment_account.deposit.countries" => $ruleOrNullable(self::REQUIRED_ARRAY),
            "iban4u_payment_account.deposit.countries.*" => $ruleOrNullable(self::REQUIRED_STRING),
            "iban4u_payment_account.deposit.approximate_per_month" => $ruleOrNullable(self::REQUIRED_STRING),
            "iban4u_payment_account.deposit.cumulative_per_month" => $ruleOrNullable(self::REQUIRED_STRING),

            'iban4u_payment_account.withdrawal' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'trading',
                    'countries',
                    'countries',
                    'approximate_per_month',
                    'cumulative_per_month'
                ])
            ],
            "iban4u_payment_account.withdrawal.trading" => $ruleOrNullable(self::REQUIRED_TRADING),
            "iban4u_payment_account.withdrawal.countries" => $ruleOrNullable(self::REQUIRED_ARRAY),
            "iban4u_payment_account.withdrawal.countries.*" => $ruleOrNullable(self::REQUIRED_STRING),
            "iban4u_payment_account.withdrawal.approximate_per_month" => $ruleOrNullable(self::REQUIRED_STRING),
            "iban4u_payment_account.withdrawal.cumulative_per_month" => $ruleOrNullable(self::REQUIRED_STRING),

            'iban4u_payment_account.activity' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'incoming_payments',
                    'outgoing_payments',
                    'held_accounts',
                    'held_accounts_description',
                    'refused_banking_relationship',
                    'refused_banking_relationship_description',
                    'terminated_banking_relationship',
                    'terminated_banking_relationship_description'
                ])
            ],
            self::IBAN4U_PAYMENT_ACCOUNT_ACTIVITY."incoming_payments" => $ruleOrNullable(self::PRESENT_ARRAY),
            self::IBAN4U_PAYMENT_ACCOUNT_ACTIVITY."outgoing_payments" => $ruleOrNullable(self::PRESENT_ARRAY),
            self::IBAN4U_PAYMENT_ACCOUNT_ACTIVITY."held_accounts" => $ruleOrNullable(self::REQUIRED_STRING),
            self::IBAN4U_PAYMENT_ACCOUNT_ACTIVITY."held_accounts_description" => $ruleOrNullable(Rule::requiredIf($this->input('iban4u_payment_account.activity.held_accounts') == 'YES')),
            self::IBAN4U_PAYMENT_ACCOUNT_ACTIVITY."refused_banking_relationship" => $ruleOrNullable(self::REQUIRED_STRING),
            self::IBAN4U_PAYMENT_ACCOUNT_ACTIVITY."refused_banking_relationship_description" => $ruleOrNullable(Rule::requiredIf($this->input('iban4u_payment_account.activity.refused_banking_relationship') == 'YES')),
            self::IBAN4U_PAYMENT_ACCOUNT_ACTIVITY."terminated_banking_relationship" => $ruleOrNullable(self::REQUIRED_STRING),
            self::IBAN4U_PAYMENT_ACCOUNT_ACTIVITY."terminated_banking_relationship_description" => $ruleOrNullable(Rule::requiredIf($this->input('iban4u_payment_account.activity.terminated_banking_relationship') == 'YES'))
        ];
    }

    function companyAddressRules($corporate_saving)
    {
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        return [
            'registered_address' => $corporate_saving
                ? 'nullable'
                : [
                    'required',
                    'array',
                    new ValidRequestKeys([
                        'registered_street_number',
                        'registered_street_name',
                        'registered_postal_code',
                        'registered_city',
                        'registered_country'
                    ])
                ],
            "registered_address.registered_street_number" => $ruleOrNullable(self::REQUIRED_STRING),
            "registered_address.registered_street_name" => $ruleOrNullable(self::REQUIRED_STRING),
            "registered_address.registered_postal_code" => $ruleOrNullable(self::REQUIRED_STRING),
            "registered_address.registered_country" => $ruleOrNullable(self::REQUIRED_ISO_COUNTRY),
            'registered_address.registered_city' => $ruleOrNullable(self::REQUIRED_STRING),
            'operational_address' => $corporate_saving
                ? 'nullable'
                : [
                    'required',
                    'array',
                    new ValidRequestKeys([
                        'operational_street_number',
                        'operational_street_name',
                        'operational_postal_code',
                        'operational_city',
                        'operational_country'
                    ])
                ],
            'operational_address.operational_street_number' => $ruleOrNullable(self::REQUIRED_STRING),
            'operational_address.operational_street_name' => $ruleOrNullable(self::REQUIRED_STRING),
            'operational_address.operational_postal_code' => $ruleOrNullable(self::REQUIRED_STRING),
            'operational_address.operational_country' => $ruleOrNullable(self::REQUIRED_ISO_COUNTRY),
            'operational_address.operational_city' => $ruleOrNullable(self::REQUIRED_STRING),
            'is_same_address' => self::OPTIONAL_BOOLEAN
        ];
    }

    function companyDataProtectionMarketing($corporate_saving)
    {
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        return [
            'data_protection_marketing' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'data_protection_notice',
                    'receive_messages_from_finxp',
                    'receive_market_research_survey'
                ])
            ],
            "data_protection_marketing.data_protection_notice" => $ruleOrNullable(['required', new Boolean()]),
            "data_protection_marketing.receive_messages_from_finxp" => $ruleOrNullable(self::REQUIRED_OPTIONS),
            "data_protection_marketing.receive_market_research_survey" => $ruleOrNullable(self::REQUIRED_OPTIONS)
        ];
    }

    function declarationRules($corporate_saving)
    {

        return [
            'file' =>  $corporate_saving ? 'nullable' : self::REQUIRED_FILE
        ];
    }

    function requiredDocumentRules($corporate_saving)
    {
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        return [
            'general_documents-memorandum_and_articles_of_association_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'general_documents-certificate_of_incorporation_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'general_documents-registry_exact_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'general_documents-company_structure_chart_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'general_documents-proof_of_address_document_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'general_documents-operating_license_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'iban4u_payment_account_documents-agreements_with_the_entities_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'iban4u_payment_account_documents-board_resolution_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'iban4u_payment_account_documents-third_party_questionnaire_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'credit_card_processing_documents-proof_of_ownership_of_the_domain_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'credit_card_processing_documents-processing_history_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'credit_card_processing_documents-cc_copy_of_bank_settlement_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'credit_card_processing_documents-company_pci_certificate_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'sepa_direct_debit_documents-template_of_customer_mandate_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'sepa_direct_debit_documents-processing_history_with_chargeback_and_ratios_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'sepa_direct_debit_documents-sepa_copy_of_bank_settlement_file' => $ruleOrNullable(self::REQUIRED_FILE),
            'sepa_direct_debit_documents-product_marketing_information_file' => $ruleOrNullable(self::REQUIRED_FILE),
        ];
    }

    function companyRepresentatives($corporate_saving, $businessId, $companyRepresentativeId)
    {
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        $role = RolesPercentOwnership::where('company_representative_id', $companyRepresentativeId)->get()->map(function ($companyRepresentative) {
            return $companyRepresentative;
        })->flatten()->pluck('roles_in_company')->toArray();


        return [
            'tax_name' => $ruleOrNullable(self::NULLABLE_STRING),
            "company_representative" => $ruleOrNullable(self::REQUIRED_ARRAY),
            'company_representative.*' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'first_name',
                    'middle_name',
                    'surname',
                    'place_of_birth',
                    'date_of_birth',
                    'nationality',
                    'citizenship',
                    'email_address',
                    'phone_number',
                    'phone_code',
                    'roles',
                    'residential_address',
                    'identity_information',
                    'proof_of_address',
                    'proof_of_address_size',
                    'identity_document',
                    'identity_document_size',
                    'identity_document_addt',
                    'identity_document_addt_size',
                    'source_of_wealth',
                    'source_of_wealth_size',
                    'company_representative_document',
                    'document_expired_date',
                    'us_citizenship'
                ])
            ],
            "company_representative.*.first_name" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "company_representative.*.middle_name" => $ruleOrNullable(['max:40']),
            "company_representative.*.surname" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "company_representative.*.place_of_birth" => $ruleOrNullable(self::REQUIRED_STRING),
            "company_representative.*.date_of_birth" => $ruleOrNullable('required', 'date'),

            "company_representative.*.nationality" => $ruleOrNullable(self::REQUIRED_STRING),
            "company_representative.*.citizenship" => $ruleOrNullable(self::REQUIRED_STRING),
            "company_representative.*.email_address" => $ruleOrNullable(['required', 'email']),
            "company_representative.*.phone_number" => $ruleOrNullable(self::REQUIRED_STRING),
            "company_representative.*.phone_code" => $ruleOrNullable(self::REQUIRED_STRING),
            'company_representative.*.document_expired_date' => $ruleOrNullable(self::NULLABLE_STRING),
            'company_representative.*.roles.*' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'roles_in_company',
                    'percent_ownership',
                    'iban4u_rights'
                ])
            ],
            self::ROLES_IN_COMPANY => $ruleOrNullable(Rule::in(CompanyRepresentative::ROLES), self::REQUIRED_STRING),
            "company_representative.*.roles.*.percent_ownership" => $ruleOrNullable([
                Rule::requiredIf(function () use ($role) {
                    return !in_array("UBO", $role) &&
                           !in_array("Shareholder", $role);
                }),
            ]),
            'company_representative.*.roles.*.roles_in_company' => $ruleOrNullable('required|string'),
            "company_representative.*.roles.*.iban4u_rights" => $ruleOrNullable([Rule::requiredIf(!in_array("Authorised Signatory", $role))]),
            'company_representative.*.residential_address' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'street_number',
                    'street_name',
                    'postal_code',
                    'city',
                    'country'
                ])
            ],
            "company_representative.*.residential_address.street_number" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "company_representative.*.residential_address.street_name" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "company_representative.*.residential_address.postal_code" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "company_representative.*.residential_address.city" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "company_representative.*.residential_address.country" => $ruleOrNullable(self::REQUIRED_STRING),
            'company_representative.*.identity_information' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'id_type',
                    'country_of_issue',
                    'id_number',
                    'document_date_issued',
                    'document_expired_date',
                    'high_net_worth',
                    'us_citizenship',
                    'politically_exposed_person'
                ])
            ],
            "company_representative.*.identity_information.id_type" => $ruleOrNullable(self::REQUIRED_STRING),
            "company_representative.*.identity_information.country_of_issue" => $ruleOrNullable(self::REQUIRED_STRING),
            "company_representative.*.identity_information.id_number" => $ruleOrNullable(self::REQUIRED_STRING),
            "company_representative.*.identity_information.document_date_issued" => $ruleOrNullable(self::REQUIRED_DATE),
            "company_representative.*.identity_information.document_expired_date" => $ruleOrNullable(self::REQUIRED_DATE),
            "company_representative.*.identity_information.high_net_worth" => $ruleOrNullable(self::REQUIRED_UNLESS_SHAREHOLDER),
            "company_representative.*.identity_information.us_citizenship" => $ruleOrNullable(self::REQUIRED_UNLESS_SHAREHOLDER),
            "company_representative.*.identity_information.politically_exposed_person" => $ruleOrNullable(self::REQUIRED_UNLESS_SHAREHOLDER),
            "company_representative.*.proof_of_address" => $ruleOrNullable(self::NULLABLE_STRING),
            "company_representative.*.proof_of_address_size" => $ruleOrNullable(self::NULLABLE_STRING),
            "company_representative.*.identity_document" => $ruleOrNullable(self::NULLABLE_STRING),
            "company_representative.*.identity_document_size" => $ruleOrNullable(self::NULLABLE_STRING),
            "company_representative.*.identity_document_addt" => $ruleOrNullable(self::NULLABLE_STRING),
            "company_representative.*.identity_document_addt_size" => $ruleOrNullable(self::NULLABLE_STRING),
            "company_representative.*.source_of_wealth" => $ruleOrNullable(self::NULLABLE_STRING),
            "company_representative.*.source_of_wealth_size" => $ruleOrNullable(self::NULLABLE_STRING),
            'company_representative.*.company_representative_document' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'proof_of_address',
                    'proof_of_address_size',
                    'identity_document',
                    'identity_document_size',
                    'identity_document_addt',
                    'identity_document_addt_size',
                    'source_of_wealth',
                    'source_of_wealth_size'
                ])
            ],
            "company_representative.*.company_representative_document.proof_of_address" => $ruleOrNullable('required'),
            "company_representative.*.company_representative_document.proof_of_address_size" => $ruleOrNullable('required'),
            "company_representative.*.company_representative_document.identity_document" => $ruleOrNullable('required'),
            "company_representative.*.company_representative_document.identity_document_size" => $ruleOrNullable('required'),
            "company_representative.*.company_representative_document.identity_document_addt" => $ruleOrNullable('required'),
            "company_representative.*.company_representative_document.identity_document_addt_size" => $ruleOrNullable('required'),
            "company_representative.*.company_representative_document.source_of_wealth" => [
                function ($value, $fail) {
                    $rolesInCompany = $this->input('roles_in_company');
                    $ownership = $this->input('ownership');
                    if ($rolesInCompany === "UBO" && $ownership >= 25 && empty($value)) {
                        $fail('Source of wealth is required');
                    }
                },
            ],
            "company_representative.*.company_representative_document.source_of_wealth_size" => [
                function ($value, $fail) {
                    $rolesInCompany = $this->input('roles_in_company');
                    $ownership = $this->input('ownership');
                    if ($rolesInCompany === "UBO" && $ownership >= 25 && empty($value)) {
                        $fail('Source of wealth is required');
                    }
                },
            ],
            'senior_management_officer' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'first_name',
                    'middle_name',
                    'place_of_birth',
                    'date_of_birth',
                    'surname',
                    'nationality',
                    'citizenship',
                    'email_address',
                    'phone_number',
                    'phone_code',
                    'roles_in_company',
                    'residential_address',
                    'identity_information',
                    'senior_management_officer_document',
                    'required_indicator'
                ])
            ],
            $this->validateSeniorManagementOfficer($corporate_saving, $businessId)
        ];
    }

    function validateSeniorManagementOfficer($corporate_saving, $businessId)
    {
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        $requiredIndicator = SeniorManagementOfficer::where('company_information_id', $businessId)->get()->value('required_indicator');

        return [
            "senior_management_officer.first_name" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.middle_name" => self::NULLABLE_STRING,
            "senior_management_officer.place_of_birth" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.date_of_birth" => $ruleOrNullable(Rule::when($requiredIndicator === 1, self::REQUIRED_DATE)),
            "senior_management_officer.surname" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.nationality" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.citizenship" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.email_address" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.phone_number" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.phone_code" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.roles_in_company" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            'senior_management_officer.residential_address' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'street_number',
                    'street_name',
                    'postal_code',
                    'city',
                    'country'
                ])
            ],
            "senior_management_officer.residential_address.street_number" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.residential_address.street_name" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.residential_address.postal_code" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.residential_address.city" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.residential_address.country" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            'senior_management_officer.identity_information' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'id_type',
                    'country_of_issue',
                    'id_number',
                    'document_date_issued',
                    'document_expired_date',
                    'high_net_worth',
                    'us_citizenship',
                    'politically_exposed_person'
                ])
            ],
            "senior_management_officer.identity_information.id_type" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.identity_information.country_of_issue" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.identity_information.id_number" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.identity_information.document_date_issued" => $ruleOrNullable(Rule::when($requiredIndicator === 1, self::REQUIRED_DATE)),
            "senior_management_officer.identity_information.document_expired_date" => $ruleOrNullable(Rule::when($requiredIndicator === 1, self::REQUIRED_DATE)),
            "senior_management_officer.identity_information.high_net_worth" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.identity_information.us_citizenship" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.identity_information.politically_exposed_person" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            'senior_management_officer.senior_management_officer_document' => [
                'sometimes',
                'array',
                new ValidRequestKeys([
                    'proof_of_address',
                    'proof_of_address_size',
                    'identity_document',
                    'identity_document_size',
                    'identity_document_addt',
                    'identity_document_addt_size',
                    'document_expired_date'
                ])
            ],
            "senior_management_officer.senior_management_officer_document.proof_of_address" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.senior_management_officer_document.identity_document" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            "senior_management_officer.senior_management_officer_document.identity_document_size" => $ruleOrNullable([Rule::requiredIf($requiredIndicator === 1)]),
            'senior_management_officer.senior_management_officer_document.proof_of_address_size' => $ruleOrNullable(self::NULLABLE_STRING),
            'senior_management_officer.senior_management_officer_document.document_expired_date' => $ruleOrNullable(self::NULLABLE_STRING),
            'senior_management_officer.senior_management_officer_document.identity_document_addt' => $ruleOrNullable(self::NULLABLE_STRING),
            'senior_management_officer.senior_management_officer_document.identity_document_addt_size' => $ruleOrNullable(self::NULLABLE_STRING),
            'senior_management_officer.required_indicator' => 'sometimes'
        ];
    }

    function companySourceRules($corporate_saving)
    {
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        return [
            "source_of_funds" => $ruleOrNullable(self::REQUIRED_STRING),
            "country_source_of_funds" => $ruleOrNullable(self::ARRAY),
            "country_source_of_funds.*" => $ruleOrNullable(self::REQUIRED_STRING),
            "source_of_wealth" => $ruleOrNullable(self::ARRAY),
            "source_of_wealth.*" => $ruleOrNullable(self::REQUIRED_STRING),
            "country_source_of_wealth" => $ruleOrNullable(self::ARRAY),
            'source_of_wealth_other' => $corporate_saving
                ? 'nullable'
                : [
                    Rule::requiredIf(in_array('Other', $this->input('source_of_wealth', [])))
                ],
            "country_source_of_wealth.*" => $ruleOrNullable(self::REQUIRED_STRING),
        ];
    }

    function sepaDDRules($corporate_saving)
    {
        $ruleOrNullable = function ($rule) use ($corporate_saving) {
            return $corporate_saving ? 'nullable' : $rule;
        };

        return [
            self::PROCESSING_SEPADD_PAYMENTS => $ruleOrNullable(self::REQUIRED_STRING),
            "sepa_dd_products" => $ruleOrNullable(self::ARRAY),
            "sepa_dd_products.*.name" => $ruleOrNullable(self::REQUIRED_MAX_40),
            "sepa_dd_products.*.value" => $ruleOrNullable(self::REQUIRED_INT),
            "sepa_dd_products.*.description" => $ruleOrNullable(self::REQUIRED_MAX_200),
            "expected_global_mon_vol" => $ruleOrNullable(self::REQUIRED_INT)
        ];
    }
    public function messages()
    {
        return [
            'tax_information.registration_number.unique' => 'The registration number has already been taken.',
        ];
    }
}
