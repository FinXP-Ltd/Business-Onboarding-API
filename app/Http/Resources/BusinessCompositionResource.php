<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\Entity;

class BusinessCompositionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $person = $this->person?->business_compositionable_type::find($this->person->business_compositionable_id);
        $resource = Entity::resource($this->model_type);

        return [
            'person' => $person ? $resource::make($person) : null,
            'model_type' => $this->model_type,
            'position' => LookupableResource::collection($this->position),
            'voting_share' => $this->voting_share,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'person_responsible' => $this->person_responsible
        ];
    }
}
