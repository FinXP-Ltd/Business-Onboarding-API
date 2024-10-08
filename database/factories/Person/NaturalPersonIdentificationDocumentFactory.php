<?php

namespace Database\Factories\Person;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Person\NaturalPerson;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person\NaturalPersonIdentificationDocument>
 */
class NaturalPersonIdentificationDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'natural_person_id' => NaturalPerson::factory(),
            'document_type' => Str::random(3),
            'document_number' => Str::random(13),
            'document_country_of_issue' => fake()->randomElement(['USA', 'PHL', 'DEU']),
            'document_expiry_date' => fake()->date('Y-m-d', 'now')
        ];
    }
}
