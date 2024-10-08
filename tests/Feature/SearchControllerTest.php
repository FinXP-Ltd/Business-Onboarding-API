<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testReturnEntitiesOwnedByTheUser()
    {
        $search = ['name' => 'Finxp'];

        $authUserToken = $this->getTokenPayload('client');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $naturalPerson = NaturalPerson::factory()->create([
            'name' => 'Finxp',
            'user_id' => $user->id
        ])->first();

        $this->assertEquals($user->auth0, $sub);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->postJson(route('search.person'), $search)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) use ($naturalPerson) {
            return $json
                ->has('status')
                ->has('message')
                ->has('data', $naturalPerson->count(), fn ($json) => $json->where('id', $naturalPerson->id)->etc());
        });
    }

    public function testReturnEmptyNotOwnedByTheUser()
    {
        $search = ['name' => 'Finxp'];

        $user = User::factory()->create();
        $naturalPerson = NaturalPerson::factory()->create([
            'name' => 'Finxp',
            'user_id' => $user->id
        ])->first();

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
            ->postJson(route('search.person'), $search)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) {
            return $json
                ->has('status')
                ->has('message')
                ->has('data', 0);
        });
    }

    public function testReturnSearchTerm()
    {
        $search = ['name' => 'Nonnatural'];

        $authUserToken = $this->getTokenPayload('client');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $nonNatural = NonNaturalPerson::factory()->create([
            'name' => 'Nonnatural',
            'user_id' => $user->id
        ])->first();

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->assertEquals($user->auth0, $sub);

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->postJson(route('search.person'), $search)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson(function (AssertableJson $json) use ($nonNatural) {
            return $json
                ->has('status')
                ->has('message')
                ->has('data', $nonNatural->count(), fn ($json) => $json->where('id', $nonNatural->id)->etc());
        });
    }

}
