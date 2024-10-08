<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SepaDdDirectDebit;/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IBAN4UPaymentAccountFactory>
 */
class SepaDdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "name" => fake()->text(),
            "description" => fake()->text(),
            "value" => fake()->text(),
            "sepa_dd_direct_debits" => SepaDdDirectDebit::factory()
        ];
    }
}
