<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KycpRequirementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return[
            'entity' => $this->entity,
            'entity_type' => $this->entity_type,
            'document_type' => $this->document_type,
            'kycp_key' => $this->kycp_key,
            'required' => $this->required
        ];
    }
}