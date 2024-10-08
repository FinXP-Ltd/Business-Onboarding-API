<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IBAN4UPaymentAccountFactory>
 */
class IBAN4UPaymentAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "share_capital" => fake()->randomNumber(),
            "annual_turnover" => fake()->randomNumber(),

            "deposit_approximate_per_month" => "5-10",
            "deposit_cumulative_per_month" =>  fake()->randomNumber(),

            "withdrawal_approximate_per_month" => "5-10",
            "withdrawal_cumulative_per_month" =>  fake()->randomNumber(),

            "held_accounts" => fake()->randomElement(['YES','NO']),
            "held_accounts_description" => Str::random(5),
            "refused_banking_relationship" => fake()->randomElement(['YES','NO']),
            "refused_banking_relationship_description" => Str::random(5),
            "terminated_banking_relationship" => fake()->randomElement(['YES','NO']),
            "terminated_banking_relationship_description" => Str::random(5),

            "purpose_of_account_opening" => null,
            "partners_incoming_transactions" => null,
            "partners_outgoing_transactions" => null,
            "estimated_monthly_transactions" => null,
            "average_amount_transaction_euro" => null,
            "accepting_third_party_funds" => fake()->randomElement(['YES','NO'])
        ];
    }
}
