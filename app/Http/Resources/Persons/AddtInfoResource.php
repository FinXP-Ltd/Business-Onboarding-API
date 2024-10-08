<?php

namespace App\Http\Resources\Persons;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class AddtInfoResource extends JsonResource
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
            'occupation' =>$this->occupation,
            'employment' => $this->employment,
            'position' => $this->position,
            'source_of_income' => $this->source_of_income,
            'source_of_wealth' => $this->source_of_wealth,
            'source_of_wealth_details' => $this->source_of_wealth_details,
            'other_source_of_wealth_details' => $this->other_source_of_wealth_details,
            'pep' => $this->pep,
            'us_citizenship' => $this->us_citizenship,
            'tin' => $this->tin,
            'country_tax' => $this->country_tax,
        ];
    }
}
