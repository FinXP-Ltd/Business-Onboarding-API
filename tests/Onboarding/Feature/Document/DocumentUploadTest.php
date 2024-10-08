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

class DocumentUploadTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldFailUploadDocumentIfValidationErrorsOccur()
    {
        $newDocument = [
            'data' => ['document_type' => 'M', 'owner_type' => 'D'],
            'file' => 'here',
        ];

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
        ->post(route('document.upload'), $newDocument)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonStructure([
            'code',
            'status',
            'message' => [
                "data.document_type",
                "data.owner_type",
                "data.mapping_id",
                "file"
            ]
        ]);
    }

    public function testShouldCreateNewDocumentWithAUboPerson()
    {
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

        $business= Business::factory()->hasTaxInformation()->create([
            'user' => $user->id,
            'status' => 'OPENED'

        ]);
        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);

        $this->addRoles($business, $naturalPerson, 'P', 'UBO', $imposter);

        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image('sample.jpg');

        $newDocument = [
            'data' => [
                'document_type' => 'utility_bill_or_proof_of_address',
                'owner_type' => 'P',
                'mapping_id' => $naturalPerson->id,
            ],
            'file' => $documentFile,
        ];

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->post(route('document.upload'), $newDocument)
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => ['document_id'],
        ]);
    }

    public function testShouldCreateNewDocument()
    {
        $authUserToken = $this->getTokenPayload('agent');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $business= Business::factory()->hasTaxInformation()->create(['user' => $user->id]);

        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image('sample.jpg');

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

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->post(route('document.upload'), $newDocument)
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => ['document_id'],
        ]);
    }

    public function testShouldCreateNewDocumentWithADirectorPerson()
    {
        $authUserToken = $this->getTokenPayload('agent');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $business= Business::factory()->hasTaxInformation()->create(['user' => $user->id]);
        $nonPerson = NonNaturalPerson::factory()->create(['user_id' => $user->id]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->addRolesNonPerson($business, $nonPerson, 'N', 'DIR', $imposter);

        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image('sample.jpg');

        $newDocument = [
            'data' => [
                'document_type' => 'utility_bill_or_proof_of_address',
                'owner_type' => 'N',
                'mapping_id' => $nonPerson->id,
            ],
            'file' => $documentFile,
        ];

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->post(route('document.upload'), $newDocument)
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => ['document_id'],
        ]);
    }

    public function testShouldThrowErrorIfDocumentIsNotNeeded()
    {
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

        $business= Business::factory()->hasTaxInformation()->create(['user' => $user->id]);
        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);

        $this->addRoles($business, $naturalPerson, 'P', 'SH', $imposter);

        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image('sample.jpg');

        $newDocument = [
            'data' => [
                'document_type' => 'application_form',
                'owner_type' => 'P',
                'mapping_id' => $naturalPerson->id,
            ],
            'file' => $documentFile,
        ];

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->post(route('document.upload'), $newDocument)
        ->assertStatus(Response::HTTP_BAD_REQUEST)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
        ]);
    }

    public function testShouldBeUnableToUploadDocumentsOver4mb()
    {
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

        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image('sample.jpg')->size(4001);

        $newDocument = [
            'data' => [
                'document_type' => 'application_form',
                'owner_type' => 'B',
                'mapping_id' => 1,
            ],
            'file' => $documentFile,
        ];


        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->post(route('document.upload'), $newDocument)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'status' => 'failed',
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'message' => [
                'file' => ["The file must not be greater than 4000 kilobytes."]
            ],
        ]);
    }

    public function testShouldNotUploadDocumentWithoutTheMappingId()
    {
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

        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image('sample.jpg');

        $newDocument = [
            'data' => [
                'document_type' => 'DRIVERS_LICENSE',
                'owner_type' => 'B',
                'mapping_id' => null
            ],
            'file' => $documentFile,
        ];


        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->post(route('document.upload'), $newDocument)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'status' => "failed",
            'message' => [
                'data.mapping_id' => [
                    "The data.mapping id field is required."
                ]
            ],
        ]);
    }

    public function testShouldReturnNotFoundResponseIfMappingInNewDocumentRequestDoesNotExist()
    {

        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image('sample.jpg');

        $newDocument = [
            'data' => [
                'document_type' => 'application_form',
                'owner_type' => 'B',
                'mapping_id' => 199,
            ],
            'file' => $documentFile,
        ];

        $mappingId = $newDocument['data']['mapping_id'];
        $ownerType = $newDocument['data']['owner_type'];

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
        ->post(route('document.upload'), $newDocument)
        ->assertStatus(Response::HTTP_NOT_FOUND)
        ->assertJson([
            'status' => 'failed',
            'code' => 404,
            'message' => "mapping_id {$mappingId} for owner_type \"{$ownerType}\" does not exist."
        ]);
    }

    private function addRoles($business, $person, $modelType, $role, $user)
    {
        $businessComposition = [
            "business_id" => $business->id,
            "model_type" => $modelType,
            "mapping_id" => $person->id,
            "position" => [$role],
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01",
            "voting_share" => 25
        ];

        $this->impersonate(credential: $user, guard: 'auth0-api')
            ->postJson(route('business.createComposition'), $businessComposition)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data' => ['business_composition_id']
            ]);
    }

    private function addRolesNonPerson($business, $person, $modelType, $role, $user)
    {
        $businessComposition = [
            "business_id" => $business->id,
            "model_type" => $modelType,
            "mapping_id" => $person->id,
            "position" => [$role],
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01",
        ];

        $this->impersonate(credential: $user, guard: 'auth0-api')
            ->postJson(route('business.createComposition'), $businessComposition)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data' => ['business_composition_id']
            ]);
    }
}
