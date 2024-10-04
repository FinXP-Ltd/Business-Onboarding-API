<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SepaDdDirectDebit>
 */
class SepaDdDirectDebitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "business_id" => Business::factory(),
            "currently_processing_sepa_dd" => fake()->randomElement(["YES","NO"]),
            "sepa_dd_volume_per_month" => fake()->randomNumber(),
            "ac_sepa_dd_volume_per_month" => fake()->randomNumber(),
        ];
    }
}
