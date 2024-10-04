<?php

namespace Database\Factories;
use Illuminate\Http\UploadedFile;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreditCardProcessingDocuments>
 */
class CreditCardProcessingDocumentsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $fakeFile = UploadedFile::fake()->create('fake_credit_card_document.pdf', 4000);
        
        return [
            'proof_of_ownership_of_the_domain' => $fakeFile->getClientOriginalName(),
            'proof_of_ownership_of_the_domain_size' => $fakeFile->getSize(),
            'processing_history' => $fakeFile->getClientOriginalName(),
            'processing_history_size' => $fakeFile->getSize(),
            'copy_of_bank_settlement' => $fakeFile->getClientOriginalName(),
            'copy_of_bank_settlement_size' => $fakeFile->getSize(),
            'company_pci_certificate' => $fakeFile->getClientOriginalName(),
            'company_pci_certificate_size' => $fakeFile->getSize(),
        ];
    }
}
