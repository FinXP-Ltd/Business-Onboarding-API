<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class IBAN4UPaymentAccountDocumentsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $iban4uList = [
            'agreements_with_the_entities' => 'Agreements with the entities that shall be settling funds into the IBAN4U Account',
            'board_resolution' => 'Board Resolution',
            'third_party_questionnaire' => 'Third Party Questionnaire'
        ];

        return  [
            $this->file_type => $this->file_name,
            "{$this->file_type}_size" => $this->file_size,
            "{$this->file_type}_label" => $iban4uList[$this->file_type],
            "{$this->file_type}_loading" => false
        ];
    }
}
