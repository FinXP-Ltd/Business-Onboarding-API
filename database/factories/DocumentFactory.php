<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Document;
use App\Models\Person\NaturalPerson;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person\NaturalPerson>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'document_type' => fake()->randomElement(config('kycp-requirement.document_types')),
            'owner_type' => fake()->randomElement(Document::OWNER_TYPES),
            'file_name' => 'P_1665495753.jpg', //already uploaded in azure blob
            'file_type' => 'jpg',
            'documentable_id' => NaturalPerson::factory(),
            'documentable_type' => 'App\\Models\\Person\\NaturalPerson'
        ];
    }
}
