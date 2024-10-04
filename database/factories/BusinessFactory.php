<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person\NaturalPerson>
 */
class BusinessFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'foundation_date' => fake()->date(),
            'vat_number' => fake()->randomNumber(),
            'telephone' => fake()->randomNumber(),
            'email' => fake()->safeEmail(),
            'website' => fake()->domainName(),
            'additional_website' => fake()->domainName(),
            'status' => fake()->randomElement(Business::STATUSES),
        ];
    }
}
