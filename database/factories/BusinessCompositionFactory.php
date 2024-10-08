<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\BusinessComposition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessComposition>
 */
class BusinessCompositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'business_id' => Business::factory(),
            'model_type' => fake()->randomElement(BusinessComposition::MODEL_TYPE),
            'start_date' => fake()->date(),
            'end_date' => fake()->date(),
        ];
    }
}
