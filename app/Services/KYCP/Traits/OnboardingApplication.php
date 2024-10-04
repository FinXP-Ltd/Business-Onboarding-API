<?php

namespace App\Services\KYCP\Traits;

use App\Models\Business;
use App\Models\BusinessCompositionable;
use App\Exceptions\KycpResponseException;
use App\Enums\KYCEntities;
use Illuminate\Http\Response;
use App\Enums\Status;
use App\Services\KYCP\Facades\KYCP;
use Exception;

trait OnboardingApplication
{
    public function addFullBusinessApplicationToKYC($business, $programId)
    {
        $data = $this->formatBusinessComposition($business, $programId);
        $kycp = $this->addApplication($data);

        $response = $kycp->json();

        if (! $response['Success'] && $response['Result'] === 'Error') {
            return $response = [
                'code' => Response::HTTP_CONFLICT,
                'status' => 'failed',
                'message' => $response['Message'],
                'errors' => $this->getErrors($response)
            ];
        }

        if ($response['Success'] && $response['Result'] == 'Ok') {

            $this->updateApplicationUid($data, $response, $business->id);

            return $response = [
                'code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' => 'Successfully submitted business!',
                'data' => $business->id,
            ];
        }

        if ($kycp->serverError() || $kycp->clientError() || !$kycp->successful()) {
            throw new KycpResponseException(null, __('response.kycp_error'), Response::HTTP_BAD_REQUEST);
        }
    }

    private function updateApplicationUid(array $data, array $kyc, $businessId)
    {
        foreach ($kyc['Entities'] as $index => $entity) {

            $this->entitiesFiles(
                KYCEntities::COMPANY->value,
                $businessId,
                $data['Entities'][0],
                $kyc['Uid'],
                $kyc['Id'], //application_id
                $kyc['Entities'][$index]['Id'],  //entity_id
                 $data['Entities'][0]['model_type']
            );

            foreach ($entity['Entities'] as $index => $person) {
                //only the first role will upload the files
                $this->entitiesFiles(
                    $person['EntityTypeId'],
                    $businessId,
                    $data['Entities'][0]['Entities'][$index],
                    $kyc['Uid'],
                    $kyc['Id'], //application_id
                    $person['Id'],  //entity_id
                    $data['Entities'][0]['Entities'][$index]['model_type']
                );
            }
        }
    }

    private function entitiesFiles($entityTypeId, $businessId, $entity,  $uid, $applicationId, $entityId, $modelType)
    {
        if ($entityTypeId == KYCEntities::COMPANY->value) {
            $app = Business::find($businessId);
            $app->update([
                'uid' => $uid,
                'application_id' => $applicationId,
                'entity_id' => $entityId,
                'kycp_status_id' => Status::INPUTTING->value,
            ]);
            $id = $businessId;
        } else {
            $app = BusinessCompositionable::where([
                'business_compositionable_id' => $entity['business_compositionable_id'],
                'business_composition_id' => $entity['business_composition_id'],
            ])->first();
            $app->update([
                'uid' => $uid,
                'application_id' => $applicationId,
                'entity_id' => $entityId,
                'entity_type_id' => $entityTypeId
            ]);
            $id = $entity['business_compositionable_id'];
        }

        $this->uploadDocument($id, $app, $modelType);
    }
}
