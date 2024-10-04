<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyIdentityInformationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return  [
            'index' => $this->index ?? null,
            'order' => $this->order ?? null,
            'id_type' => $this->id_type ?? null,
            'country_of_issue' => $this->country_of_issue ?? null,
            'id_number' => $this->id_number ?? null,
            'document_date_issued' =>  $this->document_date_issued ?? null,
            'document_expired_date' => $this->document_expired_date ?? null,
            'high_net_worth' => $this->high_net_worth ?? null,
            'us_citizenship' => $this->us_citizenship ?? null,
            'politically_exposed_person' => $this->politically_exposed_person ?? null
        ];
    }
}
