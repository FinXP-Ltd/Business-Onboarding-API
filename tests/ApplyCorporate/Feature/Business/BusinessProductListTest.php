<?php

namespace Tests\ApplyCorporate\Feature\Business;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\BusinessDetail;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Response;
use Tests\TestCase;

class BusinessProductListTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldListTheBusinessProduct()
    {
        $b = Business::factory()->create(['status' => 'OPENED']);

        $payload = [
            'section' => 'company-products',
            'products' => ['IBAN4U Payment Account'],
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
            ->postJson(route('businesses.corporateproduct.update', $b->id), $payload)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson([
                'code' => Response::HTTP_CREATED,
                'status' => 'success',
                'message' =>  'Products Created!'
        ]);

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('businesses.corporateproduct.index', $b->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'code',
            'status',
            'data'
        ]);

    }
}
