<?php

namespace Database\Factories\NonNaturalPerson;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\NonNaturalPerson\NonNaturalPerson;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person\NaturalPersonAddresses>
 */
class NonNaturalPersonAddressesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'line_1' => Str::random(5),
            'line_2' => Str::random(5),
            'locality' => Str::random(5),
            'postal_code' => Str::random(5),
            'licensed_reputable_jurisdiction' => fake()->randomElement(['YES', 'NO', 'LICENSE_NOT_REQUIRED']),
            'country' => Str::random(3)
        ];
    }
}
