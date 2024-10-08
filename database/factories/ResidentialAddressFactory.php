<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResidentialAddress>
 */
class ResidentialAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'street_number' => fake()->streetNumber(),
            'street_name' => fake()->streetName(),
            'postal_code' => fake()->postalCode(),
            'city' => fake()->city(),
            'country' => fake()->country()
        ];
    }
}
