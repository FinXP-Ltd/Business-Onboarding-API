<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Document;
use App\Models\Business;
use App\Models\BusinessComposition;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentDeleteTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldSuspendDocument()
    {
        $document = $this->createDocument();

        $authUserToken = $this->getTokenPayload('agent');
        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->deleteJson(route('document.delete', $document->id))
        ->assertStatus(Response::HTTP_OK)
        ->assertJson([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Successfully suspended document!',
        ]);

        $document = $document->fresh();
        $this->assertTrue($document->trashed());
    }

    private function createDocument()
    {
        $authUserToken = $this->getTokenPayload('agent');
        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image('sample.jpg');
        $business = Business::factory()->hasTaxInformation()->create(['user' => $user->id]);

        $newDocument = [
            'data' => [
                'document_type' => 'certificate_of_incorporation',
                'owner_type' => 'B',
                'mapping_id' => $business->id,
            ],
            'file' => $documentFile,
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->post(route('document.upload'), $newDocument);

        $data = $response->json('data');
        $document = Document::find($data['document_id']);

        return $document;
    }
}
