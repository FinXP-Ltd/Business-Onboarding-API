<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxInformation>
 */
class TaxInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'business_id' => Business::factory(),
            'name' => fake()->name,
            'tax_country' => fake()->countryCode(),
            'registration_number' => Str::random(5),
            'registration_type' => 'TRADING',
            'tax_identification_number' => Str::random(5),
        ];
    }
}
