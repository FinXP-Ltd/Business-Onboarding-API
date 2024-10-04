<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxInformation>
 */
class BusinessAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'line_1' => fake()->address,
            'line_2' => fake()->address,
            'city' => fake()->city,
            'country' => fake()->countryCode(),
            'postal_code' => Str::random(5),
            'lookup_type_id' => 1, //REGISTERED_BUSINESS_ADDRESS
        ];
    }
}
