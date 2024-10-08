<?php

namespace Database\Factories;
use Illuminate\Http\UploadedFile;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneralDocuments>
 */
class GeneralDocumentsFactory extends Factory
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
            'memorandum_and_articles_of_association' => $fakeFile->getClientOriginalName(),
            'memorandum_and_articles_of_association_size' => $fakeFile->getSize(),
            'certificate_of_incorporation' => $fakeFile->getClientOriginalName(),
            'certificate_of_incorporation_size' => $fakeFile->getSize(),
            'registry_exact' => $fakeFile->getClientOriginalName(),
            'registry_exact_size' => $fakeFile->getSize(),
            'company_structure_chart' => $fakeFile->getClientOriginalName(),
            'company_structure_chart_size' => $fakeFile->getSize(),
            'proof_of_address_document' => $fakeFile->getClientOriginalName(),
            'proof_of_address_document_size' => $fakeFile->getSize(),
            'operating_license' => $fakeFile->getClientOriginalName(),
            'operating_license_size' => $fakeFile->getSize(),
        ];
    }
}
