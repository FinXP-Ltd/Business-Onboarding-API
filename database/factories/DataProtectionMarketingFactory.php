<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxInformation>
 */
class DataProtectionMarketingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "data_protection_notice" => fake()->randomElement([true, false]),
            "receive_messages_from_finxp" => fake()->randomElement(['YES','NO']),
            "receive_market_research_survey" => fake()->randomElement(['YES','NO'])
        ];
    }
}
