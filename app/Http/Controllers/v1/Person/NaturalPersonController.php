<?php

namespace App\Http\Controllers\v1\Person;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Abstracts\Controller;
use App\Models\Person\NaturalPerson;
use App\Http\Resources\Persons\NaturalResource;
use App\Http\Requests\Person\NaturalRequest;
use App\Services\Person\Facades\Person as PersonFacade;

class NaturalPersonController extends Controller
{
    const SUCCESS_MESSAGE = 'response.success';
    const MODEL = 'natural person';

    public function store(NaturalRequest $request): JsonResponse
    {
        $personId = PersonFacade::add($request->validated());

        return $this->response([
            'status' => Response::HTTP_CREATED,
            'message' => __(self::SUCCESS_MESSAGE, ['action' => 'created', 'entity' => self::MODEL]),
            'data' => [
                'person_id' => $personId
            ],
        ], Response::HTTP_CREATED);
    }

    public function show(NaturalPerson $natural)
    {

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => __(self::SUCCESS_MESSAGE, ['action' => 'retrieved', 'entity' => self::MODEL]),
            'data' => NaturalResource::make($natural),
        ], Response::HTTP_OK);
    }

    public function update(NaturalRequest $request, NaturalPerson $natural): JsonResponse
    {
        $personId = PersonFacade::update($natural, $request->validated());

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => __(self::SUCCESS_MESSAGE, ['action' => 'updated', 'entity' => self::MODEL]),
            'data' => [
                'person_id' => $personId
            ],
        ], Response::HTTP_OK);
    }
}
