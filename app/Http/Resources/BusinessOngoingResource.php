<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BusinessComposition;
use App\Models\CompanyRepresentative;

class BusinessOngoingResource extends JsonResource
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
            'name' => $this->companyInformation?->company_name,
            'trading_name' => $this->companyInformation?->company_trading_as,
            'status' => $this->status
        ];
    }
}
