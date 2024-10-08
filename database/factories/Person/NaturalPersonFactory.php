<?php

namespace Database\Factories\Person;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person\NaturalPerson>
 */
class NaturalPersonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => fake()->randomElement(['Ms','Miss','Mr','Mrs']),
            'name' => fake()->unique()->firstName(),
            'surname' => fake()->unique()->lastName(),
            'sex' =>  fake()->randomElement(['female', 'male']),
            'date_of_birth' => fake()->date('Y-m-d', 'now'),
            'place_of_birth' => fake()->country(),
            'country_code' => fake()->countryCode(),
            'email_address' => fake()->unique()->safeEmail(),
            'mobile' => fake()->numerify('###############'),
            'user_id' => Str::uuid()->toString()
        ];
    }
}
