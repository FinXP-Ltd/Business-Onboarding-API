<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\SepaDd;

class SepaDdDirectDebitResource extends JsonResource

{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $sepa_dds = SepaDd::where('sepa_dd_direct_debits', $this->id)->get();
        return [
            'currently_processing_sepa_dd' => $this->currently_processing_sepa_dd,
            'sepa_dds' => SepaDdResource::collection($sepa_dds),
            'sepa_dd_volume_per_month' => $this->sepa_dd_volume_per_month,
            'ac_sepa_dd_volume_per_month' => $this->ac_sepa_dd_volume_per_month
        ];
    }
}
