<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DataProtectionMarketingResource extends JsonResource
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
            'data_protection_notice' => $this->data_protection_notice,
            'receive_messages_from_finxp' => $this->receive_messages_from_finxp,
            'receive_market_research_survey' => $this->receive_market_research_survey
        ];
    }
}
