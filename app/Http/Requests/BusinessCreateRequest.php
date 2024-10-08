<?php

namespace App\Http\Requests;

use App\Models\Auth\User;
use App\Models\Business;
use App\Rules\ValidPaymentConfirmation;
use App\Services\LocalUser\Facades\LocalUser;
use App\Traits\QueryParamsValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessCreateRequest extends FormRequest
{
    use QueryParamsValidator;

    const ISO_COUNTRY = ['max:3', 'string'];
    const PHONE = 'max:15';
    const INT = 'digits_between:0,11';
    const REQUIRED_ISO_COUNTRY = ['required','max:3', 'string'];

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

        return [
            "name" => 'required',
            "tax_information.tax_country" => [Rule::requiredIf(!$corporate_saving), self::ISO_COUNTRY],
            "tax_information.registration_number" => $corporate_saving ? 'nullable' : ['max:20', 'required_unless:tax_information.tax_country,DEU'],
            "tax_information.registration_type" => [Rule::in(Business::REGISTRATION_TYPE)],
            "tax_information.tax_identification_number" => ['max:20'],
            "vat_number" => $corporate_saving ? 'nullable' : ['min:4', self::PHONE, 'required_unless:tax_information.tax_country,DEU'],
            "foundation_date" => [Rule::requiredIf(!$corporate_saving), 'date'],
            "telephone" => ['min:11', self::PHONE],
            "email" => 'email',
            "website" => 'url',
            "registered_address.line_1" => [Rule::requiredIf(!$corporate_saving)],
            "registered_address.city" => [Rule::requiredIf(!$corporate_saving)],
            "registered_address.postal_code" => [Rule::requiredIf(!$corporate_saving)],
            "registered_address.country" => [Rule::requiredIf(!$corporate_saving)],
            "contact_details.first_name" => [Rule::requiredIf(!$corporate_saving)],
            "contact_details.last_name" => [Rule::requiredIf(!$corporate_saving)],
            "contact_details.position_held" => [Rule::requiredIf(!$corporate_saving)],
            "contact_details.country_code" => [Rule::requiredIf(!$corporate_saving)],
            "contact_details.mobile_no" => [Rule::requiredIf(!$corporate_saving), self::PHONE],
            "contact_details.email" => [Rule::requiredIf(!$corporate_saving), 'email'],
            "business_details.finxp_products" => [Rule::requiredIf(!$corporate_saving), 'array'],
            "business_details.industry_key" => [Rule::requiredIf(!$corporate_saving), 'array'],
            "business_details.industry_key.*" => 'string',
            "business_details.number_employees" => self::INT,
            "business_details.number_of_years" => self::INT,
            "business_details.share_capital" => [Rule::requiredIf(!$corporate_saving), 'numeric', 'lte:99999999999'],
            "business_details.number_shareholder" => [Rule::requiredIf(!$corporate_saving), 'gt:0'],
            "business_details.number_directors" => ['numeric', 'gt:0'],
            "business_details.previous_year_turnover" => 'date_format:Y',
            "business_details.license_rep_juris" =>  $corporate_saving ? 'nullable' : [Rule::requiredIf($isBetterPaymentProgram), Rule::in(Business::LICENSE_REP_JURIS)],
            "business_details.country_of_license" => self::ISO_COUNTRY,
            "business_details.country_juris_dealings.*" => self::ISO_COUNTRY,
            "business_details.country_juris_dealings" => 'array',
            "business_details.business_year_count" => self::INT,
            "business_details.source_of_funds" => 'array',
            "business_details.source_of_funds.*" =>'string',
            "business_details.terms_and_conditions" => 'boolean',
            "business_details.privacy_accepted" => 'boolean',
            "business_details.mid" => ['max:30', 'string'],
            "business_details.creditor_identifier" => ['max:10', 'string'],
            "iban4u_payment_account.country_origin" => 'array',
            "iban4u_payment_ac.country_remittance" => 'array',
            "iban4u_payment_ac.country_remittance.*" => self::ISO_COUNTRY,
            "iban4u_payment_account.estimated_monthly_transactions" => self::INT,
            "iban4u_payment_account.average_amount_transaction_euro" => 'numeric',
            "iban4u_payment_account.accepting_third_party_funds" => [Rule::requiredIf($iban4UPayload), Rule::in(ValidPaymentConfirmation::CONFIRM)],
            "sepa_dd_direct_debit.currently_processing_sepa_dd" => 'string',
            "sepa_dd_direct_debit.sepa_dds" => 'array',
            "sepa_dd_direct_debit.sepa_dd_volume_per_month" => 'numeric',
            "credit_card_processing.currently_processing_cc_payments" => [Rule::requiredIf($creditCardProcessingPayload), new ValidPaymentConfirmation()],
            "credit_card_processing.offer_recurring_billing" => [Rule::requiredIf($creditCardProcessingPayload), new ValidPaymentConfirmation()],
            "credit_card_processing.offer_refunds" => [Rule::requiredIf($creditCardProcessingPayload), new ValidPaymentConfirmation()],
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
            'corporate_saving' => 'required|boolean'
        ];
    }
}
