<?php

namespace App\Services\Business\Client;

use App\Exceptions\DuplicateEntityException;
use App\Models\Business;
use App\Models\BusinessComposition;
use App\Models\CompanyRepresentativeDocuments;
use App\Models\IdentityInformation;
use App\Models\LookupType;
use App\Models\Document;
use App\Models\IBAN4UPaymentAccount;
use App\Models\Iban4uPaymentOrders;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\ResidentialAddress;
use App\Models\SepaDd;
use Illuminate\Database\Eloquent\Model;
use App\Models\KycpRequirement;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Throwable;
use App\Traits\SaveUserToken;

class Factory
{
    use SaveUserToken;

    const DIRECTOR = 'DIR';
    const DIRECTOR_CORPORATE = 'DIR_CORPORATE';
    const SHAREHOLDER = 'SH';
    const UBO = 'UBO';
    const SHAREHOLDER_CORPORATE = 'SH_CORPORATE';
    const BUSINESS = 'B';
    const TYPE = 'lookupType.type';

    public function createBusiness(Request $payload)
    {
        DB::beginTransaction();

        try {
            $business = Business::create($payload->all());

            $this->createTaxInformation($business, $payload);
            $this->createAddresses($business, $payload);
            $business->businessDetails()->create(['business_id', $business->id]);
            $creditCardProcessing = $business->creditCardProcessing()->create(['business_id', $business->id]);
            $business->sepaDdDirectDebit()->create(['business_id', $business->id]);

            if ($payload->has('contact_details')) {
                $business->contactDetails()->create($payload->get('contact_details'));
            }

            if ($payload->has('sepa_dd_direct_debit')) {
                $this->sepaDirectDebit($business, $payload->get('sepa_dd_direct_debit'));
            }

            if ($payload->has('business_details')) {
                $this->updateBusinessDetails($business, $payload);

                if ($payload->has('iban4u_payment_account')) {
                    $this->iban4uPaymentAccount($business, $payload->get('iban4u_payment_account'));
                }

                 if ($payload->has('credit_card_processing')) {
                    $creditCardProcessingPayload = $payload->get('credit_card_processing');

                    $creditCardProcessing->update($creditCardProcessingPayload);
                    $this->attachLookupTypes($creditCardProcessing, $creditCardProcessingPayload, [
                        [ 'attribute' => 'alternative_payment_methods', 'group' => 'AlternativePaymentMethods' ],
                        [ 'attribute' => 'method_currently_offered', 'group' => 'MethodCurrentlyOffered' ],
                    ]);
                }
            }

            DB::commit();
            return $business->id;
        } catch (Throwable $e) {
            info($e);
            DB::rollBack();
            $this->throwException($e);
        }
    }

    public function updateBusiness(Model $business, Request $payload)
    {
        DB::beginTransaction();

        try {
            $business->update(
                Collection::make($payload)
                ->except(
                    'tax_information',
                    'registered_address',
                    'operational_address',
                    'contact_details',
                    'sepa_dd_direct_debit',
                    'business_details',
                    'iban4u_payment_account',
                    'credit_card_processing',
                )
                ->toArray()
            );

            if ($payload->has('name')) {
                $business->taxInformation()->update(['name' => $payload->get('name')]);
            }

            if ($payload->has('tax_information')) {
                $business->taxInformation()->update($payload->get('tax_information'));
            }

            if ($payload->has('registered_address') && $business->registeredAddress()->first()) {
                $business->registeredAddress()->update($payload->get('registered_address'));
            }else{
                $this->createAddresses($business, $payload);
            }

            if ($payload->has('operational_address') && $business->operationalAddress()->first()) {
                $business->operationalAddress()->update($payload->get('operational_address'));
            }else{
                $this->createAddresses($business, $payload);
            }

            if ($payload->has('contact_details')) {
                $business->contactDetails()->update($payload->get('contact_details'));
            }

            if ($payload->has('sepa_dd_direct_debit')) {
                $this->sepaDirectDebit($business, $payload->get('sepa_dd_direct_debit'));
            }

            if ($payload->has('data_protection_marketing') && $business->dataProtectionMarketing) {
                $business->dataProtectionMarketing()->update($payload->get('data_protection_marketing'));
            }

            if ($payload->has('business_details')) {
                $this->updateBusinessDetails($business, $payload);
            }

            if ($payload->has('iban4u_payment_account')) {
                $this->iban4uPaymentAccount($business, $payload->get('iban4u_payment_account'));
            }

            if ($payload->has('data_protection_marketing') && !$business->dataProtectionMarketing) {
                $dataProtectionMarketing = $payload->get('data_protection_marketing');
                $business->dataProtectionMarketing()->create(
                   Collection::make($dataProtectionMarketing)->toArray());
            }

            if ($payload->has('credit_card_processing') && $business->creditCardProcessing()->exists()) {
                $creditCardProcessingPayload = $payload->get('credit_card_processing');
                $creditCardProcessing = $business->creditCardProcessing()->first();
                $creditCardProcessing->update(
                    collect($creditCardProcessingPayload)
                        ->except(
                            'countries',
                            'alternative_payment_methods',
                            'method_currently_offered',
                             $payload->get('section') === 'acquiring-services'
                             ? 'trading_urls' :
                             '',
                        )
                        ->toArray()
                );

                if ($payload->has('credit_card_processing.countries')) {
                    $countriesData = $creditCardProcessingPayload['countries'];
                    $tradingUrlsData = $creditCardProcessingPayload['trading_urls'];
                    $existingTradingUrls = $creditCardProcessing->tradingUrl;

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
                            $creditCardProcessing->tradingUrl()->create(['trading_urls' => $url]);
                        }
                    }
                    $this->creditCardNumericValues($payload, $business);
                    $this->processCountriesData($creditCardProcessing, $countriesData);
                }

                if ($business->creditCardProcessing) {
                    $this->updateLookupTypes($business->creditCardProcessing, $creditCardProcessingPayload, [
                        [ 'attribute' => 'alternative_payment_methods', 'resource' => 'alternativePaymentMethods',  'group' => 'AlternativePaymentMethods' ],
                        [ 'attribute' => 'method_currently_offered', 'resource' => 'methodCurrentlyOffered', 'group' => 'MethodCurrentlyOffered' ],
                    ]);
                }
            }

            if ($payload->has("declaration_agreement")) {
                $this->handleDeclarationAgreement($business, $payload);
            }

            if ($payload->has("company_representative")) {
                $this->companyRepresentative($business, $payload->get('company_representative'));
            }

            if ($payload->has("senior_management_officer")) {
                $this->seniorManagementOfficers($business, $payload->get("senior_management_officer"));
            }

            $this->handleDocument($business, $payload, 'general_documents', [
                'memorandum_and_articles_of_association',
                'certificate_of_incorporation',
                'registry_exact',
                'company_structure_chart',
                'proof_of_address_document',
                'operating_license'
            ]);

            $this->handleDocument($business, $payload, 'iban4u_payment_account_documents', [
                'agreements_with_the_entities',
                'board_resolution',
                'third_party_questionnaire'
            ]);

            $this->handleDocument($business, $payload, 'credit_card_processing_documents', [
                'proof_of_ownership_of_the_domain',
                'processing_history',
                'copy_of_bank_settlement',
                'company_pci_certificate'
            ]);

            $this->handleDocument($business, $payload, 'sepa_direct_debit_documents', [
                'template_of_customer_mandate',
                'processing_history_with_chargeback_and_ratios',
                'copy_of_bank_settlement',
                'product_marketing_information'
            ]);


            DB::commit();
            return $business->id;
        } catch (Throwable $e) {
            info($e);
            DB::rollBack();
            $this->throwException($e);
        }
    }

    public function sepaDirectDebit($business, $payload){
        $business->sepaDdDirectDebit()->update(Collection::make($payload)->except('sepa_dds')->toArray());

        if(array_key_exists('sepa_dds', $payload)) {
            $sepaDds = Collection::make($payload['sepa_dds']);
            if($sepaDds->all()) {
                $id =$business->sepaDdDirectDebit()->pluck('sepa_dd_direct_debits.id')->first();
                SepaDd::where('sepa_dd_direct_debits', $id)->delete();

                collect($sepaDds->all())->map(function ($sepaDd) use ($id) {
                $sepaDd['sepa_dd_direct_debits'] = $id;
                    SepaDd::create($sepaDd);
                });
            }
        }
    }

    public function iban4uPaymentAccount($business, $payload)
    {
        if(!$business->iban4uPaymentAccount){
            $business->iban4uPaymentAccount()->create();
        }

        $tabs = ['deposit', 'withdrawal', 'activity'];
        foreach ($tabs as $section) {
            $get = $this->getFieldForIban4U($business, $section, $payload);
            $business->iban4uPaymentAccount()->update($get['data']);

            if(array_key_exists($section, $payload) && $get['countries'] && $payload[$section]['countries']){
                $this->iban4uLookupTypes($business->iban4uPaymentAccount, $payload[$section],$get['countries']);
            }

            if(array_key_exists($section, $payload) && $get['trading'] && $payload[$section]['trading']){
                $this->iban4uLookupTypes($business->iban4uPaymentAccount, $payload[$section],$get['trading']);
            }
        }
    }

    public function getFieldForIban4U($business, $section, $payload)
    {
        $default = [
            'purpose_of_account_opening' => $payload['purpose_of_account_opening'] ?? null,
            'annual_turnover' => $payload['annual_turnover'] ?? null,
            'partners_incoming_transactions' => $payload['partners_incoming_transactions'] ?? null,
            'estimated_monthly_transactions' => $payload['estimated_monthly_transactions'] ?? null,
            'average_amount_transaction_euro' => $payload['average_amount_transaction_euro'] ?? null,
            'accepting_third_party_funds' => $payload['accepting_third_party_funds'] ?? "YES"
        ];

        switch ($section) {
            case 'deposit':
                $data =[
                    'deposit_approximate_per_month' => $payload['deposit']['approximate_per_month'] ?? null,
                    'deposit_cumulative_per_month' => $payload['deposit']['cumulative_per_month'] ?? null
                ];
                $countries = [
                    [ 'attribute' => 'countries', 'resource' => 'depositCountries', 'group' => 'deposit_countries' ]
                ];
                $trading = [
                    [ 'attribute' => 'trading', 'resource' => 'depositTrading', 'group' => 'deposit_trading' ]
                ];
                break;
            case 'withdrawal':
                $data =[
                        'withdrawal_approximate_per_month' => $payload['withdrawal']['approximate_per_month'] ?? null,
                        'withdrawal_cumulative_per_month' => $payload['withdrawal']['cumulative_per_month'] ?? null
                ];
                $countries = [
                    [ 'attribute' => 'countries', 'resource' => 'withdrawalCountries', 'group' => 'withdrawal_countries' ]
                ];
                $trading = [
                    [ 'attribute' => 'trading', 'resource' => 'withdrawalTrading', 'group' => 'withdrawal_trading' ]
                ];
                break;
            case 'activity':
                $data =[
                    'held_accounts' => $payload['activity']['held_accounts'] ?? null,
                    'held_accounts_description' => $payload['activity']['held_accounts_description'] ?? null,
                    'refused_banking_relationship' => $payload['activity']['refused_banking_relationship'] ?? null,
                    'refused_banking_relationship_description' => $payload['activity']['refused_banking_relationship_description'] ?? null,
                    'terminated_banking_relationship' => $payload['activity']['terminated_banking_relationship'] ?? null,
                    'terminated_banking_relationship_description' => $payload['activity']['terminated_banking_relationship_description'] ?? null
                ];
                $countries =null;
                $trading =null;

                if (array_key_exists('activity', $payload)) {
                    if(array_key_exists('incoming_payments', $payload['activity']) && $payload['activity']['incoming_payments']){
                        $this->iban4uPaymentOrder($business->id, $payload['activity']['incoming_payments'], 'incoming');
                    }

                    if(array_key_exists('outgoing_payments', $payload['activity']) && $payload['activity']['outgoing_payments']){
                        $this->iban4uPaymentOrder($business->id, $payload['activity']['outgoing_payments'], 'outgoing');
                    }
                }

                break;
            default:
                $data =null;
                $countries =null;
                $trading =null;
                break;
        }

        return [
            'data' => array_merge($default, $data),
            'countries' => $countries,
            'trading' => $trading
        ];
    }

    public function iban4uPaymentOrder($id, $payload, $type)
    {
        $iban4u = IBAN4UPaymentAccount::where('business_id', $id)->first();

        Iban4uPaymentOrders::where('iban4u_payment_accounts_id', $iban4u->id)->where('type',$type)->delete();

        foreach($payload as $order){
            $order['type']= $type;
            $iban4u->payments()->create($order);
        }
    }

    public function companyRepresentative($business, $payloads)
    {
        $existingRepresentatives = $business->companyRepresentative;
        $representativeToDelete = [];

        foreach ($existingRepresentatives as $existingRepresentative) {
            $representativeIndex = $existingRepresentative->index;
            if (!isset($payloads[$representativeIndex - 1])) {
                $representativeToDelete[] = $representativeIndex;
            }
        }

        if (!empty($representativeToDelete)) {
            ResidentialAddress::whereIn('index', $representativeToDelete)->delete();
            IdentityInformation::whereIn('index', $representativeToDelete)->delete();
            CompanyRepresentativeDocuments::whereIn('index', $representativeToDelete)->delete();
        }

        $business->companyRepresentative()->whereIn('index', $representativeToDelete)->delete();

        foreach ($payloads as $index => $payload) {
            $representativeIdToUpdate = (int)$index + 1;
            $existingRepresentative = $existingRepresentatives->where('index', $representativeIdToUpdate)->first();

            $representativeData = $this->companyRepWithIndex($payload, $representativeIdToUpdate, $index);
            $residentialAddressData = $this->residentialAddressWithIndex($payload, $representativeIdToUpdate, $index);
            $identityInformationData = $this->identityInformationWithIndex($payload, $representativeIdToUpdate, $index);
            $companyRepresentativeDocumentData = $this->companyRepDocumentsWithIndex($payload, $representativeIdToUpdate, $index);

            if ($existingRepresentative) {
                $existingRepresentative->update($representativeData);
                $existingRepresentative->residentialAddress()->update($residentialAddressData);
                $existingRepresentative->identityInformation()->update($identityInformationData);
                $existingRepresentative->companyRepresentativeDocument()->update($companyRepresentativeDocumentData);
            } else {
                $newRepresentative = $business->companyRepresentative()->create($representativeData);
                $newRepresentative->residentialAddress()->create($residentialAddressData);
                $newRepresentative->identityInformation()->create($identityInformationData);
                $newRepresentative->companyRepresentativeDocument()->create($companyRepresentativeDocumentData);
            }
        }
    }

    public function seniorManagementOfficers($business, $payload)
    {
        $existingSeniorOfficer = $business->seniorManagementOfficer;

        $seniorOfficerData = $this->companyRep($payload);
        $residentialAddressData = $this->residentialAddress($payload);
        $identityInformationData = $this->identityInformation($payload);
        $companyRepDocumentsData = $this->companyRepDocuments($payload);
        if ($existingSeniorOfficer) {
            $existingSeniorOfficer->update($seniorOfficerData);
            $existingSeniorOfficer->seniorOfficerResidentialAddress()->update($residentialAddressData);
            $existingSeniorOfficer->seniorOfficerIdentityInformation()->update($identityInformationData);
            $existingSeniorOfficer->seniorManagementOfficerDocuments()->update($companyRepDocumentsData);
        } else {
             $newSeniorOfficer = $business->seniorManagementOfficer()->create($seniorOfficerData);
             $newSeniorOfficer->seniorOfficerResidentialAddress()->create($residentialAddressData);
             $newSeniorOfficer->seniorOfficerIdentityInformation()->create($identityInformationData);
             $newSeniorOfficer->seniorManagementOfficerDocuments()->create($companyRepDocumentsData);
        }
    }

    private function companyRep($payload)
    {
        $payloadCollection = collect($payload)->except(['residential_address', 'identity_information']);

        return [
            'first_name' => $payloadCollection->get('first_name'),
            'middle_name' => $payloadCollection->get('middle_name'),
            'surname' => $payloadCollection->get('surname'),
            'place_of_birth' => $payloadCollection->get('place_of_birth'),
            'date_of_birth' => $payloadCollection->get('date_of_birth'),
            'email_address' => $payloadCollection->get('email_address'),
            'nationality' => $payloadCollection->get('nationality'),
            'citizenship' => $payloadCollection->get('citizenship'),
            'phone_number' => $payloadCollection->get('phone_number'),
            'phone_code' => $payloadCollection->get('phone_code'),
            'roles_in_company' => $payloadCollection->get('roles_in_company'),
            'percent_ownership' => $payloadCollection->get('percent_ownership') ?? null,
            'iban4u_rights' => $payloadCollection->get('iban4u_rights') ?? null,
            'required_indicator' => $payloadCollection->get('required_indicator') ?? null
        ];
    }

    private function companyRepWithIndex($payload, $representativeIdToUpdate, $index)
    {
        $companyRepData = $this->companyRep($payload);
        $companyRepData['index'] = $representativeIdToUpdate;
        $companyRepData['order'] = (int) $index + 1;

        return $companyRepData;
    }

    private function residentialAddress($payload)
    {
        return [
            // 'index' => $representativeIdToUpdate,
            // 'order' => (int) $index + 1,
            'street_number' => $payload['residential_address']['street_number'] ?? null,
            'street_name' => $payload['residential_address']['street_name'] ?? null,
            'postal_code' => $payload['residential_address']['postal_code'] ?? null,
            'city' => $payload['residential_address']['city'] ?? null,
            'country' => $payload['residential_address']['country'] ?? null,
        ];
    }

    private function residentialAddressWithIndex($payload, $representativeIdToUpdate, $index)
    {
        $residentialAddressData = $this->residentialAddress($payload);
        $residentialAddressData['index'] = $representativeIdToUpdate;
        $residentialAddressData['order'] = (int) $index + 1;

        return $residentialAddressData;
    }

    private function identityInformation($payload)
    {
        return [
            'id_type' => $payload['identity_information']['id_type'] ?? null,
            'country_of_issue' => $payload['identity_information']['country_of_issue'] ?? null,
            'id_number' => $payload['identity_information']['id_number'] ?? null,
            'document_date_issued' => $payload['identity_information']['document_date_issued'] ?? null,
            'document_expired_date' => $payload['identity_information']['document_expired_date'] ?? null,
            'high_net_worth' => $payload['identity_information']['high_net_worth'] ?? null,
            'us_citizenship' => $payload['identity_information']['us_citizenship'] ?? null,
            'politically_exposed_person' => $payload['identity_information']['politically_exposed_person'] ?? null,
        ];
    }

    private function identityInformationWithIndex($payload, $representativeIdToUpdate, $index)
    {
        $identityInformationData = $this->identityInformation($payload);
        $identityInformationData['index'] = $representativeIdToUpdate;
        $identityInformationData['order'] = (int) $index + 1;

        return $identityInformationData;
    }

    private function companyRepDocuments($payload) {
        return [
            // 'index' => $representativeIdToUpdate,
            // 'order' => (int) $index + 1,
            'proof_of_address' => $payload['company_representative_document']['proof_of_address'] ?? null,
            'proof_of_address_size' => $payload['company_representative_document']['proof_of_address_size'] ?? null,
            'identity_document' => $payload['company_representative_document']['identity_document'] ?? null,
            'identity_document_size' => $payload['company_representative_document']['identity_document_size'] ?? null,
            'source_of_wealth' => $payload['company_representative_document']['source_of_wealth'] ?? null,
            'source_of_wealth_size' => $payload['company_representative_document']['source_of_wealth_size'] ?? null,
        ];
    }

    private function companyRepDocumentsWithIndex($payload, $representativeIdToUpdate, $index)
    {
        $companyRepDocumentsData = $this->companyRepDocuments($payload);
        $companyRepDocumentsData['index'] = $representativeIdToUpdate;
        $companyRepDocumentsData['order'] = (int) $index + 1;

        return $companyRepDocumentsData;
    }

    public function updateComposition(Model $businessComposition, Request $payload)
    {
        DB::beginTransaction();
        try {
            $modelTypePayload = $payload->get('model_type');
            $modelId = $payload->get('mapping_id');
            $person = null;

            if ($modelTypePayload === 'P' && $modelId) {
                $person = NaturalPerson::findOrFail($modelId);
            }

            if ($modelTypePayload === 'N' && $modelId) {
                $person = NonNaturalPerson::findOrFail($modelId);
            }

            $business = Business::findOrFail($payload->get('business_id'));
            $businessCompositionPayload = $payload->all();
            $businessCompositionPayload['business_id'] = $business->id;

            if ($payload->get('position') !== BusinessComposition::SHAREHOLDER) {
                $businessComposition->update(['voting_share' => 0]);
            }
            $businessComposition->update(Collection::make($payload)->toArray());
            $businessComposition->person()->update([
                'business_compositionable_id' => $modelId,
                'business_compositionable_type' => $person::class
            ]);

            $this->updateLookupTypes($businessComposition, $businessCompositionPayload, [
                [ 'attribute' => 'position', 'group' => BusinessComposition::POSITION_LOOKUP_GROUP ],
            ]);

            DB::commit();
            return $businessComposition->id;
        } catch (Throwable $e) {
            info($e);
            DB::rollBack();
            $this->throwException($e);
        }
    }

    public function createBusinessComposition(Request $payload)
    {
        DB::beginTransaction();

        try {
            $modelType = $payload->get('model_type');
            $modelId = $payload->get('mapping_id');

            $person = null;
            if ($modelType === 'P') {
                $person = NaturalPerson::findOrFail($modelId);
            }

            if ($modelType === 'N') {
                $person = NonNaturalPerson::findOrFail($modelId);
            }

            $business = Business::findOrFail($payload->get('business_id'));
            $businessCompositionPayload = $payload->all();
            $businessCompositionPayload['business_id'] = $business->id;
            $businessComposition = BusinessComposition::create($businessCompositionPayload);
            $this->attachLookupTypes($businessComposition, $businessCompositionPayload, [
                [ 'attribute' => 'position', 'group' => BusinessComposition::POSITION_LOOKUP_GROUP ]
            ]);
            $person->businessComposition()->create(['business_composition_id' => $businessComposition->id]);
            DB::commit();
            return $businessComposition->id;
        } catch (Throwable $e) {
            info($e);
            DB::rollBack();
            $this->throwException($e);
        }
    }

    public function findingPosition(Model $business, $lookupTypeId)
    {
        if ($lookupTypeId === self::DIRECTOR) {
            return BusinessComposition::where('business_id', $business->id)->get()->map(function ($businessComposition) {
                return $businessComposition->position()->with('lookupType')->get()->where(self::TYPE, self::DIRECTOR)->count();
            })->sum();
        }
        return BusinessComposition::where('business_id', $business->id)->get()->map(function ($businessComposition) {
            return $businessComposition->position()->with('lookupType')->get()->where(self::TYPE, self::SHAREHOLDER)->count();
        })->sum();
    }

    public function countAllPositionIntheBusiness(Model $business, $position)
    {
        return BusinessComposition::where('business_id', $business->id)->get()->map(function ($businessComposition) use ($position) {
            return [
                'position' => $businessComposition->position()->get()->where(self::TYPE, $position),
                'voting_share' => $businessComposition->position()->get(),
                'person' => $businessComposition->person()->get()
            ];
        });
    }

    public function documentCheck(Model $business, $list)
    {
        $missingList = [];
        $person = BusinessComposition::whereNotBetween('voting_share', [1, 24])->orWhere('person_responsible', "true")->get()->where('business_id', $business->id);

        foreach ($person as $p) {
            $position = $p->position()->with('lookupType')->get()->value(self::TYPE);
            $modelType = $p->model_type;

            $id = $p->person()->first()->business_compositionable_id;

            $position === 'DIR' && $modelType === 'N' ? $position = str_replace(self::DIRECTOR, self::DIRECTOR_CORPORATE, $position) : [];

            $position === 'SH' && $modelType === 'N' ? $position = str_replace(self::SHAREHOLDER, self::SHAREHOLDER_CORPORATE, $position) : [];

            $allDocuments = Document::where('documentable_id', $id)->where('owner_type', $modelType)->get();
            $listAllDocuments = Arr::pluck($allDocuments, 'document_type');
            $missingDocuments = Arr::flatten(array_diff($this->positionDocumentList($position, $modelType, $list,  $id), $listAllDocuments));
            (empty($missingDocuments)) ? $missingList : ($missingList[$id][$position] = $missingDocuments);
        }

        $kycpRequirement = new KycpRequirement();
        $documentList =  $kycpRequirement->otherRequirements(self::BUSINESS, $business->id, $list);

        $business = Document::where('documentable_id', $business->id)->where('owner_type', self::BUSINESS)->get();
        $listBusinessAllDocuments = Arr::pluck($business, 'document_type');
        $missingBusinessDocuments = Arr::flatten(array_diff($documentList['required'], $listBusinessAllDocuments));
        (empty($missingBusinessDocuments)) ? $missingList : ($missingList[self::BUSINESS] = $missingBusinessDocuments);

        return $missingList;
    }

    private function attachLookupType(string $attribute, string $group, array $data, mixed $entity)
    {
        if (!array_key_exists($attribute, $data)) {
            return null;
        }

        $types = $data[$attribute];
        $attribute = Str::camel(Str::remove('_', $attribute));
        foreach ($types as $type) {
            $lookupType = $this->lookupType::firstOrCreate([
                'group' => $group,
                'name' => $type,
                'description' => $attribute,
                'type' => $type
            ]);
            $entity->$attribute()->create(['lookup_type_id' => $lookupType->id]);
        }
    }

    private function updateLookupType(string $attribute, string $group, array $data, mixed $entity)
    {
        if (!array_key_exists($attribute, $data)) {
            return null;
        }

        $types = $data[$attribute];
        $attribute = Str::camel(Str::remove('_', $attribute));
        $entity->$attribute()->delete();
        foreach ($types as $type) {
            $lookupType = $this->lookupType::firstOrCreate([
                'group' => $group,
                'type' => $type
            ]);
            $entity->$attribute()->create(['lookup_type_id' => $lookupType->id]);
        }
    }

    public function creditCardNumericValues($payload, $business)
    {
        $creditCardProcessingPayload = $payload->get('credit_card_processing');
        $creditCardProcessing = $business->creditCardProcessing()->first();

        $keys = ['ac_cb_volumes_twelve_months', 'ac_cc_volumes_twelve_months', 'ac_refund_volumes_twelve_months'];

        foreach ($keys as $key) {
            $value = $creditCardProcessingPayload[$key] ?? null;

            if (!is_numeric($value) && is_numeric($creditCardProcessing->$key)) {
                $creditCardProcessingPayload[$key] = $creditCardProcessing->$key;
            } else {
                $creditCardProcessingPayload[$key] = is_numeric($value) ? $value : null;
            }
        }
    }

     /**
     * Handles the create, update, and delete for
     * credit card processing countries
     * @param mixed $business
     * @param mixed $payload
     * @return void
     */
    public function processCountriesData($creditCardProcessing, $countriesData)
    {
        $existingCountries = $creditCardProcessing->countries;
        $countriesToDelete = [];

        foreach ($existingCountries as $existingCountry) {
            $countryId = $existingCountry->id;
            if (!isset($countriesData[$countryId - 1])) {
                $countriesToDelete[] = $countryId;
            }
        }
        $creditCardProcessing->countries()->whereIn('id', $countriesToDelete)->delete();

        foreach ($countriesData as $index => $countryData) {
            $countryIdToUpdate = (int)$index + 1;
            $existingCountry = $existingCountries->where('id', $countryIdToUpdate)->first();

            if ($existingCountry) {
                $existingCountry->update([
                    'countries_where_product_offered' => $countryData['countries_where_product_offered'],
                    'distribution_per_country' => $countryData['distribution_per_country'],
                    'order' => (int)$index + 1
                ]);
            } else {
                $creditCardProcessing->countries()->create([
                    'id' => $countryIdToUpdate,
                    'countries_where_product_offered' => $countryData['countries_where_product_offered'],
                    'distribution_per_country' => $countryData['distribution_per_country'],
                    'order' => (int)$index + 1
                ]);
            }
        }
    }

    public function formatFileSize($size)
    {
        $units = ['B', 'KB', 'MB'];

        for ($i = 0; $size > 1024 && $i < 4; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Handles the create and update function for uploading
     * declaration
     * @param mixed $business
     * @param mixed $payload
     * @return void
     */
    public function handleDeclarationAgreement($business, $payload)
    {
        $declarationPayload = $payload->get("declaration_agreement");
        if ($payload->hasFile('declaration_agreement.file')) {
            $file = $payload->file('declaration_agreement.file');
            $declarationPayload['file_name'] = $file->getClientOriginalName();
            $declarationPayload['file_type'] = $file->extension();
            $declarationPayload['size'] = $this->formatFileSize($file->getSize());
        }

        if (!$business->declaration) {
            $business->declaration()->create(Collection::make($declarationPayload)->toArray());
        } else {
            $business->declaration()->update($declarationPayload);
        }
    }

    public function handleDocument($business, $payload, $documentType, $fileColumns)
    {
        if ($payload->has($documentType)) {
            $documentPayload = $payload->get($documentType);

            foreach ($fileColumns as $column) {
                if ($payload->hasFile("$documentType.$column")) {
                    $file = $payload->file("$documentType.$column");
                    $documentPayload[$column] = $file->getClientOriginalName();
                    $documentPayload["{$column}_size"] = $this->formatFileSize($file->getSize());
                }
            }

            $relationshipMethod = Str::camel($documentType);

            if (!$business->$relationshipMethod) {
                $business->$relationshipMethod()->create(Collection::make($documentPayload)->toArray());
            } else {
                $business->$relationshipMethod()->update($documentPayload);
            }
        }
    }

    public function getDocumentList($mappingId)
    {
        $models = [Business::class, NaturalPerson::class, NonNaturalPerson::class];
        $missingList = [];
        $existingId = null;

        foreach ($models as $model) {
            $entity = $model::find($mappingId);
            if ($entity) {
                $existingId =  $entity->id;
                break;
            }
        }

        if($existingId) {
            $allFiles = Document::where('documentable_id', $existingId)->get();

            if (!$allFiles) {
                return $missingList;
            }

            foreach ($allFiles as $file) {
                $label = ucwords(str_replace('_',' ',$file->document_type));
                $missingList[$label][] = [
                    'id' => $file->id,
                    'document_type' => $file->document_type,
                    'file_name' => $file->file_name,
                    'file_type' => $file->file_type
                ];
            }
        } else {
            return $existingId;
        }

        return $missingList;
    }

    public function iban4uLookupTypes(mixed $entity, array $data, array $lookupProperties)
    {
        foreach ($lookupProperties as $lookupProperty) {
            $attribute = $lookupProperty['attribute'];
            $group = $lookupProperty['group'];

            if (!array_key_exists($attribute, $data)) {
                continue;
            }

            $types = $data[$attribute];
            $attribute = $lookupProperty['resource'];
            $entity->$attribute()->delete();
            foreach ($types as $type) {
                $lookupType = LookupType::firstOrCreate([
                    'group' => $group,
                    'name' => $attribute,
                    'description' => $attribute,
                    'type' => $type
                ]);
                $entity->$attribute()->create(['lookup_type_id' => $lookupType->id]);
            }
        }
    }

    private function positionDocumentList(string $position, string $modelType, string $list, string $mapping_id)
    {
        switch (true) {
            case ($position === 'UBO'):
                $arr = config($list.'.ubo_document_required');
                break;
            case ($position === 'DIR'):
                $modelType === "N" ? $arr = config($list.'.dir_corporate_document_required') : $arr = config($list.'.dir_document_required');
                break;
            case ($position === 'SIG'):
                $arr = config($list.'.sig_document_required');
                break;
            default:
            $modelType === "N" ? $arr = config($list.'.sh_corporate_document_required') : $arr = config($list.'.sh_document_required');
        }

        $kycpRequirement = new KycpRequirement();
        $other = $kycpRequirement->otherRequirements($position, $mapping_id, $list);
        $arr = array_merge($arr, $other['required']);

        return $arr;
    }

    /**
      * Associates a LookupType record to an instance of a model that has a one-to-many
      * relationship column with it.
      *
      * @param mixed $entity An instance of some Model that has a one-to-many relationship
      * with the LookupType model ie. `Business#finxp_products`
      * @param array $data Payload data; associative array that contains a key/attribute with a
      * list of strings as its value.
      * @param array $lookupProperties An array of associative arrays that contains 'attribute'
      * and 'group' keys only.
      * @return void
      **/
    private function attachLookupTypes(mixed $entity, array $data, array $lookupProperties)
    {
        foreach ($lookupProperties as $lookupProperty) {
            $attribute = $lookupProperty['attribute'];
            $group = $lookupProperty['group'];

            if (!array_key_exists($attribute, $data)) {
                continue;
            }

            $types = $data[$attribute];
            $attribute = Str::camel(Str::remove('_', $attribute));
            foreach ($types as $type) {
                $lookupType = LookupType::firstOrCreate([
                    'group' => $group,
                    'name' => $attribute,
                    'description' => $attribute,
                    'type' => $type
                ]);
                $entity->$attribute()->create(['lookup_type_id' => $lookupType->id]);
            }
        }
    }

    /**
      * Updates an associated LookupType record to an instance of a model that has a one-to-many
      * relationship column with it.
      *
      * @param mixed $entity An instance of some Model that has a one-to-many relationship
      * with the LookupType model ie. `Business#finxp_products`
      * @param array $data Payload data; associative array that contains a key/attribute with a
      * list of strings as its value.
      * @param array $lookupProperties An array of associative arrays that contains 'attribute'
      * and 'group' keys only.
      * @return void
      **/
    private function updateLookupTypes(mixed $entity, array $data, array $lookupProperties)
    {
        foreach ($lookupProperties as $lookupProperty) {
            $attribute = $lookupProperty['attribute'];
            $group = $lookupProperty['group'];

            if (!array_key_exists($attribute, $data)) {
                continue;
            }

            $types = $data[$attribute];
            $attribute =$lookupProperty['resource'] ?? null;

            if($attribute && $entity){
                $entity->$attribute()->delete();

                if (is_array($types)) {
                    foreach ($types as $type) {
                        $lookupType = LookupType::firstOrCreate([
                            'group' => $group,
                            'name' => $attribute,
                            'description' => $attribute,
                            'type' => $type
                        ]);
                        $entity->$attribute()->create(['lookup_type_id' => $lookupType->id]);
                    }
                } else {
                    $lookupType = LookupType::firstOrCreate([
                        'group' => $group,
                        'name' => $attribute,
                        'description' => $attribute,
                        'type' => $types
                    ]);
                    $entity->$attribute()->create(['lookup_type_id' => $lookupType->id]);
                }
            }

        }
    }

    private function throwException(Throwable $e)
    {
        if ($e instanceof QueryException && $e->errorInfo[1] == 1062) {
            throw new DuplicateEntityException($e->errorInfo[2], $e->getCode());
        }

        throw $e;
    }

    private function createTaxInformation(Business $business, Request $payload)
    {
        if ($payload->has('tax_information')) {
            $taxInformation = array_merge(
                ['name' => $payload->get('name')],
                $payload->get('tax_information'),
            );
            $business->taxInformation()->create($taxInformation);
        } else {
            $taxInformation = ['name' => $payload->get('name')];
            $business->taxInformation()->create($taxInformation);
        }
    }

    private function createAddresses(Business $business, Request $payload)
    {
        if ($payload->has('registered_address')) {
            $business->registeredAddress()->create(array_merge(
                $payload->get('registered_address'),
                ['lookup_type_id' => LookupType::where('type', 'REGISTERED_BUSINESS_ADDRESS')->first()->id]
            ));
        }
        if ($payload->has('operational_address')) {
            $business->operationalAddress()->create(array_merge(
                $payload->get('operational_address'),
                ['lookup_type_id' => LookupType::where('type', 'OPERATIONAL_BUSINESS_ADDRESS')->first()->id],
            ));
        }
    }

    public function updateBusinessDetails($business, $payload)
    {
        $businessDetails = Collection::make($payload->get('business_details'))
            ->only(
                'business_purpose',
                'number_employees',
                'number_of_years',
                'share_capital',
                'number_shareholder',
                'number_directors',
                'previous_year_turnover',
                'license_rep_juris',
                'business_year_count',
                'terms_and_conditions',
                'privacy_accepted',
                'description',
                'is_part_of_group',
                'parent_holding_company',
                'parent_holding_company_other',
                'has_fiduciary_capacity',
                'has_constituting_documents',
                'is_company_licensed',
                'contact_person_name',
                'contact_person_email',
            )->toArray();

        $business->businessDetails()->update($businessDetails);

        $lookup =[
            [ 'attribute' => 'finxp_products', 'resource' => 'finxpProducts', 'group' => 'GENpweproduct' ],
            [ 'attribute' => 'industry_key', 'resource' => 'industryKey', 'group' => 'GENindustry' ],
            [ 'attribute' => 'country_of_license', 'resource' => 'countryOfLicense', 'group' => 'GENcountryoflicense' ],
            [ 'attribute' => 'country_juris_dealings', 'resource' => 'countryJurisDealings', 'group' => 'GENjurisdealing' ],
            [ 'attribute' => 'source_of_funds', 'resource' => 'sourceOfFunds', 'group' => 'GENsowFund' ],
            [ 'attribute' => 'source_of_funds_other', 'resource' => 'sourceOfFundsOther', 'group' => 'GENsowFundOther' ],
            [ 'attribute' => 'country_source_of_funds', 'resource' => 'countrySourceOfFunds', 'group' => 'GENsowFundCountry' ],
            [ 'attribute' => 'source_of_wealth', 'resource' => 'sourceOfWealth', 'group' => 'GENsowWealth' ],
            [ 'attribute' => 'source_of_wealth_other', 'resource' => 'sourceOfWealthOther', 'group' => 'GENsowWealthOther' ],
            [ 'attribute' => 'country_source_of_wealth', 'resource' => 'countrySourceOfWealth', 'group' => 'GENsowWealthCountry' ],
            [ 'attribute' => 'political_person_entity', 'resource' => 'politicalPersonEntity',  'group' => 'GENpoliticalPersonEntity' ],
            [ 'attribute' => 'usa_tax_liability', 'resource' => 'usaTaxLiability',  'group' => 'GENusaTaxLiability' ],
            [ 'attribute' => 'indicias', 'resource' => 'indicias',  'group' => 'GENindicias' ]
        ];

        $businessDetailsPayload = $payload->get('business_details');

        $this->updateLookupTypes($business->businessDetails, $businessDetailsPayload, $lookup);
    }
}
