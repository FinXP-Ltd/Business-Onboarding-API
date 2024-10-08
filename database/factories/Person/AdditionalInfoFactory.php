<?php

namespace Database\Factories\Person;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Person\NaturalPerson;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person\AdditionalInfo>
 */
class AdditionalInfoFactory extends Factory
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
            'occupation' => Str::random(5),
            'employment' => Str::random(5),
            'position' => Str::random(5),
            'source_of_income' => Str::random(5),
            'source_of_wealth' => Str::random(3),
            'source_of_wealth_details' => Str::random(3),
            'other_source_of_wealth_details' => Str::random(3),
            'us_citizenship' => fake()->randomElement([true, false]),
            'pep' => fake()->randomElement(['Yes', 'No']),
            'tin' => Str::random(13),
            'country_tax' => Str::random(3)
        ];
    }
}
