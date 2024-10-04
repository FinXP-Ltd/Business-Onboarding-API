<?php

namespace App\Http\Controllers\v1\Person;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Abstracts\Controller;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Http\Requests\Person\NonNaturalRequest;
use App\Http\Resources\Persons\NonNaturalResource;
use App\Services\NonPerson\Facades\NonPerson as NonPersonFacade;

class NonNaturalPersonController extends Controller
{
    const SUCCESS_MESSAGE = 'response.success';
    const MODEL = 'non natural person';

    public function store(NonNaturalRequest $request): JsonResponse
    {
        $nonPersonId = NonPersonFacade::add($request->validated());

        return $this->response([
            'status' => Response::HTTP_CREATED,
            'message' => __(self::SUCCESS_MESSAGE, ['action' => 'created', 'entity' => self::MODEL]),
            'data' => [
                'non_natural_person_id' => $nonPersonId
            ],
        ], Response::HTTP_CREATED);
    }

    public function show(NonNaturalPerson $nonNatural)
    {
        return $this->response([
            'status' => Response::HTTP_OK,
            'message' =>__(self::SUCCESS_MESSAGE, ['action' => 'retrieved', 'entity' => self::MODEL]),
            'data' => NonNaturalResource::make($nonNatural),
        ], Response::HTTP_OK);
    }

    public function update(NonNaturalRequest $request, NonNaturalPerson $nonNatural): JsonResponse
    {
        $nonPersonId = NonPersonFacade::update($nonNatural, $request->validated());

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => __(self::SUCCESS_MESSAGE, ['action' => 'updated', 'entity' => self::MODEL]),
            'data' => [
                'non_natural_person_id' => $nonPersonId
            ],
        ], Response::HTTP_OK);
    }
}
