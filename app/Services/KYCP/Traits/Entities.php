<?php

namespace App\Services\KYCP\Traits;

use App\Models\COT\Field;
use App\Http\Resources\BusinessResource;
use App\Http\Resources\BetterPaymentResource;
use App\Enums\BOEntities;
use App\Enums\KYCEntities;
use Illuminate\Support\Carbon;
use App\Models\LookupType;
use App\Models\Auth\User;
use League\ISO3166\ISO3166;
use Auth;
trait Entities
{
    public function formatEntities($data, $entityId, $programId): array
    {
        $clientType = getClientType();
        $model = config('kycp.field_model.'.$clientType);
        $fields = $model::getByProgramIdAndEntityId($programId, $entityId);
        $keys = [];
        $compositionableId = isset($data['business_compositionable_id']) ? $data['business_compositionable_id'] : null;

        if (count($fields) != 0) {
           $keys = (KYCEntities::COMPANY->value != $entityId) ? $this->formatMapTable($fields, $data['person']) : $this->formatMapTable($fields, $data);
        } else {
            //Default Entities: at least 1 requires
            $keys = $this->defaultEntity($entityId, $data);
        }

        $keys = $this->genDocComplexFormat($keys);

        return [
            'EntityTypeId' => $entityId,
            'business_id' =>  $data['business_id'] ?? null,
            'business_compositionable_id' => $compositionableId,
            'model_type' => $data['model_type'] ?? null,
            'business_composition_id' =>  $data['business_composition_id'] ?? null,
            'EntityType' =>  KYCEntities::from($entityId)->word(),
            'Fields' => (object) $keys,
            'Entities' => []
        ];
    }

    private function formatMapTable($fields, $data)
    {
        $keys = [];
        foreach ($fields as $field) {
            $keys[$field['key']] = $this->mapTable($field, $data) ?? null;
        }

        return $keys;
    }

    public function formatBusinessComposition($business, $programId)
    {
        $businesses = json_decode(BetterPaymentResource::make($business)->toJson(), true);
        $businesses['business_id'] = $business->id;
        $businesses['Uid'] = $business->application_id;
        $businesses['model_type'] = 'B';
        $data = [];
        array_push($data, $this->formatEntities($businesses, KYCEntities::COMPANY->value, $programId));

        foreach ($businesses['business_composition'] as $index => $entity) {
            foreach ($entity['position'] as $key => $position) {

                $entityId = constant("App\Enums\BOEntities::{$position['value']}")->type($entity['model_type']);
                $entity['business_compositionable_id'] = $business->businessCompositions[$index]->person['business_compositionable_id'];
                $entity['Id'] = $business->businessCompositions[$index]->person['entity_id'];
                $entity['business_id'] = $business->id;
                $entity['business_composition_id'] = $business->businessCompositions[$index]->person['business_composition_id'];

                if ($key == 0) {
                    $app = $this->formatEntities($entity, $entityId, $programId);
                    array_push($data[0]['Entities'], $app);
                } else {
                    $app = $this->formatEntities($entity, $entityId, $programId);
                    array_push($data[0]['Entities'][$index]['Entities'], $app);
                }
            }

        }

        return [
            "ProgramId" => $programId,
            "Entities" => $data
        ];
    }

    private function genDocComplexFormat($all)
    {
        $resource = $this->getMappingResources();
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

    private function getMappingResources(): array
    {
        $clientType = getClientType();
        $mappingFile = config('kycp.resources_mapping.'.$clientType);
        return include(resource_path($mappingFile));
    }

    public function mapTable($field, $data)
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

        return  $this->formatValue($value, $field);
    }

    public function formatValue($value, $field)
    {
        $format = null;
        switch ($field['type']) {
            case Field::COT_DATE:
                $format = (bool)strtotime($value) ? Carbon::parse($value)->format('m/d/Y') : null;
                break;
            case Field::COT_DATETIME:
                $format = Carbon::parse($value)->format('d/m/Y');
                break;
            case Field::COT_LOOKUP && $field['lookup_id']:
                $format = $this->getLookupValue($value, $field['type'], $field['key'], $field['repeater']);
                break;
            case Field::COT_FREETEXT || Field::COT_STRING:
                $format = $this->checkRepeater($value, $field['repeater']);
                break;
            case Field::COT_DECIMAL:
                $format = is_numeric($value) ? $value : null;
                break;
            case Field::COT_INTEGER:
                $format = intval($value);
                break;
            default:
                $format = $value;
        }

        return $format;
    }

    public function getLookupValue($value, $type, $key, $repeater)
    {
        $list = null;
        if ($type == Field::COT_LOOKUP) {
            if ($repeater){
                $list = [];
                if(is_array($value)){
                    foreach ($value as $val) {
                        $get = $this->getLookupId($key, $val['value']);
                        if ($get) {
                            array_push($list, $get);
                        }
                    }
                }else{
                    array_push($list, $this->getLookupId($key, $value));
                }
            }else{
                $list = $this->getLookupId($key, $value);
            }
        }

        return $list;
    }

    public function getLookupId($key, $value)
    {
        $resource = $this->getResources();
        if(array_intersect([$key], $resource['countries'])){
            $value = $this->getCountryValues($value);
        }

        $lookupType = LookupType::where([
            'group' =>  $key,
            'type' => Field::COT_LOOKUP,
            'name' => $value
        ])->first();

        if ($lookupType) {
            return  $lookupType['lookup_id'];
        }
    }

    protected function getCountryValues($countryAlpha)
    {
        if (!$countryAlpha) {
            return null;
        }

        $ISO3166 = new ISO3166();

        if(is_numeric($countryAlpha)) {
            $country = $ISO3166->numeric($countryAlpha);
        } else if (is_array($countryAlpha)){
            if(strlen($countryAlpha[0]['value']) != 0 ) {
                $country = strlen($countryAlpha[0]['value']) > 2 ? $ISO3166->alpha3($countryAlpha[0]['value']) :
                $ISO3166->alpha2($countryAlpha[0]['value']);
            }
        } else {
            $country = strlen($countryAlpha) > 2 ? $ISO3166->alpha3($countryAlpha) : $ISO3166->alpha2($countryAlpha);
        }

        if (!isset($country['name'])) {
            return null;
        }

        return $country['name'];
    }

    public function getResources(): array
    {
        $clientType = getClientType();
        $resources = config('kycp.resources_mapping.'.$clientType);
        return include(resource_path($resources));
    }

    public function checkRepeater($value, $repeater)
    {
        if ($repeater && is_array($value)) {
            return array_column($value, 'value');
        }

        if ($repeater && ! is_array($value)) {
            return [];
        }

        return $value;
    }

    public function defaultEntity($entityId, $data)
    {
        $keys = [];
        switch ($entityId) {
            case 1:// Natural Persons
            case 10:// UBO
            case 12:// Director Natural Person
            case 13:// Company Secretary
            case 14:// Administrator/Authorised Signatory
            case 9:// Shareholder Natural Person
                $keys['GENname'] = $data['person'][0]['name'] ?? null;
                $keys['GENsurname'] = $data['person'][0]['surname'] ?? null;
                break;
            case 2:// Company
                $keys['GENname'] = $data['name'] ?? null;
                $keys['GENregnumber'] = $data['tax_information']['registration_number'] ?? null;
                $keys['GENvatnumber'] = $data['vat_number'] ?? null;
                break;
            case 8:// Shareholder Corporate
                $keys['GENname'] = $data['tax_information']['name'] ?? null;
                $keys['GENregnumber'] = $data['tax_information']['registration_number'] ?? null;
                break;
            case 11:// Director Corporate
                $keys['GENname'] = $data['name'] ?? null;
                $keys['GENregnumber'] = $data['registration_number'] ?? null;
                break;
            default:
                $keys = null;
                break;
        }
        return $keys;
    }
}
