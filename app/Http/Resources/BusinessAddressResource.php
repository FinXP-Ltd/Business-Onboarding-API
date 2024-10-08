<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessAddressResource extends JsonResource
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
            'line_1' => $this->line_1,
            'line_2' => $this->line_2,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country
        ];
    }
}
