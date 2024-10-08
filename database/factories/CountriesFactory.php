<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Countries>
 */
class CountriesFactory extends Factory
{
    private static $order = 1;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "order" => self::$order++,
            "countries_where_product_offered" => fake()->countryISOAlpha3(),
            "distribution_per_country" => fake()->numberBetween(100, 999)
        ];
    }
}
