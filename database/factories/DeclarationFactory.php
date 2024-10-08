<?php

namespace Database\Factories;
use Illuminate\Http\UploadedFile;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Declaration>
 */
class DeclarationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $fakeFile = UploadedFile::fake()->create('fake_file.pdf', 1024, 'application/pdf');
        return [
            'file_name' => $fakeFile->getClientOriginalName(),
            'file_type' => $fakeFile->extension(),
            'size' => $fakeFile->getSize()
        ];
    }
}
