<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyDeclarationResource extends JsonResource
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
            'declaration_agreement' => [
                'file_name' => $this->companyDeclaration?->file_name ?? null,
                'file_type' => $this->companyDeclaration?->file_type ?? null,
                'size' => $this->companyDeclaration?->size ?? null,
            ],
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
