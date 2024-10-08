<?php

namespace App\Services\KYCP\Traits;

use App\Models\Business;
use App\Models\BusinessCompositionable;
use App\Models\GeneralDocuments;
use App\Models\SeniorManagementOfficer;
use App\Models\CompanyInformation;
use App\Models\CompanyRepresentative;
use App\Exceptions\KycpResponseException;
use App\Enums\KYCEntities;
use Illuminate\Http\Response;
use App\Enums\Status;
use App\Services\KYCP\Facades\KYCP;
use Exception;

trait Application
{
    use CorporateEntities, CorporateDocuments;
    public $mainFolder = 'apply_corporate/';

    public function addFullBusinessCorporateApplicationToKYC($business, $programId)
    {
        try{
            $data = $this->formatCorporateBusinessComposition($business, $programId);
            $kycp = KYCP::addApplication($data);
            $response = $kycp->json();

            if ($kycp->serverError() || $kycp->clientError() || !$kycp->successful()) {
                throw new KycpResponseException(null, __('response.kycp_error'), Response::HTTP_BAD_REQUEST);
            }

            if (!$response['Success']) {
                throw new KycpResponseException($response, $response['Message'], Response::HTTP_BAD_REQUEST);
            } else {
                $this->uploadDocumentForCorporate($data, $response, $business->id);

                return [
                    'code' => Response::HTTP_CREATED,
                    'status' => 'success',
                    'message' => 'Successfully submitted business!',
                    'data' => $business->id,
                ];
            }

        } catch(Exception $e) {

            throw new KycpResponseException(null, $e->getMessage(), $e->getCode());
        }
    }

    private function getErrors(array $response): array
    {
        $errors = [];
        $entities = $response['Entities'] ?? [];

        if (! isset($entities)) {
            return $errors;
        }

        foreach ($entities as $value) {
            if (isset($value['Result']) && ($value['Result'] === 'Error')) {
                $message = explode(' ', $value['Message']);
                $key = trim($message[0] ?? '');

                $errors[] = [
                    'EntityType' => $value['EntityType'],
                    $key => trim($message[1] ?? ''),
                    'message' => $value['Message']
                ];
            }

            if (! empty($value['Entities'])) {
                $collectedErrors = $this->getErrors($value);

                if (is_array($collectedErrors)) {
                    foreach ($collectedErrors as $err) {
                        $errors[] = $err;
                    }
                }
            }
        }

        return $errors;
    }

    private function uploadDocumentForCorporate(array $data, array $kyc, $businessId)
    {
        foreach ($data['Entities'] as $index => $entity) {
           $this->entitiesUploadFiles($entity['EntityTypeId'], $businessId, $entity, $kyc, $index , $kyc['Uid'], $kyc['Id']);
           foreach ($entity['Entities'] as $cp => $companyRep) {
                //only the first role will upload the files
                $this->entitiesUploadFiles(
                    $companyRep['EntityTypeId'],
                    $businessId,
                    $companyRep,
                    $kyc['Entities'][$index]['Entities'][$cp],
                    $cp,
                    $kyc['Uid'],
                    $kyc['Id']
                );
            }
        }
    }

    private function entitiesUploadFiles($entityTypeId, $businessId, $entity, $kyc, $index, $uid, $applicationId)
    {
        switch ($entityTypeId) {
            case KYCEntities::COMPANY->value:
                $company = CompanyInformation::where('business_id', $businessId)->first();
                $this->finxpDocuments($company, $entity, $kyc, $index);
                $business = Business::find($businessId);
                $business->update([
                    'uid' => $uid,
                    'application_id' => $applicationId,
                    'entity_id' => ($kyc['Entities'][$index]['Id']) ?? null,
                    'kycp_status_id' => Status::INPUTTING->value,
                ]);
            break;
            case isset($entity['Fields']->GENposition) && $entity['Fields']->GENposition == 'Senior Manager Officer':
                $senior = SeniorManagementOfficer::where('id', $entity['primary_id'])->first();
                $model = $senior?->seniorManagementOfficerDocuments()->first() ?? null;
                $file = 'senior_officer_document_required';
                $path = $this->mainFolder.$entity['company_information_id'].'/senior_management_officer/';
                $this->uploadCorporateDocument($path, $file, $model,  $kyc, $uid, $applicationId);
            break;
            default:
                $representative = CompanyRepresentative::where('id', $entity['primary_id'])->first();
                $model = $representative?->companyRepresentativeDocument()->first() ?? null;
                $file = 'company_representative_document_required';
                $path = $this->mainFolder.$representative->company_information_id.'/company_representative/'.$index.'/';
                $this->uploadCorporateDocument($path, $file, $model,  $kyc, $uid, $applicationId);
            break;
        }
    }

    private function finxpDocuments($company, $entity, $kyc, $index)
    {
        $path = $this->mainFolder.$entity['company_information_id'].'/required_documents/';

        $model = $company?->generalDocuments()->get() ?? null;
        $this->companyUploadDocument($path,  $model, $kyc['Entities'][$index], $kyc['Uid'], $kyc['Id']);

        $model = $company?->iban4uPaymentAccountDocuments()->get() ?? null;
        $this->companyUploadDocument($path, $model, $kyc['Entities'][$index], $kyc['Uid'], $kyc['Id']);

        $model = $company?->creditCardProcessingDocuments()->get() ?? null;
        $this->companyUploadDocument($path, $model, $kyc['Entities'][$index], $kyc['Uid'], $kyc['Id']);

        $model = $company?->sepaDirectDebitDocuments()->get() ?? null;
        $this->companyUploadDocument($path, $model, $kyc['Entities'][$index], $kyc['Uid'], $kyc['Id']);

        $model = $company?->additionalDocuments()->get() ?? null;
        $path = $this->mainFolder.$entity['company_information_id'].'/additional_documents/';
        $this->companyUploadDocument($path, $model,  $kyc['Entities'][$index], $kyc['Uid'], $kyc['Id']);
    }
}
