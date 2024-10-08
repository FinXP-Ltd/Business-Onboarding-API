<?php

namespace Database\Factories\Person;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Person\NaturalPerson;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person\NaturalPersonAddresses>
 */
class NaturalPersonAddressesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'natural_person_id' => NaturalPerson::factory(),
            'line_1' => Str::random(5),
            'line_2' => Str::random(5),
            'locality' => Str::random(5),
            'postal_code' => Str::random(5),
            'country' => Str::random(3),
            'nationality' => Str::random(3),
            'city' => Str::random(3)
        ];
    }
}
