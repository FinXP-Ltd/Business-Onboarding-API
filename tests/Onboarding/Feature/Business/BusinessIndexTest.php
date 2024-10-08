<?php

namespace Tests\Onboarding\Feature\Business;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\Document;
use App\Models\BusinessComposition;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson as NonPerson;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class BusinessIndexTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldReturnBusinessesListCreatedByCurrentMemberUser()
    {
        $memberPayload = $this->getTokenPayload('invited client');

        $sub = $memberPayload['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $memberBusinesses = Business::factory(4)
            ->hasTaxInformation()
            ->hasBusinessCompositions()
            ->create([ 'user' => $user->id ]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $memberPayload,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->get(route('business.index'))
        ->assertJson(function (AssertableJson $json) use ($memberBusinesses, $user) {
           return $json
            ->has('status')
            ->has('message')
            ->has('data', $memberBusinesses->count(), fn ($json) => $json->where('created_by', $user->id)->etc());
        });
    }

    public function testShouldReturnAllBusinessesListIfCurrentUserContainsAdminRole()
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
            ->get(route('business.index'))
            ->assertJsonCount(Business::all()->count(), 'data');
    }
}
