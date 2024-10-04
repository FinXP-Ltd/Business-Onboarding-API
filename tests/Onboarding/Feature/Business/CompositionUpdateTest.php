<?php

namespace Tests\Onboarding\Feature\Business;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\Document;
use App\Models\BusinessComposition;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson as NonPerson;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CompositionUpdateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();

        Business::query()->forceDelete();
        BusinessComposition::query()->forceDelete();
        NaturalPerson::query()->forceDelete();
    }

    public function testShouldUpdateExistingBusinessComposition()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $nonNatural = NonPerson::factory()->create(['user_id' => $user->id]);
        $business = Business::factory()->create([
            'status' => 'OPENED',
            'user' => $user->id
        ]);
        $businessComposition = BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $nonNatural->id,
            'business_compositionable_type' => NonNaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
            'business_id' => $business->id,
            'voting_share' => 10,
        ]);

        $updatedBusinessComposition = [
            "business_id" => $business->id,
            "model_type" => 'N',
            "mapping_id" => $nonNatural->id,
            "position" => ["SH"],
            "voting_share" => 2,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('business.updateComposition', $businessComposition->id), $updatedBusinessComposition)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['business_composition_id'],
        ]);
    }

    public function testShouldChangeTheShareholdingNumberIntoZeroIfThePositionUpdateIsNotAShareholder()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);
        $b = Business::factory()->hasBusinessDetails([
                'number_shareholder' => 1,
                'number_directors' => 0
            ])->create([
                'status' => 'OPENED',
                'user' => $user->id
            ]);
        $businessComposition = BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
            'business_id' => $b->id,
            'voting_share' => 10,
        ]);

        $updatedBusinessComposition = [
            "business_id" => $b->id,
            "model_type" => "P",
            "mapping_id" => $naturalPerson->id,
            "position" => ["DIR"],
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('business.updateComposition', $businessComposition->id), $updatedBusinessComposition)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['business_composition_id'],
        ]);
    }

    public function testShouldUnabletoUpdateBusinessCompositionIfTheRequestVotingShareIsGreaterThanThePreviousOne()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $b = Business::factory()->hasBusinessDetails([
                'number_shareholder' => 1,
                'number_directors' => 0
            ])->create([
                'status' => 'OPENED',
                'user' => $user->id
            ]);
        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);
        $businessComposition = BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
            'business_id' => $b->id,
            'voting_share' => 10,
        ]);
        $updatedBusinessComposition = [
            "business_id" => $b->id,
            "model_type" => "P",
            "position" => ["SH"],
            "voting_share" => 5,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->putJson(route('business.updateComposition', $businessComposition->id), $updatedBusinessComposition)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonStructure([
            'status',
            'message',
            'code',
        ]);
    }

    public function testShouldRequireMappingIdIfTheModelTypeUpdateIsDifferentFromTheTable()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $b = Business::factory()->hasBusinessDetails([
            'number_shareholder' => 1,
            'number_directors' => 0
        ])->create([
            'status' => 'OPENED',
            'user' => $user->id
        ]);
        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);
        $businessComposition = BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
            'business_id' => $b->id,
            'voting_share' => 10,
        ]);

        $updatedBusinessComposition = [
            "business_id" => $b->id,
            "model_type" => "N",
            "position" => ["DIR"],
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];


        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
             ->putJson(route('business.updateComposition', $businessComposition->id), $updatedBusinessComposition)
             ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testShouldReturnAnErrorInBusinessCompositionUpdateIfMappingIdForModelTypeDoesNotExist()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $b = Business::factory()->hasBusinessDetails([
            'number_shareholder' => 1,
            'number_directors' => 0
        ])->create([
            'status' => 'OPENED',
            'user' => $user->id
        ]);
        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);
        $businessComposition = BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
            'business_id' => $b->id,
            'voting_share' => 10,
        ]);

        $updatedBusinessComposition = [
            "business_id" => $b->id,
            "model_type" => "N",
            "position" => ["DIR"],
            "mapping_id" => $naturalPerson->id,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
             ->putJson(route('business.updateComposition', $businessComposition->id), $updatedBusinessComposition)
             ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testShouldUpdateBusinessCompositionIfMappingIdExistForModelType()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $nonNaturalPerson = NonNaturalPerson::factory()->create(['user_id' => $user->id]);
        $b = Business::factory()->hasBusinessDetails([
            'number_shareholder' => 1,
            'number_directors' => 0
        ])->create([
            'status' => 'OPENED',
            'user' => $user->id
        ]);

        $businessComposition = BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $nonNaturalPerson->id,
            'business_compositionable_type' => NonNaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
            'business_id' => $b->id,
            'voting_share' => 10,
        ]);

        $updatedBusinessComposition = [
            "business_id" => $b->id,
            "model_type" => "N",
            "position" => ["DIR"],
            "mapping_id" => $nonNaturalPerson->id,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('business.updateComposition', $businessComposition->id), $updatedBusinessComposition)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['business_composition_id'],
        ]);
    }

    public function testShouldNotUpdateIfPositionIsNotAllowedToSetVotingShare()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $b = Business::factory()->hasBusinessDetails([
            'number_shareholder' => 1,
            'number_directors' => 0
        ])->create([
            'status' => 'OPENED',
            'user' => $user->id
        ]);
        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);
        $businessCompositionUpdate = BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
            'business_id' => $b->id,
            'voting_share' => 10,
        ]);

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "position" => ["DIR"],
            "mapping_id" => $naturalPerson->id,
            "start_date" => "2015-08-06",
            "end_date" => "1971-08-09",
            'voting_share' => 10
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('business.updateComposition', $businessCompositionUpdate->id), $businessComposition)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'status' => 'failed',
                'message' =>  ["voting_share" => ['Position is not allowed to set voting share']],
                'code' => 422
            ]);
    }

    public function testShouldNotUpdateBusinessIdOfOtherBusinessesInBusinessComposition()
    {

        $businessCompositionFactory = BusinessComposition::factory()->create()->first();

        $b = Business::latest('id')->first();

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "position" => ["SH"],
            "voting_share" => 25,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

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
            ->putJson(route('business.updateComposition', $businessCompositionFactory->id), $businessComposition)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testShouldNotUpdateBusinessCompositionIfBusinessIsAlreadySubmitted()
    {
        $naturalPerson = NaturalPerson::factory()->create();
        $b = Business::factory()->create(['status' => 'SUBMITTED']);

        $composition = BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
           'business_id' => $b->id,
            'voting_share' => 25
        ]);

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "position" => ["DIR"],
            "voting_share" => 25,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

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
            ->putJson(route('business.updateComposition', $composition->id), $businessComposition)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testShouldNotUpdateNonExistingBusinessComposition()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $b = Business::factory()->create(['status' => 'SUBMITTED']);

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "position" => ["DIR"],
            "voting_share" => 25,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('business.updateComposition', 34534), $businessComposition)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
