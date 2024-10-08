<?php

namespace App\Services\PendingApplication\Client;

use App\Models\PendingApplication;
use App\Models\Auth\User;
use App\Models\Business;
use App\Models\CompanyInformation;
use App\Services\BusinessCorporate\Facades\BusinessCorporate;
use Illuminate\Support\Facades\DB;
use App\Exceptions\SaveException;
use Illuminate\Support\Arr;
use Exception;

class Factory
{
    public function updateSection(CompanyInformation $business, $payload, $section)
    {
        switch ($section) {
            case 'company_products':
                $this->updateProducts($business, $payload['products'], $business->id);
                break;
            case 'company_representatives':
                $attributes = collect($payload)->only([
                    'company_representative',
                    'senior_management_officer',
                    'indicias',
                    'entities'
                ])->all();

                $this->companyRepresentativeSection($business, $attributes);
                break;
            case 'declaration':
            case 'declaration_agreement':
                $resource['declaration_agreement'] = [
                    'file_name' =>  $payload['file_name'],
                    'file_type' => $payload['file_type'],
                    'size' =>  $payload['size']
                ];

                $this->updateTable($business, 'declaration', $resource);
                break;
            default:

                $this->updateTable($business, $section, $payload);
                break;
        }
    }

    public function updateTable($business, $section, $payload)
    {
        DB::beginTransaction();
        try {
            $business->pendingApplication()->update([
                $section => json_encode($payload)
            ]);

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();

            throw new SaveException($e->getMessage(), $e->getMessage());
        }
    }

    public function sectionTab($section)
    {
        switch ($section) {
            case 'data-protection-and-marketing':
            case 'data-protection-marketing':
                $section = 'data-protection-marketing';
                break;
            case 'declaration_agreement':
                $section = 'declaration';
                break;
            case 'company-sepa-dd':
                $section = 'sepa_direct_debit';
                break;
            default:
                return str_replace('-', '_', $section);
                break;
        }

        return str_replace('-', '_', $section);
    }

    public function getSection($business, $section)
    {
        switch ($section) {
            case 'details':
                $response = json_decode($business->company_details);
                break;
            case 'address':
                $response = json_decode($business->company_address);
                break;
            case 'sources':
                $response = json_decode($business->company_sources);
                break;
            case 'sepa-dd':
                $response = json_decode($business->sepa_direct_debit);
                break;
            case 'iban4u':
                $response = json_decode($business->iban4u_payment_account);
                break;
            case 'acquiring-services':
                $response = json_decode($business->acquiring_services);
                break;
            case 'representative':
                $companyRep = ($business->company_representatives) ? json_decode($business->company_representatives) : [];
                $response = [
                    'company_representative' => $companyRep,
                    'senior_management_officer' => json_decode($business->senior_management_officer),
                    'entities' => json_decode($business->entities),
                    'indicias' => json_decode($business->indicias),
                    'products' => json_decode($business->company_products),
                    'disabled' => ($business->status == Business::PRESUBMIT),
                    'tax_name' => $business->tax_name
                ];
                break;
            case 'data-protection-marketing':
                $response = json_decode($business->data_protection_marketing);
                break;
            case 'declaration':
            case 'declaration_agreement':
                $response = json_decode($business->declaration);
                break;
            case 'required-documents':
                $response = json_decode($business->required_documents);
                break;
            case 'finxp-products':
                return json_decode($business->company_products);
                break;
            default:
                return true;
                break;
        }

        if ($section != 'representative') {
            if ($response) {
                $response->products = json_decode($business->company_products);
                $response->disabled= ($business->status == Business::PRESUBMIT);
            } else {
                $response['products'] = json_decode($business->company_products);
                $response['disabled'] = ($business->status == Business::PRESUBMIT);
            }
        }

        return $response;
    }

    public function submit(Business $business)
    {
        $pending = $business->companyInformation->pendingApplication;
        $companyInformation = $business->companyInformation;

        $attributes = collect($pending->getAttributes())
            ->only([
               'company_details',
                'company_address',
                'company_sources',
                'sepa_direct_debit',
                'iban4u_payment_account',
                'company_representatives',
                'senior_management_officer',
                'declaration_agreement',
                'data_protection_marketing',
                'acquiring_services',
                'required_documents'
            ])->all();

        foreach ($attributes as $section => $value) {
            $payload = json_decode($value);
            if ($payload) {
                $update = $this->submitApplication($section, $companyInformation, $payload);
                if(!$update){
                    return $update;
                }
            }
        }

        return 'success';
    }

    public function updateEntitiesIndicias($payload, $columnName)
    {
        $list = [];
        foreach ($payload as $value) {
            array_push($list, [
                $columnName.'_name' => $value,
                'is_selected' => true
            ]);
        }

        return $list;
    }

    public function companyRepresentativeSection($business, $attributes)
    {
        foreach ($attributes as $key => $payload) {
            switch ($key) {
                case 'indicias':
                    $value = $this->updateEntitiesIndicias($payload, 'indicia');
                    $field = 'indicias';
                    break;
                case 'entities':
                    $value = $this->updateEntitiesIndicias($payload, 'entity');
                    $field = 'entities';
                    break;
                case 'company_representative':
                    $value = $payload;
                    $field = 'company_representatives';
                    break;
                case 'senior_management_officer':
                    $value = $payload;
                    $field = 'senior_management_officer';
                    break;
                default:
                    $value = '';
                    $field = '';
                    break;
            }

            $this->updateTable($business, $field, $value);
        }
    }

    public function requiredDocuments($business)
    {
        DB::beginTransaction();

        try{
            $resource = BusinessCorporate::getSection($business->companyInformation, 'required-documents');
            $business->companyInformation->pendingApplication->update([
                'required_documents' => json_encode($resource)
            ]);
            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            throw new SaveException($e->getMessage(), $e->getMessage());
        }
    }
}
