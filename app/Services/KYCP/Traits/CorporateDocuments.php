<?php

namespace App\Services\KYCP\Traits;

use App\Services\AzureStorage\Facades\AzureStorage;
use App\Services\KYCP\Facades\KYCP;
use Illuminate\Support\Arr;

trait CorporateDocuments
{
    public function uploadCorporateDocument($path, $section, $model, $kyc, $uid, $applicationId)
    {
        if ($model) {
            $required = $this->getRequiredList($section);
            foreach ($required as $list) {
                if($model->$list){
                    $fileName = str_replace('+', '%20', urlencode($model->$list));
                    $fileBlob = AzureStorage::downloadBlobFile($path.$fileName);

                    if($fileBlob){
                        $docId = $this->getDocId($list) ?? null;
                        if ($docId) { //without the docID it will not upload on KYCP
                            $payload = [
                                'applicationUid' => $uid,
                                'applicationId' => $applicationId,
                                'entityId' => $kyc['Id'],
                                'entityTypeId' => $kyc['EntityTypeId'],
                                'title' => $list,
                                'docTypeId' => $docId,
                                'documentFor' => 'SpecificEntityWithinApplication',
                                'verificationStatus' => 'Unverified',
                            ];

                            KYCP::uploadEntityDocument($fileBlob, $model->$list, $payload);
                        }
                   }
                }
            }
        }
    }

    public function companyUploadDocument($path, $model, $kyc, $uid, $applicationId)
    {
        if ($model) {
            $allFiles = $model->toArray();
            if ($allFiles) {
                foreach ($allFiles as $files) {
                    $fileName = str_replace('+', '%20', urlencode($files['file_name']));
                    $fileBlob = AzureStorage::downloadBlobFile($path.$fileName);
                    if ($fileBlob) {
                        $docId = $this->getDocId($files['file_type']) ?? null;
                        if ($docId) { //without the docID it will not upload on KYCP
                            $payload = [
                                'applicationUid' => $uid,
                                'applicationId' => $applicationId,
                                'entityId' => $kyc['Id'],
                                'entityTypeId' => $kyc['EntityTypeId'],
                                'title' => $files['file_type'],
                                'docTypeId' => $docId,
                                'documentFor' => 'SpecificEntityWithinApplication',
                                'verificationStatus' => 'Unverified',
                            ];

                            KYCP::uploadEntityDocument($fileBlob, $files['file_name'], $payload);
                        }
                   }
                }
            }
        }
    }

    private function getDocId($document_type)
    {
        return  Arr::get(config('corporate-requirement.assigned_keys'), $document_type);
    }

    private function getRequiredList($field)
    {
        return  config('corporate-requirement.'.$field);
    }
}
