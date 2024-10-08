<?php

namespace Tests\Onboarding\Feature\Document;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\Document;
use App\Models\BusinessComposition;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson as NonPerson;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class DocumentShowTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testReturnErrorDocumentList()
    {
        $b = Business::factory()->create();

        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('business.documents', $b->id))
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure(['status', 'message']);
    }

    public function testReturnErrorBusinessDocumentListIfNotExisting()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('business.documents', 'non-existent'))
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure(['status', 'message']);
    }

    public function testShouldRetrieveDocument()
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
        ->getJson(route('document.show', $document->id))
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'id',
            'document_type',
            'file_name',
            'file_type',
            'owner_type',
            'mapping_id',
        ]);
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
