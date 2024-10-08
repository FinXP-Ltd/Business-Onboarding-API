<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LookupType>
 */
class LookupTypeFactory extends Factory
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
            'description' => fake()->text(),
            'group' => fake()->text(),
            'type' => fake()->text(),
            'lookup_type_id' => fake()->randomDigit(),
            'lookup_id' => fake()->randomDigit(),
        ];
    }
}
