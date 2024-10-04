<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IdentityInformation>
 */
class IdentityInformationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            "id_type" => "Driver's License",
            "country_of_issue"=> "PHL",
            "id_number"=> "040618",
            "document_date_issued"=> fake()->date('Y-m-d', 'now'),
            "document_expired_date"=> fake()->date('Y-m-d', 'now'),
            "high_net_worth"=>  fake()->randomDigit(),
            "politically_exposed_person"=> "No"
        ];
    }
}
