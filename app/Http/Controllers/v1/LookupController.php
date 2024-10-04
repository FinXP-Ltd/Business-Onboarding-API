<?php

namespace App\Http\Controllers\v1;

use App\Abstracts\Controller as BaseController;
use App\Http\Resources\LookupableResource;
use App\Models\LookupType;
use App\Models\Business;
use App\Models\Document;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LookupController extends BaseController
{
    public function getGroupList($group, Request $request)
    {
        $selectedIds = explode(',', $request->ids);

        try {
            $lookup = $this->getEnumGroup($group, $selectedIds);

        } catch (Exception $e) {
            info($e);

            return $this->response(
                [
                    'code' => Response::HTTP_BAD_REQUEST,
                    'status' => 'failed',
                    'message' => 'Failed to retrieve lookup.',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->response(
            [
                'code' => Response::HTTP_OK,
                'status' => 'success',
                'data' => $lookup,
            ],
            Response::HTTP_OK
        );
    }

    public function getEnumsType()
    {
        return $this->response([
            [
                'group' => 'BUSINESS_POSITION',
                'label' => 'Roles/Position',
            ],
            [
                'group' => 'REGISTRATION_TYPE',
                'name' => 'Registration Type',
            ],
            [
                'group' => 'LICENSE_REP_JURIS',
                'name' => 'License Rep Juris',
            ],
            [
                'group' => 'STATUS',
                'name' => 'Business Applicaiton Statuses',
            ],
            [
                'group' => 'FINXP_PRODUCTS',
                'name' => 'FinXp Products',
            ],
            [
                'group' => 'PAYMENT_METHOD',
                'name' => 'Payment Method',
            ],
            [
                'group' => 'DOCUMENT_TYPES',
                'name' => 'Document Types',
            ],
            [
                'group' => 'OWNER_TYPES',
                'name' => 'Owner Types',
            ],
            [
                'group' => 'INDUSTRY_KEY',
                'name' => 'Industry Types',
            ],
            [
                'group' => 'SOURCE_OF_WEALTH',
                'name' => 'Source of Wealth',
            ]
        ]);
    }

    public function getEnumGroup($group, $selectedIds)
    {

        switch ($group) {
            case 'BUSINESS_POSITION':
                $position = config('bp-requirement')['entity_documents'];
                $list = array_map('strtoupper', $position);
                break;
            case 'REGISTRATION_TYPE':
                $list =  Business::REGISTRATION_TYPE;
                break;
            case 'LICENSE_REP_JURIS':
                $list = Business::LICENSE_REP_JURIS;
                break;
            case 'STATUS':
                $list = Business::STATUSES;
                break;
            case 'FINXP_PRODUCTS':
                $list = Business::FINXP_PRODUCTS;
                break;
            case 'PAYMENT_METHOD':
                $list = Business::PAYMENT_METHOD;
                break;
            case 'DOCUMENT_TYPES':
                $docTypes = config('bp-requirement')['document_types'];
                $list = array_map('strtoupper', $docTypes);
                break;
            case 'OWNER_TYPES':
                $list = Document::OWNER_TYPES;
                break;
            case 'INDUSTRY_KEY':
                $list = LookupType::where('group','GENindustry')->get();
                break;
            case 'SOURCE_OF_WEALTH':
                $list = LookupType::where('group','GENsow')->get();
                break;
            default:
                $lookup = LookupType::where('group',$group)->get();
                $list =  $selectedIds && $group ? $lookup->whereIn('lookup_id', $selectedIds) : $lookup;
                break;
        }

        return $list;
    }
}
