<?php

namespace Tests\ApplyCorporate\Feature\Business;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\BusinessDetail;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Response;
use Tests\TestCase;

class BusinessProductUpdateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldUpdateBusinessProduct()
    {
        $payload = [
            'section' => 'company-products',
            'name' => 'Finxp Limited',
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

        $business = Business::all()->last();

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('businesses.corporateproduct.update', $business->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'status' => Response::HTTP_OK,
                'message' =>  'Product Updated.'
        ]);
    }

    public function testShouldValidateUpdatingBusinessProduct()
    {
        $b = Business::factory()->create(['status' => 'OPENED']);

        $payload = [
            'section' => 'company-products',
            'products' => [],
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
            ->putJson(route('businesses.corporateproduct.update', $b->id), $payload)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'code' => 422,
                'status' => 'failed',
                'message' =>  ["products" => ['The products field is required.']]
        ]);
    }

    public function testShouldThrowErrorIfFinxpProductsDoesNotExist()
    {
        $b = Business::factory()->create(['status' => 'OPENED']);

        $payload = [
            'section' => 'company-products',
            'products' => ['SOMETHING'],
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
            ->putJson(route('businesses.corporateproduct.update', $b->id), $payload)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
           ->assertJson([
                'code' => 422,
                'status' => 'failed',
                'message' =>  ["products" => [ 'The selected products is invalid.']]
        ]);
    }
}
