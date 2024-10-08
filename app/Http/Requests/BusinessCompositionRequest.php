<?php

namespace App\Http\Requests;

use App\Http\Resources\LookupableResource;
use App\Rules\ValidBusinessComposition;
use App\Models\BusinessComposition;
use App\Models\BusinessCompositionable;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\Person\NaturalPerson;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Traits\QueryParamsValidator;

class BusinessCompositionRequest extends FormRequest
{
    use QueryParamsValidator;

    const MAPPING_ALREADY_EXISTS = 'response.error.mapping_already_exists';

    public function __construct(private NonNaturalPerson $nonNaturalPerson, private NaturalPerson $naturalPerson)
    {
        $this->nonNaturalPerson = $nonNaturalPerson;
        $this->naturalPerson = $naturalPerson;
    }

    public function rules()
    {
        $position = is_array($this->input('position')) ? $this->input('position') : [];
        $isShareholder = array_intersect(['SH', 'UBO'], $position);
        $businessId = $this->input('business_id');
        $requestShare = $this->input('voting_share');
        $modelId = $this->input('mapping_id');
        $modelTypePayload = $this->input('model_type');
        $requiredShare = BusinessComposition::where('voting_share', '>=', 25)->where('business_id', $businessId)->get('voting_share')->first();
        $validateShares = BusinessComposition::where('voting_share', '<', 25)->where('voting_share', '!=', 0)->where('business_id', $businessId)->get()
        ->map(function ($businessComposition) {
            return $businessComposition->position()->with('lookupType')->get()->where('lookupType.type', BusinessComposition::SHAREHOLDER);
        })->count();

        Validator::extend('isShareholder', function () use ($isShareholder) {
            return $isShareholder;
        }, 'Position is not allowed to set voting share');

        Validator::extend('mappingPositionAlreadyExists', function () use ($position, $modelId, $modelTypePayload, $businessId) {
            $modelType = null;

            $modelTypePayload === 'P' ? $modelType = NaturalPerson::class : $modelType = NonNaturalPerson::class;

            $filterMappingId = BusinessCompositionable::where('business_compositionable_type', $modelType)->pluck('business_compositionable_id');

            $businessComposition = BusinessComposition::where('business_id', $businessId)->whereHas('person', function ($query) use ($modelType, $modelId) {
                $query->where('business_compositionable_type', $modelType)
                      ->where('business_compositionable_id', $modelId);
            })
            ->whereHas('position', function ($query) use ($position) {
                $query->whereHas('lookupType', function ($query) use ($position) {
                    $query->whereIn('type', $position);
                });
            });

            if ($businessComposition->exists() && $filterMappingId->contains($modelId)) {
                $positions = json_decode(LookupableResource::collection($businessComposition->first()->position)->toJson());
                $positions = array_map(fn ($position) => $position->value, $positions);

                $existingPositions = array_intersect($positions, $this->input('position'));

                Validator::replacer('mappingPositionAlreadyExists', function ($message) use ($existingPositions) {
                    return str_replace(':existing_positions', join(", ", $existingPositions), $message);
                });
            }

            return !$businessComposition->exists();
        }, __(self::MAPPING_ALREADY_EXISTS, ['model_type' => $modelTypePayload, 'mapping_id' => $modelId]));

        Validator::extend('compositionDoesNotExists', function () use ($businessId) {
            $filterBusinessId = BusinessComposition::pluck('business_id');

            return $filterBusinessId->contains($businessId);
        }, 'Business composition does not exist');

        Validator::extend('validateEntityExistence', function () use ($modelTypePayload, $modelId) {
            $entity = null;
            $modelTypePayload === 'N' ? $entity = $this->nonNaturalPerson::find($modelId) :
            $entity = $this->naturalPerson::find($modelId);

            return $entity;
        }, 'Entity does not exist');

        if ($this->isMethod('post')) {
            $rules = [
                "business_id" => ['required', new ValidBusinessComposition()],
                "model_type" => ['required', Rule::in(BusinessComposition::MODEL_TYPE)],
                "mapping_id" => ['required', 'mappingPositionAlreadyExists'],
                "position" => ['required', 'array'],
                "position.*" => Rule::in(BusinessComposition::POSITION),
                "voting_share" => ['isShareholder', 'digits_between:1,100', 'gt:0'],
                "start_date" => ['required', 'date'],
                "end_date" => 'date',
                "person_responsible" => [Rule::requiredIf($validateShares === 3 && $isShareholder && !$requiredShare && $requestShare < 25)]
            ];
        }

        if ($this->isMethod('put')) {
            $businessCompositionId = $this->route('businessComposition')->id;
            $modelTypeValue = BusinessComposition::where('id', $businessCompositionId)->where('business_id', $businessId)->value('model_type');

            $rules = [
                "business_id" => ['required', 'compositionDoesNotExists', new ValidBusinessComposition()],
                "model_type" => ['required', Rule::in(BusinessComposition::MODEL_TYPE)],
                "mapping_id" => ['required', 'validateEntityExistence'],
                "position" => ['required', 'array'],
                "position.*" => Rule::in(BusinessComposition::POSITION),
                "voting_share" => ['isShareholder', 'digits_between:1,100', 'gt:0'],
                "start_date" => ['required', 'date'],
                "end_date" => 'date',
            ];
        }

        if ($isShareholder) {
            array_push($rules['voting_share'], 'required');
        }

        return $rules;
    }
}
