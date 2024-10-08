<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;
use App\Exceptions\SaveException;

trait ApplyCorporateProcessData
{
    public function creditCardNumericValues($payload, $companyInformation)
    {
        $creditCardProcessingPayload = $payload->credit_card_processing;
        $creditCardProcessing = $companyInformation->companyCreditCardProcessing()->first();

        $keys = ['cb_volumes_twelve_months', 'sales_volumes_twelve_months', 'refund_twelve_months'];

        foreach ($keys as $key) {
            $value = $creditCardProcessingPayload[$key] ?? null;

            if (!is_numeric($value) && is_numeric($creditCardProcessing[$key])) {
                $creditCardProcessingPayload[$key] = $creditCardProcessing[$key];
            } else {
                $creditCardProcessingPayload[$key] = is_numeric($value) ? $value : null;
            }
        }
    }

     /**
     * Handles the create, update, and delete for
     * credit card processing countries
     * @param mixed $companyInformation
     * @param mixed $payload
     * @return void
     */
    public function processCountriesData($creditCardProcessing, $countriesData)
    {
        $existingCountries = $creditCardProcessing->companyCountries;
        $countriesToDelete = [];

        foreach ($existingCountries as $existingCountry) {
            $countryIndex = $existingCountry->index;
            if (!isset($countriesData[$countryIndex - 1])) {
                $countriesToDelete[] = $countryIndex;
            }
        }
        $creditCardProcessing->companyCountries()->whereIn('index', $countriesToDelete)->delete();

        foreach ($countriesData as $index => $countryData) {
            $countryIdToUpdate = (int)$index + 1;
            $existingCountry = $existingCountries->where('index', $countryIdToUpdate)->first();

            if ($existingCountry) {
                $existingCountry->update([
                    'index' => $countryIdToUpdate,
                    'order' => $countryIdToUpdate,
                    'countries_where_product_offered' => $countryData['countries_where_product_offered'],
                    'distribution_per_country' => $countryData['distribution_per_country']
                ]);
            } else {
                $creditCardProcessing->companyCountries()->create([
                    'index' => $countryIdToUpdate,
                    'order' => $countryIdToUpdate,
                    'countries_where_product_offered' => $countryData['countries_where_product_offered'],
                    'distribution_per_country' => $countryData['distribution_per_country']
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

    public function handleDocument($companyInformation, $payload, $documentType, $fileColumns)
    {
        if ($payload->$documentType) {
            $documentPayload = $payload->$documentType;
            $relationshipMethod = Str::camel($documentType);

            foreach ($documentPayload as $column => $value) {
                if(isset($documentPayload->$column)){
                    foreach ($payload->$documentType->$column as $value) {
                        $docs = (array)$value;

                        if ($companyInformation->$relationshipMethod) {
                            $companyInformation->$relationshipMethod()->firstOrNew([
                                'id' => Str::uuid(),
                                'file_name' => $docs[$column] ?? null,
                                'file_type' => $documentType,
                                'file_size' => $docs["{$column}_size"] ?? null,
                                'company_information_id' => $companyInformation->id
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function checkBoxCreate($business, $request, $modelName, $payload, $columnName)
    {
        DB::beginTransaction();

        try {
            $names = $request->{$payload};

            foreach ($names as $name) {
                $exists = $modelName::where([
                    'business_id' => $business->id,
                    $columnName => $name
                ])->exists();

                if (!$exists) {
                    $modelName::create([
                        'business_id' => $business->id,
                        $columnName => $name,
                        'is_selected' => 1
                    ]);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            throw new SaveException($e->getMessage(), $e->getMessage());
        }

        return $names ?? null;
    }

    public function checkboxUpdate($business, $request, $modelName, $payload, $columnName)
    {
        DB::beginTransaction();

        try {
            $names = $request->{$payload};

            if (count($names) == 0) {
                $modelName::where('business_id', $business->id)
                ->update(['is_selected' => false]);
            }

            $selectedEntities = $modelName::where([
                'business_id' => $business->id,
                'is_selected' => true
            ])->pluck($columnName)->toArray();

            $notSelected = array_diff($selectedEntities, $names);

            foreach ($names as $name) {
                $ent = $modelName::where([
                    'business_id' => $business->id,
                    $columnName => $name
                ])->first();

                if (!$ent) {
                    $modelName::create([
                        'business_id' => $business->id,
                        $columnName => $name,
                        'is_selected' => true
                    ]);
                } else if ($ent) {
                    $ent->update(['is_selected' => true]);
                }
            }

            foreach ($notSelected as $name) {
                $ent = $modelName::where([
                    'business_id' => $business->id,
                    $columnName => $name
                ])->first();
                $ent->update(['is_selected' => false]);
            }

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            throw new SaveException($e->getMessage(), $e->getCode());
        }


        return $names ?? null;
    }


    public function sourceLookup(array $payload, array $lookupProperties, $id = null)
    {
        DB::beginTransaction();

        try {
            foreach ($lookupProperties as $lookupProperty) {
                $key = $lookupProperty['attribute'];
                $secondKey = array_key_exists('second_attribute', $lookupProperty) ? $lookupProperty['second_attribute'] : null;
                if (!array_key_exists($key, $payload)) {
                    continue;
                }

                $data = $secondKey ? $payload[$key][$secondKey] : $payload[$key];

                $this->sourceLookupColumn($lookupProperty, $data, $id);
            }
            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
        }
    }

    public function sourceLookupColumn($lookupProperty, $data, $id)
    {
        $model = $lookupProperty['model'];
        $type = $lookupProperty['type'];
        $column_name_1 = $lookupProperty['column_name_1'];
        $column_name_2 = $lookupProperty['column_name_2'];
        $column_name_3 = $lookupProperty['column_name_3'];
        $column_name_4 = $lookupProperty['column_name_4'];

        if(is_array($data)) {
            $model::where($column_name_1, $id)
                ->where($column_name_2, $type)
                ->whereNotIn($column_name_3, $data)
                ->update([
                    $column_name_4 => false
            ]);

            $model::where($column_name_1, $id)
                ->where($column_name_2, $type)
                ->whereIn($column_name_3, $data)
                ->update([
                    $column_name_4 => true
            ]);

            $this->sourceData($data, $lookupProperty, $id);

        } else {

            $res = $model::where([
                $column_name_1  => $id,
                $column_name_2  => $type,
                $column_name_3  => 'Other'
            ])->first();

            if($data && $res) {
                $res->update(['other_value' => $data]);
            }
        }
    }

    public function sourceData($data, $lookupProperty, $id)
    {
        $model = $lookupProperty['model'];
        $type = $lookupProperty['type'];
        $column_name_1 = $lookupProperty['column_name_1'];
        $column_name_2 = $lookupProperty['column_name_2'];
        $column_name_3 = $lookupProperty['column_name_3'];
        $column_name_4 = $lookupProperty['column_name_4'];

        foreach($data as $d) {
            $res = $model::where([
                $column_name_1  => $id,
                $column_name_2  => $type,
                $column_name_3  => $d
            ])->first();

            if(!$res && $d) {
                $model::create([
                    $column_name_1 => $id,
                    $column_name_2  => $type,
                    $column_name_3  => $d,
                    $column_name_4 => true
                ]);
            }
        }
    }

    public function iban4uProcessData($payload, $id, $type)
    {
        return [
            'type' => $type,
            'index' => $id,
            'name' => $payload['name'],
            'country' => $payload['country']
        ];

    }

    public function secureFileName($originalFileName, $prohibitedWords) {

        $characters = config('apply.prohibited_characters');

        foreach ($prohibitedWords as $word) {
            if (preg_match("/\b$word\b/i", $originalFileName)) {
                return false;
            }
        }

        return !Str::contains($originalFileName, str_split($characters));
    }

    public function eachCompanyRepresentative($companyRepresentatives, $existingRepresentatives, $companyInformation)
    {
        foreach ($companyRepresentatives as $index => $companyRepresentative) {
            $representativeIdToUpdate = (int)$index + 1;
            $existingRepresentative = $existingRepresentatives->where('index', $representativeIdToUpdate)->first();

            $representativeData = $this->companyRep($companyRepresentative, $representativeIdToUpdate, $index);

            $residentialAddressData = $this->residentialAddress($companyRepresentative, $representativeIdToUpdate, $index);
            $identityInformationData = $this->identityInformation($companyRepresentative, $representativeIdToUpdate, $index);
            $companyRepresentativeDocumentData = $this->companyRepDocuments($companyRepresentative, $representativeIdToUpdate, $index);

            if ($existingRepresentative) {
                $existingRepresentative->update($representativeData);
                if (isset($representativeData['rolesPercentOwnership'])) {
                    $existingRepresentative->rolesPercentOwnership()->delete();
                    $existingRepresentative->rolesPercentOwnership()->createMany($representativeData['rolesPercentOwnership']);
                }
                $existingRepresentative->residentialAddress()->update($residentialAddressData);
                $existingRepresentative->identityInformation()->update($identityInformationData);
                $existingRepresentative->companyRepresentativeDocument()->update($companyRepresentativeDocumentData);
            } else {
                $newRepresentative = $companyInformation->companyRepresentative()->create($representativeData);
                if (isset($representativeData['rolesPercentOwnership'])) {
                    $newRepresentative->rolesPercentOwnership()->createMany($representativeData['rolesPercentOwnership']);
                }
                $newRepresentative->residentialAddress()->create($residentialAddressData);
                $newRepresentative->identityInformation()->create($identityInformationData);
                $newRepresentative->companyRepresentativeDocument()->create($companyRepresentativeDocumentData);

            }
        }
    }

    public function passFilter($request)
    {
        $validates = [
            'first_name',
            'surname',
            'place_of_birth',
            'date_of_birth',
            'email_address',
            'nationality',
            'citizenship',
            'phone_number',
            'phone_code',
            'street_number',
            'street_name',
            'postal_code',
            'city',
            'country',
            'id_type',
            'country_of_issue',
            'id_number',
            'document_date_issued',
            'document_expired_date',
            'high_net_worth',
            'us_citizenship',
            'politically_exposed_person'
        ];

        foreach ($validates as $validate) {
            if (isset($request[$validate]) && $request[$validate] != null) {
                return true;
            }
        }

        return false;
    }

    private function SMO($request)
    {
        return [
            'first_name' => $request['first_name'] ?? null,
            'middle_name' => $request['middle_name'] ?? null,
            'surname' => $request['surname'] ?? null,
            'place_of_birth' => $request['place_of_birth'] ?? null,
            'date_of_birth' => $request['date_of_birth'] ?? null,
            'email_address' => $request['email_address'] ?? null,
            'nationality' => $request['nationality'] ?? null,
            'citizenship' => $request['citizenship'] ?? null,
            'phone_number' => $request['phone_number'] ?? null,
            'phone_code' => $request['phone_code'] ?? null,
            'roles_in_company' => $request['roles_in_company'] ?? null,
            'required_indicator' => $request['required_indicator'] ?? null
        ];
    }

    private function smoAddress($request)
    {
        return [
            'street_number' => $request['residential_address']['street_number'] ?? null,
            'street_name' => $request['residential_address']['street_name'] ?? null,
            'postal_code' => $request['residential_address']['postal_code'] ?? null,
            'city' => $request['residential_address']['city'] ?? null,
            'country' => $request['residential_address']['country'] ?? null,
        ];
    }

    private function smoInformation($request)
    {
        $response = [];

        if(array_key_exists('identity_information', $request)) {
            $response= [
                'id_type' => $request['identity_information']['id_type'] ?? null,
                'country_of_issue' => $request['identity_information']['country_of_issue'] ?? null,
                'id_number' => $request['identity_information']['id_number'] ?? null,
                'document_date_issued' => $request['identity_information']['document_date_issued'] ?? null,
                'document_expired_date' => $request['identity_information']['document_expired_date'] ?? null,
                'high_net_worth' => $request['identity_information']['high_net_worth'] ?? null,
                'us_citizenship' => $request['identity_information']['us_citizenship'] ?? null,
                'politically_exposed_person' => $request['identity_information']['politically_exposed_person'] ?? null,
            ];
        }
        return $response;
    }

    private function smoDocuments($request) {
        return [
            'proof_of_address' => $request['senior_management_officer_document']['proof_of_address'] ?? null,
            'proof_of_address_size' => $request['senior_management_officer_document']['proof_of_address_size'] ?? null,
            'identity_document' => $request['senior_management_officer_document']['identity_document'] ?? null,
            'identity_document_size' => $request['senior_management_officer_document']['identity_document_size'] ?? null,
            'identity_document_addt' => $request['senior_management_officer_document']['identity_document_addt'] ?? null,
            'identity_document_addt_size' => $request['senior_management_officer_document']['identity_document_addt_size'] ?? null,
        ];
    }

    private function companyRep($request, $representativeIdToUpdate, $index)
    {
        $payloadCollection = collect($request)->except(['residential_address', 'identity_information']);

        $representativeData = [
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
            'iban4u_rights' => $payloadCollection->get('iban4u_rights'),
            'index' => $representativeIdToUpdate,
            'order' => (int) $index + 1,
        ];

        if (isset($request['roles']) && is_array($request['roles'])) {
            $rolesData = [];
            foreach ($request['roles'] as $index => $role) {
                $roleId = (int)$index + 1;
                $rolesData[] = [
                    'roles_in_company' => $role['roles_in_company'] ?? null,
                    'iban4u_rights' => $role['iban4u_rights'] ?? null,
                    'percent_ownership' => $role['percent_ownership'] ?? null,
                    'index' => $roleId,
                    'order' => $roleId
                ];
            }
            $representativeData['rolesPercentOwnership'] = $rolesData;
        }

        return $representativeData;
    }

    private function residentialAddress($request, $representativeIdToUpdate, $index)
    {
        return  [
            'street_number' => $request['residential_address']['street_number'] ?? null,
            'street_name' => $request['residential_address']['street_name'] ?? null,
            'postal_code' => $request['residential_address']['postal_code'] ?? null,
            'city' => $request['residential_address']['city'] ?? null,
            'country' => $request['residential_address']['country'] ?? null,
            'index' => $representativeIdToUpdate,
            'order' => (int) $index + 1,
        ];
    }

    private function identityInformation($request, $representativeIdToUpdate, $index)
    {
        return [
            'id_type' => $request['identity_information']['id_type'] ?? null,
            'country_of_issue' => $request['identity_information']['country_of_issue'] ?? null,
            'id_number' => $request['identity_information']['id_number'] ?? null,
            'document_date_issued' => $request['identity_information']['document_date_issued'] ?? null,
            'document_expired_date' => $request['identity_information']['document_expired_date'] ?? null,
            'high_net_worth' => $request['identity_information']['high_net_worth'] ?? null,
            'us_citizenship' => $request['identity_information']['us_citizenship'] ?? null,
            'politically_exposed_person' => $request['identity_information']['politically_exposed_person'] ?? null,
            'index' => $representativeIdToUpdate,
            'order' => (int) $index + 1,
        ];
    }

    private function companyRepDocuments($request, $representativeIdToUpdate, $index) {
        return [
            'proof_of_address' => $request['company_representative_document']['proof_of_address'] ?? null,
            'proof_of_address_size' => $request['company_representative_document']['proof_of_address_size'] ?? null,
            'identity_document' => $request['company_representative_document']['identity_document'] ?? null,
            'identity_document_size' => $request['company_representative_document']['identity_document_size'] ?? null,
            'identity_document_addt' => $request['company_representative_document']['identity_document_addt'] ?? null,
            'identity_document_addt_size' => $request['company_representative_document']['identity_document_addt_size'] ?? null,
            'source_of_wealth' => $request['company_representative_document']['source_of_wealth'] ?? null,
            'source_of_wealth_size' => $request['company_representative_document']['source_of_wealth_size'] ?? null,
            'index' => $representativeIdToUpdate,
            'order' => (int) $index + 1,
        ];
    }
}
