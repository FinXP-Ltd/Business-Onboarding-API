<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\TaxInformation;
use App\Rules\Boolean;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyAddress>
 */
class CompanyAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {   
        // $randomBoolean = (bool)random_int(0, 1);
        
        return [
            "id" => $this->faker->uuid,
            'tax_information_id' => TaxInformation::factory(),
            'registered_street_number' => Str::random(5),
            'registered_street_name' => fake()->streetName,
            'registered_postal_code' => fake()->postcode,
            'registered_city' => fake()->city,
            'registered_country' => fake()->country,

            'same_as_registered' => fake()->boolean,

            'optional_street_number' => Str::random(5),
            'optional_street_name' => fake()->streetName,
            'optional_postal_code' => fake()->postcode,
            'optional_city' => fake()->city,
            'optional_country' => fake()->country,

        ];
    }
}
