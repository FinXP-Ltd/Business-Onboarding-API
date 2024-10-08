<?php

namespace Tests\Onboarding\Feature\NonNaturalPerson;

use Illuminate\Http\Response;
use Tests\TestCase;
use App\Exceptions\NaturalPersonException;
use App\Models\Auth\User;
use App\Models\NonNaturalPerson\NonNaturalPerson;

class NonNaturalPersonShowTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testItShouldShowNonNaturalPerson()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $origPerson = NonNaturalPerson::factory()->create([
            'user_id' => $user->id
        ]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('nonNatural.show', $origPerson->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'name',
                    'registration_number',
                    'date_of_incorporation',
                    'country_of_incorporation',
                    'address' =>[
                        '*' => [
                            'line_1',
                            'line_2',
                            'locality',
                            'postal_code',
                            'country',
                            'licensed_reputable_jurisdiction'
                        ]
                    ],
                    'company_shareholding'
                ],
            ]);

        $personModel = NonNaturalPerson::find($origPerson->id);
        $this->assertEquals($origPerson->id, $personModel->id);
    }

    public function testItShouldErrorOnNotFoundNonNaturalPerson()
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
            ->getJson(route('nonNatural.show', 99))
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }
}
