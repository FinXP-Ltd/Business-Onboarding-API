<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BusinessComposition;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BusinessCompositionable>
 */
class BusinessCompositionableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'business_composition_id' => BusinessComposition::factory(),
            'business_compositionable_id' => BusinessComposition::factory(),
            'business_compositionable_type' => 'App\Models\NonNaturalPerson\NonNaturalPerson'
        ];
    }
}
