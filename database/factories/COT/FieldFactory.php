<?php

namespace Database\Factories\COT;

use Illuminate\Database\Eloquent\Factories\Factory;

class FieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'program_id' => 1,
            'entity_id' => rand(1, 2),
            'key' =>  fake()->randomElement(['GenName', 'GenTitle']),
            'type' => fake()->randomElement(['Lookup', 'String'])
        ];
    }
}
