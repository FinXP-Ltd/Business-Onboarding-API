<?php

namespace App\Http\Controllers\v1;

use App\Abstracts\Controller as BaseController;
use App\Exceptions\SaveException;
use App\Http\Requests\BusinessCorporate\BusinessCorporateListRequest;
use App\Http\Requests\BusinessCreateRequest;
use App\Http\Requests\BusinessProductRequest;
use App\Http\Resources\BusinessOngoingResource;
use App\Models\Auth\User;
use App\Models\Business;
use App\Models\BusinessProduct;
use App\Models\CompanyInformation;
use App\Models\GeneralDocuments;
use App\Models\Indicias;
use App\Models\PoliticalPersonEntity;
use App\Services\BusinessCorporate\Facades\BusinessCorporate;
use App\Traits\ApplyCorporateProcessData;
use App\Traits\UploadDocumentCorporate;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\AzureStorage\Facades\AzureStorage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Enums\UserRole;
use App\Http\Requests\BusinessValidate;
use App\Http\Requests\InviteRequest;
use App\Http\Requests\RemoveComRepDocumentRequest;
use App\Http\Requests\ShareApplicationRequest;
use App\Http\Requests\UploadDocumentRequests;
use App\Http\Resources\CompanyDefaultResource;
use App\Services\BusinessCorporate\Facades\CompanyRepresentativeDocument;
use Illuminate\Support\Str;

class BusinessCorporateController extends BaseController
{

    use ApplyCorporateProcessData, UploadDocumentCorporate;

    const RESPONSE_BUSINESS_NOT_EXIST = 'response.business';

    const APPLY_CORPORATE_FOLDER = 'apply_corporate';
    const BAD_REQUEST_MESSAGE = 'Bad Request!';

    public function index(BusinessCorporateListRequest $request): JsonResponse
    {
        $user = User::whereAuth0(auth()->id())->first();

        $type = $request->input('type', null);

        switch (true) {
            case hasAuth0Role(UserRole::OPERATION()):
            case hasAuth0Role(UserRole::AGENT()):
                $queryWithType = $type === 'in-progress'
                    ? Business::where('status', Business::STATUS_OPENED)
                    : Business::whereNot('status', Business::STATUS_OPENED);

                $orderBy = $type === 'in-progress'
                    ? 'created_at'
                    : 'updated_at';

                $businessesQuery = $type
                    ? $queryWithType->orderByDesc($orderBy)
                    : Business::orderByDesc('created_at');

                $data = BusinessOngoingResource::collection($businessesQuery->get());
                break;
            case hasAuth0AnyRole([UserRole::INVITED_CLIENT(), UserRole::CLIENT()]):
                $list = Business::where('user', $user?->id)
                    ->orWhereHas('sharedApplication', function ($model) use ($user) {
                        $model->whereId($user->id);
                    })
                    ->orWhereHas('user', function ($model) use ($user) {
                        $model->whereHas('sharedInvitation', function ($model) use ($user) {
                            $model->whereParentId($user->id);
                        });
                    })
                    ->orderByDesc('created_at')
                    ->get();
                $data = BusinessOngoingResource::collection($list);
                break;
            default:
                $list =  Business::where('user', $user?->id)->orderByDesc('created_at');
                $data = BusinessOngoingResource::collection($list);
                break;
        }

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => __('response.businesses.success'),
            'data' => $data,
        ], Response::HTTP_OK);
    }

    public function create(BusinessCreateRequest $businessCreateRequest): JsonResponse
    {
        try {
            $businessId =  BusinessCorporate::createBusinessCorporate($businessCreateRequest);
        } catch (Exception $e) {
            info($e);

            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => self::BAD_REQUEST_MESSAGE,
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->response([
            'status' => Response::HTTP_CREATED,
            'message' => 'Successfully created a new company!',
            'data' => ['company_id' => $businessId],
        ], Response::HTTP_CREATED);

    }

    public function getProducts(Business $business): JsonResponse
    {
        $products = BusinessProduct::where([
            'business_id' => $business->id,
            'is_selected' => true
        ])->get();

        return $this->response([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'data' => [
                'products' => $products,
                'disabled' => $business->status === "PRESUBMIT"
            ]
        ], Response::HTTP_OK);
    }

    public function createProducts(BusinessProductRequest $request, Business $business): JsonResponse
    {
        $products = $request->products;

        foreach($products as $product) {
            $exists = BusinessProduct::where([
                'business_id' => $business->id,
                'product_name' => $product
            ])->exists();

            if(!$exists) {
                BusinessProduct::create([
                    'business_id' => $business->id,
                    'product_name' => $product,
                    'is_selected' => 1
                ]);
            }
        }

        return $this->response([
            'code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Products Created!'
        ], Response::HTTP_CREATED);
    }

    public function updateProducts(BusinessProductRequest $request, Business $business): JsonResponse
    {
        DB::beginTransaction();

       try {
           $products = $request->products;

           if(count($products) == 0) {
               throw new SaveException('Product Required',  Response::HTTP_UNPROCESSABLE_ENTITY);
           }

           $selectedProducts = BusinessProduct::where([
               'business_id' => $business->id,
               'is_selected' => true
           ])->pluck('product_name')->toArray();

           $notSelected = array_diff($selectedProducts, $products);

           foreach($products as $product) {

               $prod = BusinessProduct::where([
                   'business_id' => $business->id,
                   'product_name' => $product
               ])->first();

               if(!$prod) {
                   BusinessProduct::create([
                       'business_id' => $business->id,
                       'product_name' => $product,
                       'is_selected' => true
                   ]);
               } else if($prod) {
                   $prod->update(['is_selected' => true]);
               }
           }

           foreach($notSelected as $product) {
                $prod = BusinessProduct::where([
                    'business_id' => $business->id,
                    'product_name' => $product
                ])->first();
                $prod->update(['is_selected' => false]);
            }

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();

            if($e instanceof SaveException) {
                return $this->response([
                    'code' => $e->getCode(),
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], $e->getCode());
            }

            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => 'Product Updated.'
        ], Response::HTTP_OK);
    }

    public function invite(InviteRequest $request): JsonResponse
    {
        $response = BusinessCorporate::inviteClient($request->all());
        return $this->response($response, $response['code']);
    }

    public function shareApplication(ShareApplicationRequest $request): JsonResponse
    {
        $response = BusinessCorporate::shareApplication($request->all());
        return $this->response($response, $response['code']);
    }

    public function deleteApplication(Business $business): JsonResponse
    {
        $response = BusinessCorporate::deleteApplication($business);
        return $this->response($response, $response['code']);
    }

    public function uploadDocument(UploadDocumentRequests $uploadDocumentRequests, Business $business)
    {
        DB::beginTransaction();
        $prohibitedWords = ['write',
            'sleep',
            'insert',
            'update',
            'delete',
            'select',
            'drop',
            'truncate',
            'http',
            'https',
            '.com'
        ];
        try {
            $path = self::APPLY_CORPORATE_FOLDER . '/' . $business->companyInformation->id;

            $uploadedFile = $uploadDocumentRequests->file('file');
            if (!$this->secureFileName($uploadedFile->getClientOriginalName(), $prohibitedWords)) {
                return response()->json([
                    'status' => 'failed',
                    'code' => '422',
                    'message' => 'The file name contains prohibited words or characters.'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($uploadDocumentRequests->file('file')) {
                switch($uploadDocumentRequests->section) {
                    case 'company_representative':
                        $index = $uploadDocumentRequests->index;
                        $path = $path . '/company_representative/'.$index;
                        $data = $this->companyRepresentativeRequirements($path, $uploadDocumentRequests);
                    break;
                    case 'company_declaration':
                        $path = $path . '/declaration';
                        $data = $this->companyDeclarationRequirements($path, $uploadDocumentRequests);
                        $business->companyInformation->companyDeclaration->update($data);
                    break;
                    case 'required_documents':
                        $path = $path . '/required_documents';
                        $data = $this->requiredDocumentUpload($path, $uploadDocumentRequests);
                        $data['company_information_id'] =$business->companyInformation->id;

                        $documentType = $uploadDocumentRequests->type;
                        $relationshipMethod = Str::camel($documentType);
                        $model = $business->companyInformation->$relationshipMethod()->where($data);

                        if ($model->count() == 0) {
                            $data['id'] = Str::uuid(36);
                            $business->companyInformation->$relationshipMethod()->insert($data);
                        }
                    break;
                    case 'additional_documents':
                        $path = $path . '/additional_documents';
                        $data = $this->requiredDocumentUpload($path, $uploadDocumentRequests);
                        $data['company_information_id'] =$business->companyInformation->id;

                        $documentType = $uploadDocumentRequests->type;
                        $relationshipMethod = Str::camel($documentType);
                        $model = $business->companyInformation->$relationshipMethod()->where($data);

                        if ($model->count() == 0) {
                            $data['id'] = Str::uuid(36);
                            $business->companyInformation->$relationshipMethod()->insert($data);
                        }
                    break;
                    default:
                    break;
                }
            }

            DB::commit();
        } catch (Exception $e) {
            info($e);
            DB::rollBack();
            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => self::BAD_REQUEST_MESSAGE
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->response([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'data' => ['file_name' =>  $data['file_name']]
        ], Response::HTTP_OK);
    }

    public function removeDocument(Request $request, Business $business)
    {
       try{
        $path = self::APPLY_CORPORATE_FOLDER . '/' . $business->companyInformation->id;

        switch ($request->type) {
            case 'iban4u_payment_account_documents':
                $fileExist = $business->companyInformation->iban4uPaymentAccountDocuments;
                break;
            case 'credit_card_processing_documents':
                $fileExist = $business->companyInformation->creditCardProcessingDocuments;
                break;
            case 'sepa_direct_debit_documents':
                $fileExist = $business->companyInformation->sepaDirectDebitDocuments;
                break;
            case 'additional_documents':
                $fileExist = $business->companyInformation->additionalDocuments;
                $path = $path . '/additional_documents';
                break;
            default:
                $fileExist = $business->companyInformation->generalDocuments;
                $path = $path . '/required_documents';
                break;
        }

        $findFile = $fileExist->where('file_name', $request->file_name)->where('file_type', $request->column)->first();

        if($findFile){
            $fileName = str_replace('+', '%20', urlencode($findFile->file_name));
            $azure = AzureStorage::removeBlobFile($path.'/'.$fileName, $findFile);

            if($azure['code'] == Response::HTTP_ACCEPTED) {
                $findFile->delete();

                return $this->response([
                    'code' =>  $azure['code'],
                    'message' => $azure['message']
                ], $azure['code']);
            }
        }

        return $this->response([
            'code' => Response::HTTP_ACCEPTED,
            'message' => 'success'
        ], Response::HTTP_ACCEPTED);

       }catch (Exception $e) {
            info($e);
            DB::rollBack();
            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => self::BAD_REQUEST_MESSAGE
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function removeCompanyRepresentativeDocument(
        RemoveComRepDocumentRequest $request,
        Business $business,
    ): JsonResponse
    {
        $companyInformation = $business->companyInformation()->firstOrFail();

        $companyRepresentative = $companyInformation
            ->companyRepresentative()
            ->whereIndex((int)($request->input('index')) + 1)
            ->first();

        $response = CompanyRepresentativeDocument::setBusiness($business)
            ->setCompanyRepresentative($companyRepresentative)
            ->setProperties($request->all())
            ->deleteDocument();

        return $this->response($response['data'], $response['code']);
    }

    public function dowloadDocument(Request $request, Business $business)
    {
        try {
            $path = self::APPLY_CORPORATE_FOLDER . '/' . $business->companyInformation->id;
            $fileName = $request->file_name;
            $fileName = str_replace('+', '%20', urlencode($fileName));
            $section = $request->section;

            switch($section) {
                case 'company-representative':
                    $path = $path . '/company_representative';

                    if($request->is_senior_management) {
                        $path = $path . '/senior_management';
                    } else {
                        $path = $path . '/' . $request->index;
                    }

                    $path = $path . '/' . $fileName;
                    break;
                case 'declaration':
                    $path = $path . '/declaration/' . $fileName;
                    break;
                case 'required-documents':
                    $path = $path . '/required_documents/' . $fileName;
                    break;
                case 'additional-documents':
                    $path = $path . '/additional_documents/' . $fileName;
                    break;
                default:
                    break;
            }
            $file = AzureStorage::downloadBlobFile($path);

            if (!$file)
            {
                return response([
                    'code' => Response::HTTP_NOT_FOUND,
                    'status' => 'failed',
                    'message' => 'File not found'
                ], Response::HTTP_NOT_FOUND);
            }

        } catch (Exception $e) {

            info($e);
            return response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => self::BAD_REQUEST_MESSAGE
            ], Response::HTTP_BAD_REQUEST);
        }

        // Set the response headers for the download
        $headers = [
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response($file, Response::HTTP_OK, $headers);
    }

    public function getSection(Business $business, string $section = null): JsonResponse
    {
        if (! $business) {
            throw new SaveException(__(self::RESPONSE_BUSINESS_NOT_EXIST),  Response::HTTP_NOT_FOUND);
        }

        try {
            $company = CompanyInformation::where('business_id', $business->id)->first();
            $data = (!$section) ? new CompanyDefaultResource($company) : BusinessCorporate::getSection($company, $section);

            return $this->response([
                'code' => Response::HTTP_OK,
                'status' => 'success',
                'data' => $data
            ], Response::HTTP_OK);

        } catch(Exception $e) {
            info($e);

            if($e instanceof SaveException) {
                return $this->response([
                    'code' => $e->getCode(),
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], $e->getCode());

            }

            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => self::BAD_REQUEST_MESSAGE
            ], Response::HTTP_BAD_REQUEST);

        }
    }

    public function updatePoliticalEntityPerson(Business $business, Request $request)
    {
        try {
            $response = $this->checkboxUpdate($business, $request, PoliticalPersonEntity::class, 'entities', 'entity_name');

            return $this->response([
                'code' => Response::HTTP_OK,
                'status' => 'success',
                'data' => $response
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            info($e);

            if($e instanceof SaveException) {
                return $this->response([
                    'code' => $e->getCode(),
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], $e->getCode());

            }

            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => self::BAD_REQUEST_MESSAGE
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function createIndicias(Business $business, Request $request)
    {
        try {
            $response = $this->checkBoxCreate($business, $request, Indicias::class, 'indicias', 'indicia_name');
            return $this->response([
                'code' => Response::HTTP_OK,
                'status' => 'success',
                'data' => $response
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            info($e);

            if($e instanceof SaveException) {
                return $this->response([
                    'code' => $e->getCode(),
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], $e->getCode());

            }

            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => self::BAD_REQUEST_MESSAGE
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateIndicias(Business $business, Request $request)
    {
        try {
            $response = $this->checkboxUpdate($business, $request, Indicias::class, 'indicias', 'indicia_name');
            return $this->response([
                'code' => Response::HTTP_OK,
                'status' => 'success',
                'data' => $response
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            info($e);

            if($e instanceof SaveException) {
                return $this->response([
                    'code' => $e->getCode(),
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], $e->getCode());

            }

            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => self::BAD_REQUEST_MESSAGE
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function saveProgress(BusinessValidate $request, Business $business): JsonResponse
    {
        if ($business->status == Business::STATUS_SUBMITTED) {
            throw new SaveException('Unable to update Business. Business is already submitted',  Response::HTTP_CONFLICT);
        }

        if (! $business) {
            throw new SaveException(__(self::RESPONSE_BUSINESS_NOT_EXIST),  Response::HTTP_NOT_FOUND);
        }

        $section = $request->section;

        try {
            if ($business->status != Business::PRESUBMIT) {
                BusinessCorporate::updateSection($section, $business, $request);
            }
        } catch(Exception $e) {

            info($e);
            if($e instanceof SaveException) {
                return $this->response([
                    'code' => $e->getCode(),
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ], $e->getCode());

            }

            return $this->response([
                'code' => Response::HTTP_BAD_REQUEST,
                'status' => 'failed',
                'message' => self::BAD_REQUEST_MESSAGE
            ], Response::HTTP_BAD_REQUEST);

        }

        return $this->response([
            'status' => Response::HTTP_OK,
            'message' => 'Successfully Saved.'
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

        $documentUrls = config('apply.download_documents');

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

    public function submitApplication(Request $request, Business $business): JsonResponse
    {
        $statuses = [Business::STATUS_OPENED, Business::STATUS_INPUTTING];
        DB::beginTransaction();

        try {

            if (!in_array($business->status, $statuses) ) {
                return response()->json([
                    'code' => Response::HTTP_CONFLICT,
                    'status' => 'Already Submitted',
                    'message' => 'failed',
                ], Response::HTTP_CONFLICT);
            }

            $submit = BusinessCorporate::submit($business);
            if (($submit['status'] ?? null) === 'success') {
                $business->update(['status' => Business::PRESUBMIT]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            info($e);

            info($e->getCode());

            return $this->response([
                'code' => $e?->getCode() ?? Response::HTTP_INTERNAL_SERVER_ERROR,
                'status' => 'failed',
                'message' => $e->getMessage()
            ], $e->getCode());
        }

        return response()->json([
            'code' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Successfully submitted business!'
        ], Response::HTTP_CREATED,);
    }
}
