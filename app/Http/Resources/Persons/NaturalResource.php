<?php

namespace App\Http\Resources\Persons;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class NaturalResource extends JsonResource
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
            'id' => $this->id,
            'title' => $this->title,
            'name' => $this->name,
            'surname' => $this->surname,
            'sex' => $this->sex,
            'date_of_birth' => $this->date_of_birth,
            'place_of_birth' => $this->place_of_birth,
            'email_address' => $this->email_address,
            'country_code' => $this->country_code,
            'mobile' => str_replace('+', '', $this->mobile),
            'address' => AddressResource::make($this->addresses),
            'identification_document' => PersonDocumentsResource::make($this->identificationDocument),
            'additional_info' => AddtInfoResource::make($this->additionalInfos),
        ];
    }
}
