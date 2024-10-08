<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BusinessDetailFactory extends Factory
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
            'share_capital' => fake()->numberBetween(100, 100000),
            'number_shareholder' => fake()->numberBetween(4, 5),
            'number_directors' => fake()->numberBetween(4, 5),
            'license_rep_juris' => 'YES',
            'terms_and_conditions' => fake()->boolean(),
            'privacy_accepted' => fake()->boolean(),
        ];
    }
}
