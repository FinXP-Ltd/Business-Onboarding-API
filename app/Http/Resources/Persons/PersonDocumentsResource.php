<?php

namespace App\Http\Resources\Persons;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PersonDocumentsResource extends JsonResource
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
            'document_type' => $this->document_type,
            'document_number' => $this->document_number,
            'document_country_of_issue' => $this->document_country_of_issue,
            'document_expiry_date' => $this->document_expiry_date
        ];
    }
}
