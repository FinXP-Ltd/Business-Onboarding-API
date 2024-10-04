<?php

namespace Database\Factories;
use Illuminate\Http\UploadedFile;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SepaDirectDebitDocuments>
 */
class SepaDirectDebitDocumentsFactory extends Factory
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
            'template_of_customer_mandate' => $fakeFile->getClientOriginalName(),
            'template_of_customer_mandate_size' => $fakeFile->getSize(),
            'processing_history_with_chargeback_and_ratios' => $fakeFile->getClientOriginalName(),
            'processing_history_with_chargeback_and_ratios_size' => $fakeFile->getSize(),
            'copy_of_bank_settlement' => $fakeFile->getClientOriginalName(),
            'copy_of_bank_settlement_size' => $fakeFile->getSize(),
            'product_marketing_information' => $fakeFile->getClientOriginalName(),
            'product_marketing_information_size' => $fakeFile->getSize(),
        ];
    }
}
