<?php

namespace App\Services\Document\Client;

use App\Models\Business;
use App\Models\Document;
use App\Models\KycpRequirement;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\AzureStorage\Facades\AzureStorage;
use InvalidArgumentException;
use Throwable;

class Factory
{
    public function __construct(
        private Document $document,
        private Business $business,
        private NaturalPerson $naturalPerson,
        private NonNaturalPerson $nonNaturalPerson,
        private KycpRequirement $kycpRequirement,
    ) {
        $this->document = $document;
        $this->business = $business;
        $this->naturalPerson = $naturalPerson;
        $this->nonNaturalPerson = $nonNaturalPerson;
        $this->kycpRequirement = $kycpRequirement;
    }

    public function uploadDocument(array $payload): string | Throwable
    {
        DB::beginTransaction();

        try {
            $data = $payload['data'];
            $ownerType = $data['owner_type'];
            $file = $payload['file'];
            $list = KycpRequirement::BP_REQUIREMENT;
            $tsNow = Carbon::now()->timestamp;
            $fileName = "{$ownerType}_{$tsNow}.{$file->extension()}";

            $data['file_name'] = $fileName;
            $data['file_type'] = $file->extension();
            $mimeType = $file->getMimeType();

            $folderName = null;
            $newDocument = null;

            if ($ownerType === $this->document::BUSINESS_TYPE) {
                $newDocument = $this->attachDocument($data, $this->business, $list);
                $folderName = $this->document::BUSINESS_FOLDER;
            }

            if ($ownerType === $this->document::NATURAL_PERSON_TYPE) {
                $newDocument = $this->attachDocument($data, $this->naturalPerson, $list);
                $folderName = $this->document::NATURAL_PERSON_FOLDER;
            }

            if ($ownerType === $this->document::NON_NATURAL_PERSON_TYPE) {
                $newDocument = $this->attachDocument($data, $this->nonNaturalPerson, $list);
                $folderName = $this->document::NON_NATURAL_PERSON_FOLDER;
            }

            if (! $newDocument) {
                throw new InvalidArgumentException("Unknown owner type: {$ownerType}");
            }

            AzureStorage::uploadBlob($file, $fileName, $mimeType, $folderName);

            DB::commit();
            return $newDocument->id;
        } catch (Throwable $e) {
            DB::rollBack();
            return $e;
        }
    }

    public function getDocumentList($entity, $list, $businessId)
    {
        switch ($entity) {
            case "DIR":
                $array_required = config($list.'.dir_document_required');
                $array_optional = config($list.'.dir_document_optional');
                break;
            case "DIR_CORPORATE":
                $array_required = config($list.'.dir_corporate_document_required');
                $array_optional = config($list.'.dir_corporate_document_optional');
                break;
            case "UBO":
                $array_required = config($list.'.ubo_document_required');
                $array_optional = config($list.'.ubo_document_optional');
                break;
            case "SH":
                $array_required = config($list.'.sh_document_required');
                $array_optional = config($list.'.sh_document_optional');
                break;
            case "SH_CORPORATE":
                $array_required = config($list.'.sh_corporate_document_required');
                $array_optional = config($list.'.sh_corporate_document_optional');
                break;
            case "SIG":
                $array_required = config($list.'.sig_document_required');
                $array_optional = config($list.'.sig_document_optional');
                break;
            case ($businessId && $entity == 'B' && in_array($this->kycpRequirement->findingBusinessType($businessId), $this->business::SOLE_TRADER)):
                $array_required = config($list.'.sole_trader_document_required');
                $array_optional = config($list.'.sole_trader_document_optional');
                break;
            default:
                $array_required = config($list.'.business_document_required');
                $array_optional = config($list.'.business_document_optional');
                break;
        }

        return [
            $entity => [
                'required' => $array_required,
                'optional' => $array_optional
            ]
        ];
    }

    private function attachDocument(array $data, Model $model, string $list): Document
    {
        $instance = $model::findOrFail($data['mapping_id']);

        $newDocument = $instance->documents()->create($data);

        $newDocument->kycpRequirement()->create([
            'document_type' => $data['document_type'],
            'entity' => $data['owner_type'],
            'entity_type' => $this->kycpRequirement->entityType($data['mapping_id'], $data['owner_type']),
            'kycp_key' => $this->kycpRequirement->kycpKey($data['document_type'], $list),
            'required' => $this->kycpRequirement->required($data['document_type'], $data['mapping_id'], $data['owner_type'], $list)
        ]);

        return $newDocument;
    }
}
