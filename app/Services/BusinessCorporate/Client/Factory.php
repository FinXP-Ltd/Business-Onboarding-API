<?php

namespace App\Services\BusinessCorporate\Client;

use App\Traits\Services\BusinessCorporate\InvitationAndSharing;
use Throwable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\Database\QueryException;

use App\Exceptions\DuplicateEntityException;
use App\Exceptions\KycpResponseException;
use App\Exceptions\SaveException;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\CompanyIban4UAccount;
use App\Models\CompanyIban4UAccountCountry;
use App\Models\CompanyRepresentativeDocuments;
use App\Models\IdentityInformation;
use App\Models\ResidentialAddress;
use App\Models\CompanyAddress;
use App\Models\CompanyInformation;
use App\Models\CompanySepaDd;
use App\Models\CompanySepaDdProduct;
use App\Models\CompanySource;
use App\Models\CompanySourceCountry;
use App\Services\KYCP\Traits\Application;

use App\Traits\ApplyCorporateProcessData;
use Carbon\Carbon;
use App\Http\Resources\CompanyAddressResource;
use App\Http\Resources\CompanyCreditCardProcessingResource;
use App\Http\Resources\CompanyDataProtectionMarketingResource;
use App\Http\Resources\CompanyDeclarationResource;
use App\Http\Resources\CompanyDefaultResource;
use App\Http\Resources\CompanyIban4uAccountResource;
use App\Http\Resources\CompanyInformationResource;
use App\Http\Resources\CompanyRepresentativeParentResource;
use App\Http\Resources\CompanyRequiredDocumentsResource;
use App\Http\Resources\CompanySepaDDResource;
use App\Http\Resources\CompanySourcesResource;
use App\Services\BusinessCorporate\Facades\BusinessCorporateDocument;

class Factory
{
    use ApplyCorporateProcessData, Application, InvitationAndSharing;
    /**
     * Update Company
     *
     */
    const COMPANY_REPRESENTATIVE = 'company_representative';

    const APPLY_CORPORATE_FOLDER = 'apply_corporate';

    public function createBusinessCorporate(Request $payload)
    {
        DB::beginTransaction();

        try {
           $user = User::whereAuth0(auth()->id())->first();

           $business = Business::create(['user' => $user->id]);

           $company = CompanyInformation::create([
               'business_id' => $business->id,
               'company_name' => $payload->name
            ]);

            $company->companyAddress()->create();
            $company->companySource()->create();
            $company->companySourceCountry()->create();
            $company->companySepaDd()->create();
            $company->companyIban4u()->create();
            $company->companyCreditCardProcessing()->create();
            $company->companyDataProtectionMarketing()->create();
            $company->companyDeclaration()->create();

            DB::commit();

            return $business->id;

        } catch (Exception $e) {
            info($e);
           DB::rollBack();
           $this->throwException($e);
        }
    }

    public function updateCompanyInformation($companyInformation, Request $request)
    {
        DB::beginTransaction();
        $info = [];
        try {

            $infoPayload = [
                'company_name' => $request->name,
                'registration_number' => $request->registration_number,
                'type_of_company' => $request->registration_type,
                'company_trading_as' => $request->trading_name,
                'date_of_incorporation' => $request->foundation_date ? Carbon::parse($request->foundation_date)->format('Y-m-d') : $request->foundation_date,
                'country_of_incorporation' => $request->tax_country,
                'number_of_employees' => $request->number_employees,
                'number_of_years' => $request->number_of_years,
                'vat_number' => $request->vat_number,
                'tin' => $request->tax_identification_number,
                'tin_jurisdiction' => $request->jurisdiction,
                'industry_type' => $request->industry_key,
                'business_activity_description' => $request->business_activity_description,
                'industry_description' => $request->description,
                'share_capital' => $request->share_capital,
                'previous_year_turnover' => $request->previous_year_turnover,
                'email' => $request->email,
                'website' => $request->website,
                'additional_website' => $request->additional_website,
                "is_group_corporate" =>  $request->is_part_of_group,
                "parent_holding_company" => $request->parent_holding_company,
                "parent_holding_company_other" => $request->parent_holding_company_other,
                "company_fiduciary_capacity" =>  $request->has_fiduciary_capacity,
                "allow_constituting_documents" => $request->has_constituting_documents,
                "is_company_licensed" =>  $request->is_company_licensed,
                "licensed_in" => $request->license_rep_juris,
                "full_name" =>  $request->contact_person_name,
                "email_address" => $request->contact_person_email,
            ];

            $info = $companyInformation->update($infoPayload);

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
        }

        return $info;
    }

    public function updateCompanyAddress($companyInformation, Request $request)
    {
        DB::beginTransaction();
        try {
            $registered_address = (array)$request->registered_address;
            $operational_address = (array)$request->operational_address;
            $address = $companyInformation->companyAddress;

            $payload = [
                'company_information_id' => $companyInformation->id,
                'registered_street_number' => $registered_address['registered_street_number'] ?? null,
                'registered_street_name' => $registered_address['registered_street_name'] ?? null,
                'registered_postal_code' => $registered_address['registered_postal_code'] ?? null,
                'registered_city' => $registered_address['registered_city'] ?? null,
                'registered_country' => $registered_address['registered_country'] ?? null,
                'operational_street_number' => $operational_address['operational_street_number'] ?? null,
                'operational_street_name' => $operational_address['operational_street_name'] ?? null,
                'operational_postal_code' => $operational_address['operational_postal_code'] ?? null,
                'operational_city' => $operational_address['operational_city'] ?? null,
                'operational_country' => $operational_address['operational_country'] ?? null,
                'is_same_address' => $request->is_same_address ?? false
            ];

            $data = $address ? $address->update($payload) : CompanyAddress::create($payload);

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            throw new SaveException($e->getMessage(), $e->getMessage());
        }
        return $data;
    }

    public function updateCompanySources($companyInformation, Request $request)
    {

        $lookup =[
            [ 'attribute' => 'country_source_of_funds', 'model' => CompanySourceCountry::class, 'type' => 'fund', 'column_name_1' => 'company_information_id', 'column_name_2' => 'type', 'column_name_3' => 'country', 'column_name_4' => 'is_selected',],
            [ 'attribute' => 'source_of_wealth', 'model' => CompanySource::class, 'type' => 'wealth', 'column_name_1' => 'company_information_id', 'column_name_2' => 'type', 'column_name_3' => 'source_name', 'column_name_4' => 'is_selected',],
            [ 'attribute' => 'source_of_wealth_other', 'model' => CompanySource::class, 'type' => 'wealth', 'column_name_1' => 'company_information_id', 'column_name_2' => 'type', 'column_name_3' => 'source_name', 'column_name_4' => 'is_selected',],
            [ 'attribute' => 'country_source_of_wealth', 'model' => CompanySourceCountry::class, 'type' => 'wealth', 'column_name_1' => 'company_information_id', 'column_name_2' => 'type', 'column_name_3' => 'country', 'column_name_4' => 'is_selected',],
        ];

        DB::beginTransaction();
        try {
            $data = $request->except(['products', 'section', 'corporate_saving']);
            $companyInformation->update(['source_of_funds' => $request->source_of_funds]);

            $this->sourceLookup($data, $lookup, $companyInformation->id);

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            throw new SaveException($e->getMessage(), $e->getMessage());
        }

        return $companyInformation;
    }

    public function updateCompanySepaDD($companyInformation, Request $request)
    {
        DB::beginTransaction();
        try {
            $request_sepa_dd_products = $request->sepa_dd_products;
            $sepa = $companyInformation?->companySepaDD ?? null;
            $sepa_dd_products = $sepa?->sepaProducts ?? null;

            $payload = [
                'company_information_id' => $companyInformation->id,
                'processing_sepa_dd' => $request->processing_sepa_dd ?? null,
                'expected_global_mon_vol' => $request->expected_global_mon_vol ?? null
            ];

            if($sepa && $sepa_dd_products) {
                $sepa->update($payload);
                if($sepa_dd_products && count($request_sepa_dd_products) > 0) {
                    $sepa_dd_products->where('company_sepa_dd_id', $sepa->id)->each->delete();
                }
            } else {
                $sepa = CompanySepaDd::create($payload);
            }

            foreach($request_sepa_dd_products as $request_sepa_dd_product) {
                if($request_sepa_dd_product['name'] || $request_sepa_dd_product['value'] || $request_sepa_dd_product['description']) {
                    CompanySepaDdProduct::updateOrCreate([
                        'company_sepa_dd_id' => $sepa->id,
                        'name' => $request_sepa_dd_product['name'] ?? null,
                        'value' => $request_sepa_dd_product['value'] ?? null,
                        'description' => $request_sepa_dd_product['description'] ?? null,
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            throw new SaveException($e->getMessage(), $e->getMessage());
        }
        return $sepa;
    }

    public function updateCompanyIban4u($companyInformation, Request $request)
    {
        DB::beginTransaction();
        try {
            $iban4u_payment_account = (array)$request->iban4u_payment_account;
            $deposit = $iban4u_payment_account['deposit'];
            $withdraw = $iban4u_payment_account['withdrawal'];
            $activity = $iban4u_payment_account['activity'];

            $iban4u_account = $companyInformation?->companyIban4u ?? null;
            $iban4u_account_activities = $iban4u_account?->activities ?? null;

            $lookup =[
                [ 'attribute' => 'deposit', 'model' => CompanyIban4UAccountCountry::class, 'type' => 'deposit', 'column_name_1' => 'company_iban4u_account_id', 'column_name_2' => 'type', 'column_name_3' => 'country', 'column_name_4' => 'is_selected', 'second_attribute' => 'countries'],
                [ 'attribute' => 'withdrawal', 'model' => CompanyIban4UAccountCountry::class, 'type' => 'withdraw', 'column_name_1' => 'company_iban4u_account_id', 'column_name_2' => 'type', 'column_name_3' => 'country', 'column_name_4' => 'is_selected', 'second_attribute' => 'countries'],
            ];

            $payload = [
                'company_information_id' => $companyInformation->id,
                'annual_turnover' => $iban4u_payment_account['annual_turnover'],
                'purpose_of_account_opening' => $iban4u_payment_account['purpose_of_account_opening'],

                'deposit_type' => is_array($deposit['trading']) ? implode(',', $deposit['trading']) : null,
                'deposit_approximate_per_month' => $deposit['approximate_per_month'],
                'deposit_cumulative_per_month' => $deposit['cumulative_per_month'],

                'withdrawal_type' => is_array($withdraw['trading']) ? implode(',', $withdraw['trading']) : null,
                'withdrawal_approximate_per_month' => $withdraw['approximate_per_month'],
                'withdrawal_cumulative_per_month' => $withdraw['cumulative_per_month'],

                'held_accounts' => $activity['held_accounts'],
                'held_accounts_description' => $activity['held_accounts_description'],
                'refused_banking_relationship' => $activity['refused_banking_relationship'],
                'refused_banking_relationship_description' => $activity['refused_banking_relationship_description'],
                'terminated_banking_relationship' => $activity['terminated_banking_relationship'],
                'terminated_banking_relationship_description' => $activity['terminated_banking_relationship_description'],
            ];

            if($iban4u_account) {
                $iban4u_account->update($payload);
                $iban4u_account_activities->where('company_iban4u_account_id', $iban4u_account->id)->each->delete();
            } else {
                $iban4u_account = CompanyIban4UAccount::create($payload);
            }

            //add or remove countries
            $this->sourceLookup($iban4u_payment_account, $lookup, $iban4u_account->id);

            $existingIncomingActivities = $companyInformation->companyIban4u->activities()->where('type', 'incoming')->get();
            $existingOutgoingActivities = $companyInformation->companyIban4u->activities()->where('type', 'outgoing')->get();

            //create or update incoming activity
            foreach ($activity['incoming_payments'] as $index => $requestIncomingActivity) {
                $incomingIndexId = (int)$index + 1;
                $existingIncomingActivity = $existingIncomingActivities->where('index', $incomingIndexId)->first();

                $incomingData = $this->iban4uProcessData($requestIncomingActivity, $incomingIndexId, 'incoming');

                if ($existingIncomingActivity) {
                    $existingIncomingActivity->update($incomingData);
                } else {
                    $iban4u_account?->activities()->create($incomingData);
                }
            }

            //create or update outgoing activity
            foreach ($activity['outgoing_payments'] as $index => $requestOutgoingActivity) {
                $outgoingIndexId = (int)$index + 1;
                $existingOutgoingActivity = $existingOutgoingActivities->where('index', $outgoingIndexId)->first();

                $outgoingData = $this->iban4uProcessData($requestOutgoingActivity, $outgoingIndexId, 'outgoing');

                if ($existingOutgoingActivity) {
                    $existingOutgoingActivity->update($outgoingData);
                } else {
                    $iban4u_account?->activities()->create($outgoingData);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            throw new SaveException($e->getMessage(), $e->getMessage());
        }

        return $iban4u_account ?? null;
    }

    public function usaTaxLiability($companyInformation, $request)
    {
        $existingTax = $companyInformation->usaTaxLiability;
        $tax = $request->tax_name;

        if ($existingTax !== null && $tax) {
            $existingTax->update(['tax_name' => $tax]);
        } else {
            $companyInformation->usaTaxLiability()->create(['tax_name' => $tax]);
        }

       return $tax ?? null;
    }

    public function companyRepresentative($companyInformation, Request $request)
    {
        DB::beginTransaction();
        try {

            $companyRepresentatives = $request->company_representative;
            $existingRepresentatives = $companyInformation->companyRepresentative;
            $representativeToDelete = [];

            $companyInformation->companyRepresentative()->whereIn('index', $representativeToDelete)->delete();
            if($companyRepresentatives) {

                foreach ($existingRepresentatives as $existingRepresentative) {
                    $representativeIndex = $existingRepresentative->index;
                    if (!isset($companyRepresentatives[$representativeIndex - 1])) {
                        array_push($representativeToDelete, $representativeIndex);
                        $companyInformation->companyRepresentative()->where('index', $representativeIndex)->delete();
                    }
                }

                if (!empty($representativeToDelete)) {
                    ResidentialAddress::whereIn('index', $representativeToDelete)->delete();
                    IdentityInformation::whereIn('index', $representativeToDelete)->delete();
                    CompanyRepresentativeDocuments::whereIn('index', $representativeToDelete)->delete();
                }

                $this->eachCompanyRepresentative($companyRepresentatives, $existingRepresentatives, $companyInformation);
            }

            DB::commit();
            return $companyRepresentatives ?? null;
        } catch (Exception $e) {
            info($e);
            DB::rollBack();

            throw new SaveException($e->getMessage(), $e->getCode());
        }
    }

    public function seniorManagementOfficers($companyInformation, Request $request)
    {
        $existingSeniorOfficer = $companyInformation->seniorManagementOfficer;
        $seniorManagementRequest = $request->senior_management_officer;
        $seniorOfficerData = $this->SMO($seniorManagementRequest);

        $this->passFilter($seniorManagementRequest);

        $residentialAddressData = $this->smoAddress($seniorManagementRequest);
        $identityInformationData = $this->smoInformation($seniorManagementRequest);
        $companyRepDocumentsData = $this->smoDocuments($seniorManagementRequest);

        if ($existingSeniorOfficer !== null) {
            $existingSeniorOfficer->update($seniorOfficerData);
            $existingSeniorOfficer->seniorOfficerResidentialAddress()->update($residentialAddressData);
            $existingSeniorOfficer->seniorOfficerIdentityInformation()->update($identityInformationData);
            $existingSeniorOfficer->seniorManagementOfficerDocuments()->update($companyRepDocumentsData);
        } else {
            $newSeniorOfficer = $companyInformation->seniorManagementOfficer()->create($seniorOfficerData);
            $newSeniorOfficer->seniorOfficerResidentialAddress()->create($residentialAddressData);
            $newSeniorOfficer->seniorOfficerIdentityInformation()->create($identityInformationData);
            $newSeniorOfficer->seniorManagementOfficerDocuments()->create($companyRepDocumentsData);
        }

        return $seniorOfficerData ?? null;
    }

    public function updateCompanyCreditCardProcessing($companyInformation, Request $request)
    {
        if ($request->has('credit_card_processing') && $companyInformation->companyCreditCardProcessing()->exists()) {
            $creditCardProcessingPayload = $request->get('credit_card_processing');
            $creditCardProcessing = $companyInformation->companyCreditCardProcessing()->first();
            $creditCardProcessing->update(
                collect($creditCardProcessingPayload)
                    ->except(
                        'countries',
                         $request->get('section') === 'acquiring-services'
                         ? 'trading_urls' :
                         '',
                    )
                    ->toArray()
            );

            if ($request->has('credit_card_processing.countries')) {
                $this->updateCreditCardCountries($creditCardProcessing, $creditCardProcessingPayload, $request, $companyInformation);
            }
        }
    }

    public function updateCreditCardCountries($creditCardProcessing, $creditCardProcessingPayload, $request, $companyInformation)
    {
        $countriesData = $creditCardProcessingPayload['countries'];
        $tradingUrlsData = $creditCardProcessingPayload['trading_urls'];
        $existingTradingUrls = $creditCardProcessing->companyTradingUrl;

        foreach ($existingTradingUrls as $existingUrl) {
            $payloadIndex = array_search($existingUrl->trading_urls, $tradingUrlsData);

            if ($payloadIndex !== false && $tradingUrlsData[$payloadIndex] !== null) {
                $existingUrl->update(['trading_urls' => $tradingUrlsData[$payloadIndex]]);
            } else {
                $existingUrl->delete();
            }
            unset($tradingUrlsData[$payloadIndex]);
        }

        foreach ($tradingUrlsData as $url) {
            if ($url !== null) {
                $creditCardProcessing->companyTradingUrl()->create(['trading_urls' => $url]);
            }
        }
        $this->creditCardNumericValues($request, $companyInformation);
        $this->processCountriesData($creditCardProcessing, $countriesData);

    }

    public function updateCompanyDataProtectionMarketing($companyInformation, Request $request)
    {

        DB::beginTransaction();
        try {

            if ($request->has('data_protection_marketing') && $companyInformation->companyDataProtectionMarketing) {
                $companyInformation->companyDataProtectionMarketing()->update($request->get('data_protection_marketing'));
            }
            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
        }

        return $companyInformation->companyDataProtectionMarketing;

    }

    public function updateDeclaration($companyInformation, Request $request)
    {
        DB::beginTransaction();

        try {

            $req = $request->except(['products', 'section', 'user', 'file', 'corporate_saving']);
            if($companyInformation->companyDeclaration) {
                $companyInformation->companyDeclaration->update($req);
            }

            DB::commit();

        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            throw new SaveException($e->getMessage(), $e->getCode());
        }
    }

    public function updateCompanyRequiredDocuments($companyInformation, Request $request)
    {

        DB::beginTransaction();
        try {

            $this->handleDocument($companyInformation, $request, 'general_documents', [
                'memorandum_and_articles_of_association',
                'certificate_of_incorporation',
                'registry_exact',
                'company_structure_chart',
                'proof_of_address_document',
                'operating_license'
            ]);

            $this->handleDocument($companyInformation, $request, 'iban4u_payment_account_documents', [
                'agreements_with_the_entities',
                'board_resolution',
                'third_party_questionnaire'
            ]);

            $this->handleDocument($companyInformation, $request, 'credit_card_processing_documents', [
                'proof_of_ownership_of_the_domain',
                'processing_history',
                'cc_copy_of_bank_settlement',
                'company_pci_certificate'
            ]);

            $this->handleDocument($companyInformation, $request, 'sepa_direct_debit_documents', [
                'template_of_customer_mandate',
                'processing_history_with_chargeback_and_ratios',
                'sepa_copy_of_bank_settlement',
                'product_marketing_information'
            ]);

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
        }

    }

    public function deleteApplication(Business $business): array
    {
        DB::beginTransaction();

        try {
            BusinessCorporateDocument::setBusiness($business)
                ->deleteDocuments();

            $business->forceDelete();

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            info($exception);

            return [
                'code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'status' => 'failed',
                'message' => 'Unable to delete application!'
            ];
        }

        return [
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Successfully Deleted Application!',
        ];
    }

    private function throwException(Throwable $e)
    {
        if ($e instanceof QueryException && $e->errorInfo[1] == 1062) {
            throw new DuplicateEntityException($e->errorInfo[2], $e->getCode());
        }

        throw $e;
    }

    public function submit($business)
    {
        try {
            $products = $business->products->where('is_selected', true)->pluck('product_name')->toArray();

            $programId = in_array(Business::IBAN4U, $products)
                ? config('kycp.program_id.iban4u_applications')
                : config('kycp.program_id.payment_processing');

            return $this->addFullBusinessCorporateApplicationToKYC($business, $programId);

        } catch (Exception $e) {

            throw new KycpResponseException(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getSection($company,$section)
    {
        switch ($section) {
            case 'details':
                $response = new CompanyInformationResource($company);
                break;
            case 'address':
                $response = new CompanyAddressResource($company);
                break;
            case 'sources':
                $response = new CompanySourcesResource($company);
                break;
            case 'sepa-dd':
                $response = new CompanySepaDDResource($company);
                break;
            case 'iban4u':
                $response = new CompanyIban4uAccountResource($company);
                break;
            case 'acquiring-services':
                $response = new CompanyCreditCardProcessingResource($company);
                break;
            case 'representative':
                $response = new CompanyRepresentativeParentResource($company);
                break;
            case 'data-protection-marketing':
                $response = new CompanyDataProtectionMarketingResource($company);
                break;
            case 'declaration':
                $response = new CompanyDeclarationResource($company);
                break;
            case 'required-documents':
                $response = new CompanyRequiredDocumentsResource($company);
                break;
            default:

                $response = new CompanyDefaultResource($company);
                break;
        }

        return $response;
    }

    public function updateSection($section, $business, $request)
    {
        switch ($section) {
            case 'company-details':
                $this->updateCompanyinformation($business->companyInformation, $request);
                break;
            case 'company-address':
                $this->updateCompanyAddress($business->companyInformation, $request);
                break;
            case 'company-sources':
                $this->updateCompanySources($business->companyInformation, $request);
                break;
            case 'company-sepa-dd':
                $this->updateCompanySepaDD($business->companyInformation, $request);
                break;
            case 'iban4u-payment-account':
                $this->updateCompanyIban4u($business->companyInformation, $request);
                break;
            case 'acquiring-services':
                $this->updateCompanyCreditCardProcessing($business->companyInformation, $request);
                break;
            case 'company_representatives':
                $this->companyRepresentative($business->companyInformation, $request);
                $this->seniorManagementOfficers($business->companyInformation, $request);
                $this->usaTaxLiability($business->companyInformation, $request);
                break;
            case 'data-protection-and-marketing':
                $this->updateCompanyDataProtectionMarketing($business->companyInformation, $request);
                break;
            case 'declaration':
                $this->updateDeclaration($business->companyInformation, $request);
                break;
            case 'required_documents':
                $this->updateCompanyRequiredDocuments($business->companyInformation, $request);
                break;
            default:
                break;
        }
    }
}
