<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanySepaDDResource extends JsonResource
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
            'processing_sepa_dd' => $this->CompanySepaDd->processing_sepa_dd ?? null,
            'expected_global_mon_vol' => $this->CompanySepaDd->expected_global_mon_vol ?? null,
            'sepa_dd_products' => $this->CompanySepaDd->sepaProducts,
            'products' => $this->business->products->where('is_selected', true)->map->only(['product_name'])->toArray(),
            'disabled' => $this->business->status === "PRESUBMIT"
        ];
    }
}
