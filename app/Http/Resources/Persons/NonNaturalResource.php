<?php

namespace App\Http\Resources\Persons;

use Illuminate\Support\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NonNaturalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $address = $this->addresses->first();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'registration_number' => $this->registration_number,
            'date_of_incorporation' => $this->date_of_incorporation,
            'country_of_incorporation' => $this->country_of_incorporation,
            'address' => [
                    'line_1' => $address?->line_1,
                    'line_2' => $address?->line_2,
                    'locality' => $address?->locality,
                    'postal_code' => $address?->postal_code,
                    'country' => $address?->country,
                    'licensed_reputable_jurisdiction' => $address?->licensed_reputable_jurisdiction
            ],
            'company_shareholding' =>[
                'name_of_shareholder_percent_held' => $this->name_of_shareholder_percent_held,
            ]
        ];
    }
}
