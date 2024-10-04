<?php

namespace App\Http\Controllers\v1;

use DB;
use App\Abstracts\Controller as BaseController;
use App\Http\Requests\AgentCompany\CreateAgentCompanyRequest;
use App\Http\Resources\AgentCompanyResource;
use App\Models\AgentCompany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Throwable;

class AgentCompanyController extends BaseController
{
    public function __construct(private AgentCompany $agentCompanies)
    {

    }

    public function index(): AnonymousResourceCollection
    {
        return AgentCompanyResource::collection(AgentCompany::get());
    }

    public function store(CreateAgentCompanyRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $company = $this->agentCompanies->create($request->all());

            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();

            info($exception);

            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => 'Bad Request!',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->response([
            'status' => Response::HTTP_CREATED,
            'message' => 'Successfully created a new agent company!',
            'data' => ['agent_company' => $company],
        ], Response::HTTP_CREATED);
    }
}
