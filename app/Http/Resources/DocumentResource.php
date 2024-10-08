<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'owner_type' => $this->owner_type,
            'mapping_id' => $this->documentable->id ?? null,
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'kycp_requirement' => KycpRequirementResource::make($this->kycpRequirement),
        ];
    }
}
