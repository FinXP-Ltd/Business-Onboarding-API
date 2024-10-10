<?php

namespace App\Http\Controllers\v1;

use App\Abstracts\Controller as BaseController;
use App\Http\Requests\BusinessCompositionRequest;
use App\Http\Requests\BusinessOnboardingRequest;
use App\Http\Requests\InviteRequest;
use App\Http\Requests\BusinessValidate;
use App\Http\Resources\BetterPaymentResource;
use App\Models\Declaration;
use App\Models\GeneralDocuments;
use Illuminate\Http\Request;
use App\Models\Business;
use App\Models\BusinessDetail;
use App\Models\BusinessComposition;
use Illuminate\Http\Response;
use App\Models\KycpRequirement;
use App\Enums\Status;
use App\Http\Resources\BusinessOngoingList;
use App\Enums\UserRole;
use App\Services\KYCP\Facades\KYCP;
use App\Services\Business\Facades\Business as BusinessFacade;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Models\Auth\User;
use App\Models\SeniorManagementOfficer;
use App\Services\BusinessCorporate\Facades\BusinessCorporate;
use App\Services\LocalUser\Facades\LocalUser;
use Exception;

class BusinessController extends BaseController
{

    const RESPONSE_BUSINESS_NOT_EXIST = 'response.business';
    public Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function index(Request $request): JsonResponse
    {
        $user = User::whereAuth0(auth()->id())->first();

        $list = Business::whereUser($user?->id)->get();
        $data = BusinessOngoingList::collection($list);

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => __('response.businesses.success'),
            'data' => $data,
        ], Response::HTTP_OK);
    }


    public function create(BusinessOnboardingRequest $businessCreateRequest): JsonResponse
    {
        $businessCreateRequest->validated();

        try {
            $businessId = BusinessFacade::createBusiness($businessCreateRequest);
        } catch (Exception $e) {

            info($e);

            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => 'Bad Request!',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->response([
            'status' => Response::HTTP_CREATED,
            'message' => 'Successfully created a new company!',
            'data' => ['company_id' => $businessId],
        ], Response::HTTP_CREATED);
    }

    public function update(BusinessOnboardingRequest $businessCreateRequest, Business $business): JsonResponse
    {
        $businessCreateRequest->validated();

        if ($business->status == Business::STATUS_SUBMITTED) {
            return $this->response(
                ['code' => Response::HTTP_CONFLICT,
                'status' => 'failed', 'message' => 'Unable to update Business. Business is already submitted'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $businessId = BusinessFacade::updateBusiness($business, $businessCreateRequest);

        if (! $business) {
            return $this->response(
                ['status' => Response::HTTP_NOT_FOUND, 'message' => __(self::RESPONSE_BUSINESS_NOT_EXIST)],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => 'Successfully Updated!',
            'data' => ['company_id' => $businessId],
        ], Response::HTTP_OK);
    }

    public function downloadDeclaration(Business $business)
    {
        $headers = [
            'content-type' => 'application/pdf',
        ];

        return response()->json(['fileUrl' => config('apply.declaration_url')], Response::HTTP_OK, $headers);
    }

    public function downloadDocuments(GeneralDocuments $generalDocuments, $documentType, $columnName)
    {
        if (!in_array($documentType, $generalDocuments->getValidDocumentTypes())) {
            return response()->json([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => 'Invalid document type',
            ], Response::HTTP_BAD_REQUEST);
        }

        $documentUrls = $generalDocuments->getDocumentUrls();

        if (isset($documentUrls[$documentType]) && isset($documentUrls[$documentType][$columnName])) {
            $fileUrl = $documentUrls[$documentType][$columnName];

            $headers = [
                'content-type' => 'application/pdf',
            ];

            return response()->json(['fileUrl' => $fileUrl], Response::HTTP_OK, $headers);
        } else {
            return response()->json(['message' => 'Invalid column name'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function show(Business $business): JsonResponse
    {
        if (! $business) {
            return $this->response(
                ['status' => Response::HTTP_NOT_FOUND, 'message' => __(self::RESPONSE_BUSINESS_NOT_EXIST)],
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->response(BetterPaymentResource::make($business), Response::HTTP_OK);
    }

    public function createComposition(BusinessCompositionRequest $businessCompositionRequest): JsonResponse
    {
        $businessCompositionRequest->validated();
        $total_shares = BusinessComposition::where('business_id', $businessCompositionRequest->business_id)->get()->sum('voting_share');
        $added_shares = $total_shares + $businessCompositionRequest->get('voting_share');
        $lowestVotingShare =  BusinessComposition::latest()->first()?->where('business_id', $businessCompositionRequest->business_id)->where('voting_share', '!=', 0)->min('voting_share');
        $requestVotingShare = $this->request->input('voting_share');

        if ($added_shares > 100) {
            return $this->response([
                'code' => Response::HTTP_CONFLICT,
                'status' => 'failed',
                'message' => __('response.exceed'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($requestVotingShare > $lowestVotingShare && $lowestVotingShare !== null) {
            return $this->response([
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'status' => 'failed',
                'message' => __('response.error.voting_share_restriction'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $businessCompositionId = BusinessFacade::createBusinessComposition($businessCompositionRequest);
        return $this->response([
            'code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Successfully added business compositions!',
            'data' => ['business_composition_id' => $businessCompositionId],
        ], Response::HTTP_CREATED);
    }

    public function updateComposition(BusinessCompositionRequest $businessCompositionRequest, BusinessComposition $businessComposition): JsonResponse
    {
        $businessId = BusinessComposition::where('id', $businessComposition->id)->value('business_id');
        $businessIdpayload = $this->request->input('business_id');
        $businessCompositionId = BusinessComposition::where('id', $businessComposition->id)->first()->id;
        $allCompositions = BusinessComposition::where('business_id',$businessId)->get()->pluck('id')->toArray();
        $index =  array_search($businessCompositionId, $allCompositions);

        $previousBusinessCompositionId =$allCompositions[$index-1] ?? null ;
        $previousVotingShare = BusinessComposition::where('id', $previousBusinessCompositionId)->where('business_id', $businessId)->where('voting_share', '!=', 0)->first()?->voting_share;
        $nextBusinessCompositionId = $allCompositions[$index+1] ?? null ;
        $nextVotingShare = BusinessComposition::where('id', $nextBusinessCompositionId)->where('business_id', $businessId)->where('voting_share', '!=', 0)->first()?->voting_share;
        $requestVotingShare = $this->request->input('voting_share');

        switch (true) {
            case ($businessId !== $businessIdpayload):
                $code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $response = [
                    'code' => $code,
                    'status' => 'failed',
                    'message' => __('response.error.business_id_restriction'),
                ];
                break;
            case ($requestVotingShare > $previousVotingShare && $previousVotingShare !== null):
                $code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $response = [
                    'code' => $code,
                    'status' => 'failed',
                    'message' => __('response.error.voting_share_restriction'),
                ];
                break;
            case ($requestVotingShare < $nextVotingShare && $requestVotingShare !== null):
                $code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $response = [
                    'code' => $code,
                    'status' => 'failed',
                    'message' => __('response.error.voting_share_update_restriction'),
                ];
                break;
            default:
                $businessCompositionRequest->validated();
                $businessCompositionId = BusinessFacade::updateComposition($businessComposition, $businessCompositionRequest);
                $code = Response::HTTP_OK;
                $response = [
                    'code' => $code,
                    'status' => Response::HTTP_OK,
                    'message' => 'Business Composition Successfully Updated!',
                    'data' => ['business_composition_id' => $businessCompositionId],
                ];
        }

        return response()->json($response, $code);
    }

    public function submitApplication(Request $request, Business $business): JsonResponse
    {
        $list = KycpRequirement::BP_REQUIREMENT;
        if($request->has('corporate_saving') && $business->status == Business::STATUS_OPENED ||
            $request->has('corporate_saving') && $business->status == Business::STATUS_INPUTTING ){

            $business->update(['status' => Business::PRESUBMIT]);
            return $this->response([
                'code' => Response::HTTP_OK,
                'status' => 'success',
                'message' => __('response.submit.presubmit')
            ], Response::HTTP_OK);
        }
        return $this->submit($business, $list);
    }


    public function submit(Request $request, Business $business): JsonResponse
    {
        $list = KycpRequirement::BP_REQUIREMENT;
        $required_share = BusinessComposition::where('voting_share', '>=', 25)->where('business_id', $business->id)->first()?->voting_share;
        $no_of_directors = BusinessDetail::where('business_id', $business->id)->first()->number_directors;
        $no_of_shareholders = BusinessDetail::where('business_id', $business->id)->first()->number_shareholder;
        $bc_of_directors = BusinessFacade::findingPosition($business, BusinessComposition::DIRECTOR);
        $bc_of_shareholders = BusinessFacade::findingPosition($business, BusinessComposition::SHAREHOLDER);
        $person_responsible = BusinessComposition::where('person_responsible', '=', 'true')->where('business_id', $business->id)->first()?->person_responsible;
        $no_document = BusinessFacade::documentCheck($business, $list);
        $no_of_directors = is_null($no_of_directors) ? 0 : $no_of_directors;

        switch (true) {
            case (!$required_share && !$person_responsible):
                $code = Response::HTTP_CONFLICT;
                $response = [
                    'code' => $code,
                    'status' => 'failed',
                    'message' => __('response.submit.incomplete'),
                ];
                break;
            case ($no_of_directors > $bc_of_directors || $no_of_shareholders > $bc_of_shareholders):
                $code = Response::HTTP_CONFLICT;
                $response = [
                    'code' => $code,
                    'status' => 'failed',
                    'message' => __('response.submit.composition', [
                        'no_of_directors' =>  ($no_of_directors - $bc_of_directors),
                        'no_of_shareholders' =>  ($no_of_shareholders - $bc_of_shareholders)
                    ]),
                ];
                break;
            case (!empty($no_document)):
                $code = Response::HTTP_CONFLICT;
                $response = [
                    'code' => $code,
                    'status' => 'failed',
                    'message' => __('response.submit.documents'),
                    'missing documents' => $no_document,
                ];
                break;
            case ($business->status === 'SUBMITTED'):
                $code = Response::HTTP_CONFLICT;
                $response = [
                    'code' => $code,
                    'status' => 'failed',
                    'message' => __('response.submit.submitted'),
                ];
                break;
            default:
                $programId = LocalUser::getUserProgramId();
                $code = Response::HTTP_CREATED;
                $response = KYCP::addFullBusinessApplicationToKYC($business, $programId);
            }

            if ($response['status'] == 'success') {
                $business->update(['status' => Business::STATUS_SUBMITTED]);
            }

        return response()->json($response, $code);
    }

    public function withdraw(Business $business): JsonResponse
    {
        if ($business->status == Business::STATUS_WITHDRAWN) {
            return $this->response([
                'code' => Response::HTTP_CONFLICT,
                'status' => 'failed',
                'message' => __('response.error.withdrawn_error')
            ], Response::HTTP_CONFLICT);
        }

        $business->update(['status' => Business::STATUS_WITHDRAWN]);

        if ($business->kycp_status_id !== null) {
            $business->update(['kycp_status_id' => Status::DORMANT_WITHDRAWN->value]);
        }

        if ($business->status == Business::STATUS_WITHDRAWN && $business->uid) {
            KYCP::updateStatus($business->uid, Status::DORMANT_WITHDRAWN->value);
        }

        return $this->response([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => __('response.withdrawn.withdrawn_success'),
            'data' => ['company_id' => $business->id],
        ], Response::HTTP_OK);
    }

    public function draft(Business $business): JsonResponse
    {
        if ($business->status !== Business::STATUS_WITHDRAWN) {
            return $this->response([
                'code' => Response::HTTP_CONFLICT,
                'status' => 'failed',
                'message' => __('response.error.draft_error')
            ], Response::HTTP_CONFLICT);
        }

        $business->update(['status' => Business::STATUS_DRAFT]);

        return $this->response([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => __('response.draft.draft_success'),
            'data' => ['company_id' => $business->id],
        ], Response::HTTP_OK);
    }

    public function getDocumentsList($mappingId): JsonResponse
    {
        $documents = BusinessFacade::getDocumentList($mappingId);

        if (!is_array($documents)) {
            return $this->response([
                'status' => Response::HTTP_NOT_FOUND,
                'message' =>  __('response.documents.entity_not_found'),
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->response([
            'data' => $documents
        ], Response::HTTP_OK);
    }

    public function delete(BusinessComposition $businessComposition): JsonResponse
    {
        $businessComposition->delete();

        return $this->response([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => __('response.success', ['action' => 'deleted', 'entity' => 'composition'])
        ], Response::HTTP_OK);
    }
}
