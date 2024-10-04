<?php

namespace App\Services\KYCP\Traits;

use App\Models\BusinessCorporate\Field;
use App\Http\Resources\CompanyDefaultResource;
use App\Models\CompanyInformation;
use App\Http\Resources\BetterPaymentResource;
use App\Enums\BOEntities;
use App\Enums\KYCEntities;
use Illuminate\Support\Carbon;
use App\Models\LookupType;
use App\Models\Business;
use App\Models\Auth\User;
use League\ISO3166\ISO3166;
use Auth;

trait CorporateEntities
{
    use Entities;
    public $selectedProducts;

    public function formatCorporateEntities($data, $entityId, $programId): array
    {
        $fields = Field::getByProgramIdAndEntityId($programId, $entityId);
        $keys = [];

        if (count($fields) != 0) {
            $this->selectedProducts = isset($data['products']) ? array_column($data['products'], 'product_name') : [];

            foreach ($fields as $field) {
                $keys[$field['key']] = $this->mapCorporateTable($field, $data) ?? null;
            }
        } else {
            //Default Entities: at least 1 requires
            $keys = $this->defaultEntity($entityId, $data);
        }

        $keys = $this->documentEntitiesFormat($keys);

        return [
            'EntityTypeId' => $entityId,
            'primary_id' => isset($data['primary_id']) ? $data['primary_id'] : null,
            'company_information_id' => isset($data['company_information_id']) ? $data['company_information_id'] : null,
            'EntityType' =>  KYCEntities::from($entityId)->word(),
            'Fields' => (object) $keys,
            'Entities' => []
        ];
    }

    public function finxpProducts($label)
    {
        $productExist = in_array($label, $this->selectedProducts);
        return $productExist ? 'Yes': 'No';
    }

    private function addToSameEntities($data, $entityId, $programId)
    {
        $fields = Field::getByProgramIdAndEntityId($programId, $entityId);
        $keys = [];

        if (count($fields) != 0) {
            foreach ($fields as $field) {
                $keys[$field['key']] = $this->mapCorporateTable($field, $data) ?? null;
            }
        } else {
            //Default Entities: at least 1 requires
            $keys = $this->defaultEntity($entityId, $data);
        }

        $keys = $this->documentEntitiesFormat($keys);

        return [
            'EntityTypeId' => $entityId,
            'EntityType' =>  KYCEntities::from($entityId)->word(),
            'Fields' => (object) $keys,
        ];
    }

    public function formatCorporateBusinessComposition($business, $programId)
    {
        $company = CompanyInformation::where('business_id', $business->id)->first();
        $businesses = json_decode(CompanyDefaultResource::make($company)->toJson(), true);

        $businessData = $this->formatCorporateEntities($businesses, KYCEntities::COMPANY->value, $programId);
        $businessData['company_information_id'] = $company->id;
        $businessData['primary_id'] = $business->id;

        $data = [];
        array_push($data, $businessData);

        //COMPANY REPRESENTATIVES with Multiple roles
        foreach ($businesses['company_representative'] as $entity) {
            if($entity['roles']){
                foreach ($entity['roles'] as $key => $roles) {
                    $entityId = KYCEntities::corporateEntities($roles['roles_in_company']);

                    if ($key == 0 ) {
                        $cp =0;
                        $app = $this->formatCorporateEntities($entity, $entityId, $programId);
                        $app['Fields']->GENposition = $roles['roles_in_company'];
                        $app['company_information_id'] = $company->id;
                        $app['primary_id'] = $entity['id'] ?? null;
                        array_push($data[0]['Entities'], $app);
                    } else {
                        $sameEntities = $this->addToSameEntities($entity, $entityId, $programId);
                        $sameEntities['Fields']->GENposition = $roles['roles_in_company'];
                        array_push($data[0]['Entities'][$cp]['Entities'], $sameEntities);
                    }
                }
                $cp++;
            }
        }
        //SENIOR MANAGEMENT OFFICER
        if($businesses['senior_management_officer'] && $businesses['senior_management_officer']['first_name']
            && $businesses['senior_management_officer']['surname']){

            $entityId = KYCEntities::UBO->value;
            $app = $this->formatCorporateEntities($businesses['senior_management_officer'], $entityId, $programId);
            $app['Fields']->GENposition = 'Senior Manager Officer';
            $app['company_information_id'] = $company->id;
            $app['primary_id'] = $businesses['senior_management_officer']['id'] ?? null;
            array_push($data[0]['Entities'], $app);
        }

        return [
            "ProgramId" => $programId,
            "Entities" => $data
        ];
    }

    private function documentEntitiesFormat($all)
    {
        $resource = $this->getCorporateResources();
        if(array_key_exists('GENDocComplex', $all)){
            $all['GENDocComplex'] = [];
            $new = [];

            $list = $resource['GENDocComplex'];

            foreach ($list as $value) {
                if(isset($all[$value])){
                    $new = array_merge($new, [$value => $all[$value]]);
                    unset($all[$value]);
                }
            }

            if(isset($new['GENdoctype'])){
                array_push( $all['GENDocComplex'], $new);
            }
        }

        return $all;
    }

    public function mapCorporateTable($field, $data)
    {
        $value = null;
        $checkValue = json_decode($field['mapping_table']);

        if(is_array($checkValue)){
            foreach($checkValue as $map){
                $value .= $this->trimValue($map, $data).' ';
            }
        }else{
            $value = $this->trimValue($field['mapping_table'], $data);
        }

        return  $this->formatCorporateValue($value, $field);
    }

    public function trimValue($mappingTable, $data)
    {
        $value = null;

        if (str_contains($mappingTable, '.')) {
            $index = array_map('trim', explode('.', $mappingTable));
            if (isset($data[$index[0]][$index[1]])) {
                if(substr_count($mappingTable, '.') == 2 && isset($data[$index[0]][$index[1]][$index[2]])){
                    $value = $data[$index[0]][$index[1]][$index[2]];
                }else{
                    $value = $data[$index[0]][$index[1]];
                }
            }
        } else {
            if (isset($data[$mappingTable])) {
                $value = $data[$mappingTable];
            }
        }

        return $value;
    }

    public function formatCorporateValue($value, $field)
    {
        $format = null;

        switch ($field['type']) {
            case Field::COT_DATE:
                $format = (bool)strtotime($value) ? Carbon::parse($value)->format('m/d/Y') : null;
                break;
            case Field::COT_DATETIME:
                $format = $value ? Carbon::parse($value)->format('d/m/Y'): null;
                break;
            case Field::COT_LOOKUP && $field['lookup_id']:
                $format = $this->getCorporateLookupValue($value, $field['type'], $field['key'], $field['repeater']);
                break;
            case Field::COT_DECIMAL:
                $format = $this->decimalTypeFormat($value, $field['key']);
                break;
            case Field::COT_FREETEXT:
            case Field::COT_STRING:
                $format = $this->stringTypeRepeater($value, $field['repeater'], $field['key']);
                break;
            case Field::COT_INTEGER:
                $format = intval($value);
                break;
            default:
                $format = $value ?? null;
        }

        return $format;
    }

    public function decimalTypeFormat($value, $key)
    {
        switch (true) {
            case  $key == 'GenDDPricing' && $value:
                $value = min(array_column($value, 'value'));
                break;
            case  $key == 'GenDDPricingHIGH' && $value:
                $value = max(array_column($value, 'value'));
                break;
            default:
                $value = is_numeric($value) ? $value : null;
                break;
        }

        return $value;
    }

    public function stringTypeRepeater($value, $repeater, $key)
    {
        switch (true) {
            case $key == 'GENInPart' && $value:
            case $key == 'GenOutPart' && $value:
            case $key == 'GenDDProd' && $value:
                $string = array_column($value, 'name');
                break;
            case $repeater && is_array($value):
                $string = array_column($value, 'value');
                break;
            case $repeater && !is_array($value):
                $string =  [$value];
                break;
            case $key == 'GENmobile':
                $string = str_replace('+','',$value);
                break;
            default:
                $string = $value;
                break;
        }

        return $string;
    }

    public function getCorporateLookupValue($value, $type, $key, $repeater)
    {
        $list = null;
        if ($type == Field::COT_LOOKUP) {
            if ($repeater){
                $list = [];
                if(is_array($value)){
                    $list = $this->lookupFormat($value, $key);
                }else{
                    array_push($list, $this->getCorporateLookupId($key, $value));
                }
            }else{

                $value = $this->genKeyLookupValue($key, $value);
                $list = $this->getCorporateLookupId($key, $value);
            }
        }

        return $list;
    }

    public function lookupFormat($value, $key)
    {
        $list = [];

        foreach ($value as $val) {
            switch ($key) {
                case 'GENpweproduct':
                    $get = $this->getCorporateLookupId($key, $val['product_name']);
                    break;
                case 'GENCounInPay':
                case 'GenCounOutPay':
                    $get = $this->getCorporateLookupId($key, $val);
                    break;
                default:
                    $get = $this->getCorporateLookupId($key, $val['value']);
                    break;
            }
            array_push($list, $get);
        }

        return $list;
    }

    public function genKeyLookupValue($key, $value)
    {
        switch ($key) {
            case 'GENsameasreg':
                $data =  $value ? 'Yes' : 'No';
                $value = $data;
            break;
            case 'GenIBANYN':
                $value = $this->finxpProducts(Business::IBAN4U);
            break;
            case 'GenCCYN':
                $value = $this->finxpProducts(Business::CC_PROCESSING);
            break;
            case 'SEPADD_YN':
                $value = $this->finxpProducts(Business::SEPADD);
            break;
            case 'GENSignRightsSpecific':
                $found_key = array_search('Authorised Signatory', array_column($value, 'roles_in_company'));
                $value = $value[$found_key]['iban4u_rights'];
            break;
            default:
                return $value;
            break;
        }

        return $value;
    }

    public function getCorporateLookupId($key, $value)
    {
        $resource = $this->getCorporateResources();
        if(array_intersect([$key], $resource['countries'])){
            $value = $this->getCountryValues($value);
        }

        $lookupType = LookupType::where([
            'group' =>  $key,
            'type' => Field::COT_LOOKUP
        ])->where('name', 'LIKE', "%{$value}%") ->first();

        if ($lookupType && $value) {
            return  $lookupType['lookup_id'];
        }
    }

    public function getCorporateResources(): array
    {
        return include(resource_path('constants/corporate-kycp.php'));
    }
}
