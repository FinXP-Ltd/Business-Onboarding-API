<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeniorManagementOfficer>
 */
class SeniorManagementOfficerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
         return [
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
            'required_indicator' => fake()->randomElement([1,0])
        ];
    }
}
