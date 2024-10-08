<?php

namespace App\Http\Controllers\v1;

use App\Abstracts\Controller as BaseController;
use App\Http\Requests\DocumentUploadRequest;
use App\Http\Requests\DocumentEntityListRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\KycpRequirement;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\Document\Facades\Document as DocumentFacade;
use Throwable;
use Validator;

class DocumentController extends BaseController
{
    const NOT_FOUND_MESSAGE = 'response.error.not_found';
    const MAPPING_NOT_FOUND = 'response.error.mapping_not_found';
    const SUCCESS_MESSAGE = 'response.success';

    public function __construct(private Request $request)
    {
        $this->request = $request;
    }

    public function upload(DocumentUploadRequest $documentUploadRequest): JsonResponse
    {
        $documentUploadRequest->validated();
        $payload = $documentUploadRequest->all();
        $payload['data']['document_type'] = strtolower($payload['data']['document_type']);
        $documentId = DocumentFacade::uploadDocument($payload);

        if ($documentId instanceof ModelNotFoundException) {
            return $this->response([
                'status' => 'failed',
                'code' => Response::HTTP_NOT_FOUND,
                'message' => __(self::MAPPING_NOT_FOUND, [
                    'mapping_id' => $payload['data']['mapping_id'],
                    'owner_type' => $payload['data']['owner_type'],
                ]),
            ], Response::HTTP_NOT_FOUND);
        }

        if ($documentId instanceof Throwable) {
            return $this->response([
                'status' => 'failed',
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => "Unable to upload file: {$documentId->getMessage()}",
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->response([
            'status' => 'success',
            'code' => Response::HTTP_CREATED,
            'message' => __(self::SUCCESS_MESSAGE, ['action' => 'added', 'entity' => 'document']),
            'data' => ['document_id' => $documentId],
        ], Response::HTTP_CREATED);
    }

    public function show(Document $document): JsonResponse
    {
        if (! $document) {
            return $this->response(
                [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => __(self::NOT_FOUND_MESSAGE, ['entity' => 'Document']),
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->response(DocumentResource::make($document), Response::HTTP_OK);
    }

    public function delete(Document $document): JsonResponse
    {
        if (! $document) {
            return $this->response(
                [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => __(self::NOT_FOUND_MESSAGE, ['entity' => 'Document']),
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        $document->delete();

        return $this->response([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => __(self::SUCCESS_MESSAGE, ['action' => 'suspended', 'entity' => 'document']),
        ], Response::HTTP_OK);
    }

    public function list(DocumentEntityListRequest $request)
    {
        $list = KycpRequirement::BP_REQUIREMENT;
        $businessId = $request->business_id ?? null;
        $entity =  $request->query('entity_type') ?? 'B';

        if (!in_array($entity, config($list.'.entity_documents'))) {
            return $this->response(
                [
                    'status' => Response::HTTP_NOT_FOUND,
                    'message' => __('response.documents.entity_not_found'),
                ],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->response(DocumentFacade::getDocumentList($entity, $list, $businessId), Response::HTTP_OK);
    }
}
