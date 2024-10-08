<?php

namespace App\Http\Controllers\v1;

use App\Abstracts\Controller as BaseController;
use App\Models\Company;
use App\Http\Requests\CompanyCreateRequest;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Resources\CompanyResource;
use App\Services\Company\Facades\Company as CompanyFacade;

class CompanyController extends BaseController
{
    public function index(): JsonResponse
    {
        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => __('response.businesses.success'),
            'data' => CompanyResource::collection(Company::all())
        ], Response::HTTP_OK);
    }
    
    public function store(CompanyCreateRequest $companyCreateRequest): JsonResponse
    {
        $companyId = CompanyFacade::add($companyCreateRequest->validated());

        return $this->response([
            'status' => Response::HTTP_CREATED,
            'message' => 'Successfully created a new company!',
            'data' => ['company_id' => $companyId],
        ], Response::HTTP_CREATED);
    }
    
    public function update(CompanyCreateRequest $companyCreateRequest, Company $company): JsonResponse
    {
        $companyId = CompanyFacade::update($company, $companyCreateRequest->validated());
        
        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => 'Successfully Updated!',
            'data' => ['company_id' => $companyId],
        ], Response::HTTP_OK);
    }
    
    public function show(Company $company): JsonResponse
    {
        return $this->response(CompanyResource::make($company), Response::HTTP_OK);
    }
    
    public function delete(Company $company): JsonResponse
    {
        $company->delete();
        
        return $this->response([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => __('response.success', ['action' => 'deleted', 'entity' => 'company'])
        ], Response::HTTP_OK);
    }
}
