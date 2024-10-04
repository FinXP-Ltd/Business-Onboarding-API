<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyRepresentative>
 */
class CompanyRepresentativeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => fake()->uuid,
            'index' => fake()->randomDigit(),
            'first_name' => fake()->firstName,
            'middle_name' => fake()->firstName,
            'surname' => fake()->lastName,
            'place_of_birth' => fake()->country,
            'date_of_birth' => fake()->date('Y-m-d', 'now'),
            'nationality' => fake()->country,
            'citizenship' => fake()->country,
            'email_address' => fake()->unique()->safeEmail(),
            'phone_code' => '+639',
            'phone_number' => '090481250816931',
            'roles_in_company' =>  'UBO',
            'percent_ownership' => fake()->randomDigit()
        ];
    }
}
