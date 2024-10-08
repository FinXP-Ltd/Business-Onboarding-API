<?php

namespace Database\Factories;

use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreditCardProcessing>
 */
class CreditCardProcessingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "business_id" => Business::factory(),
            "currently_processing_cc_payments" => fake()->randomElement(['YES','NO']),
            "offer_recurring_billing" => fake()->randomElement(['YES','NO']),
            "frequency_offer_billing" => fake()->randomElement(Business::FREQUENCY),
            "offer_refunds" => fake()->randomElement(['YES','NO']),
            "frequency_offer_refunds" => fake()->randomElement(Business::FREQUENCY),
            "if_other_offer_refunds" => fake()->text(10),
            "processing_account_primary_currency" => fake()->currencyCode(),
            "ac_average_ticket_amount" => fake()->numberBetween(100, 999),
            "ac_highest_ticket_amount" => fake()->numberBetween(100, 999),
            "ac_alternative_payment_methods" => fake()->text(10),
            "ac_method_currently_offered" => fake()->text(10),
            "ac_current_mcc" => fake()->text(40),
            "ac_current_descriptor" =>fake()->text(40),
            "ac_cb_volumes_twelve_months" =>fake()->randomDigit(),
            "ac_cc_volumes_twelve_months" => fake()->randomDigit(),
            "ac_refund_volumes_twelve_months" => fake()->randomDigit(),
            "ac_current_acquire_psp" => fake()->text(40)
        ];
    }
}
