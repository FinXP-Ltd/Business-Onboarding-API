<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SeniorManagementOfficerIdentityResource extends JsonResource
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
            'id_type' => $this->id_type,
            'country_of_issue' => $this->country_of_issue,
            'id_number' => $this->id_number,
            'document_date_issued' => $this->document_date_issued ?? null,
            'document_expired_date' => $this->document_expired_date ?? null,
            'high_net_worth' => $this->high_net_worth,
            'us_citizenship' => $this->us_citizenship,
            'politically_exposed_person' => $this->politically_exposed_person
        ];
    }
}
