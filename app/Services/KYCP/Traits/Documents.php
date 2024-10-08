<?php

namespace App\Services\KYCP\Traits;

use App\Exceptions\KycpResponseException;
use App\Models\Document;
use App\Models\KycpRequirement;
use Illuminate\Http\Response;
use App\Services\AzureStorage\Facades\AzureStorage;
use App\Enums\Entity;
use App\Enums\KYCEntities;

trait Documents
{
    public function uploadDocument($id, $entity, $modelType)
    {
        $allDocuments = Document::where('documentable_id', $id)->where('owner_type', $modelType)->get();

        foreach ($allDocuments as $document) {

            $fileBlob = AzureStorage::downloadBlobFile($document->getFileName());

            if($fileBlob && $document->getFileName()) {
                $docId = $document->kycpRequirement()->first()->kycp_key ?? null;
                $entityTypeId = ($modelType == Entity::BUSINESS->value) ? KYCEntities::COMPANY->value : $entity['entity_type_id'];

                $payload = [
                    'applicationUid' => $entity['uid'],
                    'applicationId' => $entity['application_id'],
                    'entityId' => $entity['entity_id'],
                    'entityTypeId' => $entityTypeId,
                    'title' => $document->file_name,
                    'docTypeId' => $docId,
                    'documentFor' => 'SpecificEntityWithinApplication',
                    'verificationStatus' => 'Unverified',
                ];

                $kycp = $this->uploadEntityDocument($fileBlob, $document->file_name, $payload, $document->file_type);
                $response = $kycp->json();

                if ($response['Result'] != 'Ok') {
                    info($response);
                }

                if ($kycp->serverError() || $kycp->clientError() || !$kycp->successful()) {
                    throw new KycpResponseException($entity['entity_id'], _('response.kycp_error'), Response::HTTP_BAD_REQUEST);
                }
            }
        }
    }
}
