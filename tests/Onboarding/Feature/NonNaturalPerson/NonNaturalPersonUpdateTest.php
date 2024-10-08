<?php

namespace Tests\Onboarding\Feature\NonNaturalPerson;

use Illuminate\Http\Response;
use Tests\TestCase;
use App\Exceptions\NonNaturalPersonException;
use App\Models\Auth\User;
use App\Models\NonNaturalPerson\NonNaturalPerson;

class NonNaturalPersonUpdateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testItShouldUpdateNonNaturalPerson()
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

        $nonNatural = [
            'name' => 'Elian Brown V',
            'registration_number' => '389687510078077',
            'date_of_incorporation' => '2018-05-15',
            'country_of_incorporation' => 'DEU',
            'name_of_shareholder_percent_held' => 'Dr. Thora Lakin Jr.',
            'address' => [
                'line_1' => 'Line 1',
                'line_2' => 'Line 2',
                'locality' => 'ABC123',
                'postal_code' => '123',
                'country' => 'AD'
            ]
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('nonNatural.update', $origPerson->id), $nonNatural)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['non_natural_person_id'],
            ]);

        $personModel = NonNaturalPerson::find($origPerson->id);

        $this->assertEquals($personModel->name, $nonNatural['name']);
    }

    public function testItShouldThrowNonNaturalPersonExceptionOnUpdatePersonWithDuplicate()
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

        $dupPerson = NonNaturalPerson::factory()->create([
            'name' => 'Elian Brown V',
            'user_id' => $user->id
        ]);

        $nonNatural = [
            'name' => 'Elian Brown V',
            'registration_number' => '389687510078077',
            'date_of_incorporation' => '2018-05-15',
            'country_of_incorporation' => 'DEU',
            'name_of_shareholder_percent_held' => 'Dr. Thora Lakin Jr.',
            'address' => [
                'line_1' => 'Line 1',
                'line_2' => 'Line 2',
                'city' => 'Amsterdam',
                'locality' => 'ABC123',
                'postal_code' => '123',
                'country' => 'AD'
            ]
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('nonNatural.update', $origPerson->id), $nonNatural)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }

    public function testItShouldThrowValidationErrorOnMissingRequired()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $origPerson = NonNaturalPerson::factory()->create(['user_id' => $user->id]);

        $nonNatural = [
            'name' => 'Elian Brown V',
            'registration_number' => '389687510078077',
            'date_of_incorporation' => '2018-05-15',
            'country_of_incorporation' => 'DEU',
            'name_of_shareholder_percent_held' => 'Dr. Thora Lakin Jr.',
            'address' => [
                'line_1' => 'Line 1',
                'line_2' => 'Line 2',
                'city' => 'Amsterdam',
                'postal_code' => '123',
                'country' => 'AD'
            ]
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('nonNatural.update', $origPerson->id), $nonNatural)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }
}
