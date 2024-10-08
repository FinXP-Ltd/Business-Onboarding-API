<?php

namespace Database\Factories\NonNaturalPerson;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person\NaturalPerson>
 */
class NonNaturalPersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->name(),
            'registration_number' =>  fake()->numerify('###############'),
            'date_of_incorporation' => fake()->date('Y-m-d', 'now'),
            'country_of_incorporation' => Str::random(3),
            'name_of_shareholder_percent_held' => fake()->name(),
            'user_id' => Str::uuid()->toString()
        ];
    }
}
