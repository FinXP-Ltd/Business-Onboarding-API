<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use App\Exceptions\DocumentException;
use App\Models\Business;
use App\Enums\Entity;
use App\Enums\Position;
use Illuminate\Http\Response;
use App\Enums\EntityTypes;

class KycpRequirement extends Model
{
    use HasFactory;

    const NATURALPERSON = 'App\Models\Person\NaturalPerson';
    const NONNATURALPERSON = 'App\Models\NonNaturalPerson\NonNaturalPerson';
    protected $fillable = ['entity', 'entity_type', 'document_type', 'kycp_key', 'required'];
    protected $hidden = ['created_at', 'updated_at', 'id', 'document_id'];
    const KYCP_REQUIREMENT = 'kycp-requirement';
    const BP_REQUIREMENT = 'bp-requirement';
    const BUSINESS_DOCUMENT_REQUIRED = '.business_document_required';

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function required($document_type, $mapping_id, $entity, $list)
    {
        switch ($entity) {
            case (Entity::NON_NATURAL_PERSON()):
                $required = ($this->findingPositionType($mapping_id, $entity) === Position::DIRECTOR()) ?
                config($list.'.dir_corporate_document_required') : config($list.'.sh_corporate_document_required');
                break;
            case (Entity::NATURAL_PERSON()):
                $required = $this->naturalPersonDocuments($mapping_id, $entity, $list);
                break;
            default:
                $required = config($list.self::BUSINESS_DOCUMENT_REQUIRED);
                break;
        }

        $other = $this->otherRequirements($entity, $mapping_id, $list);
        $required =array_merge($required, $other['required']);

        return $this->ifRequired($document_type, $mapping_id, $entity, $required, $list);
    }

    public function kycpKey($document_type, $list)
    {
        return Arr::get(config($list.'.assigned_keys'), $document_type);
    }

    public function entityType($mapping_id, $entity)
    {
        if ($entity === Entity::BUSINESS()) {
            return $this->findingBusinessType($mapping_id);
        } else {
            return $this->findingPositionType($mapping_id, $entity);
        }
    }

    public function findingBusinessType($mapping_id)
    {
        $business = Business::find($mapping_id);

        if(!$business) {
            throw new DocumentException(__('response.businesses.not_exist'), Response::HTTP_NOT_FOUND);
        }else{
            return  $business->taxInformation()->value('registration_type');
        }
    }

    private function findingPositionType($mapping_id, $entity)
    {
        $business_composition_id = ($entity === 'P') ? BusinessCompositionable::where('business_compositionable_id', $mapping_id)->where('business_compositionable_type', self::NATURALPERSON)->value('business_composition_id') :
        BusinessCompositionable::where('business_compositionable_id', $mapping_id)->where('business_compositionable_type', self::NONNATURALPERSON)->value('business_composition_id');

        return BusinessComposition::find($business_composition_id)->position()->with('lookupType')->get()->value('lookupType.type');
    }

    private function ifRequired($document_type, $mapping_id, $entity, $required_array, $list)
    {
        if (in_array($document_type, $this->entityTypeDocuments($entity, $mapping_id, $list))) {
            return in_array($document_type, $required_array) ? 1 : 0;
        } else {
            throw new DocumentException(__('response.documents.not_needed'));
        }
    }

    private function entityTypeDocuments($entity, $mapping_id, $list)
    {
        switch ($entity) {
            case (Entity::NON_NATURAL_PERSON()):
                ($this->findingPositionType($mapping_id, $entity) === EntityTypes::DIR()) ?
                $documents = array_merge(config($list.'.dir_corporate_document_required'), config($list.'.dir_corporate_document_optional')) :
                $documents = array_merge(config($list.'.sh_corporate_document_required'), config($list.'.sh_corporate_document_optional'));
                break;
            case (Entity::NATURAL_PERSON()):
                $documents = $this->naturalPersonDocumentTypes($mapping_id, $entity, $list);
                break;
            default:
                $documents =  array_merge(config($list.self::BUSINESS_DOCUMENT_REQUIRED), config($list.'.business_document_optional'));
                break;
        }

        $other = $this->otherRequirements($entity, $mapping_id, $list);
        $documents = array_merge($documents, $other['required'], $other['optional']);

        return $documents;
    }

    private function naturalPersonDocumentTypes($mapping_id, $entity, $list)
    {
        switch (true) {
            case ($this->findingPositionType($mapping_id, $entity) === EntityTypes::UBO):
                $array_required = config($list.'.ubo_document_required');
                $array_optional = config($list.'.ubo_document_optional');
                break;
            case ($this->findingPositionType($mapping_id, $entity) === EntityTypes::DIR):
                $array_required = config($list.'.dir_document_required');
                $array_optional = config($list.'.dir_document_optional');
                break;
            case ($this->findingPositionType($mapping_id, $entity) === EntityTypes::SIG):
                $array_required = config($list.'.sig_document_required');
                $array_optional = config($list.'.sig_document_optional');
                break;
            default:
                $array_required = config($list.'.sh_document_required');
                $array_optional = config($list.'.sh_document_optional');
        }

        return array_merge($array_required, $array_optional);
    }

    private function naturalPersonDocuments($mapping_id, $entity, $list)
    {
        switch (true) {
            case ($this->findingPositionType($mapping_id, $entity) === Position::UBO()):
                $required = config($list.'.ubo_document_required');
                break;
            case ($this->findingPositionType($mapping_id, $entity) === Position::DIRECTOR()):
                $required = config($list.'.dir_document_required');
                break;
            case ($this->findingPositionType($mapping_id, $entity) === Position::SIGNATORY()):
                $required = config($list.'.sig_document_required');
                break;
            default:
                $required = config($list.'.sh_document_required');
        }

        return $required;
    }

    public function otherRequirements($entity, $mapping_id, $list)
    {
        switch (true) {
            case ($mapping_id && $entity == 'B' && in_array($this->findingBusinessType($mapping_id), Business::SOLE_TRADER)):
                $required = config($list.'.sole_trader_document_required');
                $optional = config($list.'.sole_trader_document_optional');
                break;
            case ($mapping_id && $entity === Position::UBO() && $list ==  self::BP_REQUIREMENT):
                $business_id =$this->getBusinessIdOfAPerson($mapping_id, 'P');
                $persons = $this->countPositionInTheBusiness($business_id, 'UBO',24); //at least one UBO with >25 voting share
                $documents = $this->documentCheckForThePerson($persons);
                $required = ($documents->count() == 0) ? ['utility_bill_or_proof_of_address'] : [];
                $optional= [];
                break;
            default:
                $required= [];
                $optional= [];
                break;
        }

        return  [
            'required' => $required,
            'optional' => $optional
        ];
    }

    public function getBusinessIdOfAPerson($mapping_id, $entity)
    {
        if($entity == 'P'){
            $id = BusinessCompositionable::where('business_compositionable_id', $mapping_id)
            ->where('business_compositionable_type', self::NATURALPERSON)->value('business_composition_id');
        }else{
            $id = BusinessCompositionable::where('business_compositionable_id', $mapping_id)
            ->where('business_compositionable_type', self::NONNATURALPERSON)->value('business_composition_id');
        }

       return BusinessComposition::where('id',$id)->first()->value('business_id');
    }

    public function countPositionInTheBusiness($business_id, $position, $voting_share = 24)
    {
        $business = BusinessComposition::where('business_id',$business_id)->whereNotBetween('voting_share', [1, $voting_share])->get();
        $list = [];

        foreach($business as $person){
            $personPosition =BusinessComposition::find($person->id)->position()->with('lookupType')->get()->value('lookupType.type');
            $id = $person->person()->first()->business_compositionable_id;

            if($position == $personPosition){
                array_push($list, $id);
            }
        }
        return $list;
    }

    public function documentCheckForThePerson($listOfIds)
    {
       return Document::whereIn('documentable_id', $listOfIds)->get();
    }
}
