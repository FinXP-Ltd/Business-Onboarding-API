<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\LookupType;
use Illuminate\Http\Response;
use Tests\TestCase;

class LookupTypeControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldShowAllLookupKeys()
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

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('lookup.enums.list'))
            ->assertStatus(Response::HTTP_OK);


        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('group', $content[0]);
        $this->assertArrayHasKey('label', $content[0]);

    }

    public function testFetchTheListOfBusinessPosition()
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

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('lookup.group.list', 'BUSINESS_POSITION'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'data' => [
                'UBO',
                'SH',
                'SH_CORPORATE',
                'DIR',
                'DIR_CORPORATE',
                'SIG',
                'B',
            ]
        ]);
    }

    public function testFetchTheListOfRegistrationType()
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

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('lookup.group.list', 'REGISTRATION_TYPE'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'data' => [
                'TRADING',
                'HOLDING',
                'PARTNERSHIP',
                'FOUNDATION',
                'CHARITIES',
                'TRUST',
                'PUBLIC',
                'LIMITED'
            ]
        ]);
    }

    public function testFetchTheListOfLicenseRepJuris()
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

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('lookup.group.list', 'LICENSE_REP_JURIS'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'data' => [
                'YES',
                'NO',
                'LICENSE_NOT_REQUIRED'
            ]
        ]);
    }

    public function testFetchTheListOfStatuses()
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

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('lookup.group.list', 'STATUS'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'data' => [
                'OPENED',
                'SUBMITTED',
                'INPUTTING'
            ]
        ]);
    }

    public function testFetchTheListOfFinxpProducts()
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

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('lookup.group.list', 'FINXP_PRODUCTS'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'data' => [
                'IBAN4U Payment Account',
                'Credit Card Processing',
                'SEPA Direct Debit'
            ]
        ]);
    }

    public function testFetchTheListOfPaymentMethods()
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

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('lookup.group.list', 'PAYMENT_METHOD'))
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([
            'code' => Response::HTTP_OK,
            'status' => 'success',
            'data' => [
                'PAYPAL',
                'SOFORT',
                'SKRILL',
                'GIRO',
                'RTP',
                'OTHER'
            ]
        ]);
    }
}
