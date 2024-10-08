<?php

namespace Database\Factories;
use Illuminate\Http\UploadedFile;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IBAN4UPaymentAccountDocuments>
 */
class IBAN4UPaymentAccountDocumentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $fakeFile = UploadedFile::fake()->create('fake_file.pdf', 1024);
        
        return [
            'agreements_with_the_entities' => $fakeFile->getClientOriginalName(),
            'agreements_with_the_entities_size' => $fakeFile->getSize(),
            'board_resolution' => $fakeFile->getClientOriginalName(),
            'board_resolution_size' => $fakeFile->getSize(),
            'third_party_questionnaire' => $fakeFile->getClientOriginalName(),
            'third_party_questionnaire_size' => $fakeFile->getSize(),
        ];
    }
}
