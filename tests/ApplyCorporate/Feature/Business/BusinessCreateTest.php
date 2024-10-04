<?php

namespace Tests\ApplyCorporate\Feature\Business;

use App\Models\Auth\User;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Support\Str;

class BusinessCreateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldCreateNewBusinessApplyCorporate()
    {
        $payload = [
            'name' => 'Test',
            'corporate_saving' => true
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub
        ]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->postJson(route('businesses.corporatecreate'), $payload)
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['company_id'],
        ]);
    }

    public function testShouldCreateInDuplicateWithNameOnly()
    {
        $payload = [
            'name' => 'Test',
            'corporate_saving' => true
        ];

        $authUserToken =  $this->getTokenPayload('agent', 'client_id.app');
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
            ->postJson(route('businesses.corporatecreate'), $payload)
            ->assertStatus(Response::HTTP_CREATED);

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->postJson(route('businesses.corporatecreate'), $payload)
            ->assertStatus(Response::HTTP_CREATED);
    }

}
