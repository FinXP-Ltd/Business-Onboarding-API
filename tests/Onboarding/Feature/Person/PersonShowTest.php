<?php

namespace Tests\Onboarding\Feature\Person;

use Illuminate\Http\Response;
use Tests\TestCase;
use App\Exceptions\NaturalPersonException;
use App\Models\Auth\User;
use App\Models\Person\AdditionalInfo;
use App\Models\Person\NaturalPerson;
use App\Models\Person\NaturalPersonAddresses;
use App\Models\Person\NaturalPersonIdentificationDocument;

class PersonShowTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testItShouldShowPerson()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $origPerson = NaturalPerson::factory()->create([
            'user_id' => $user->id
        ]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->getJson(route('natural.show', $origPerson->id))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'title',
                    'name',
                    'surname',
                    'sex',
                    'date_of_birth',
                    'place_of_birth',
                    'email_address',
                    'country_code',
                    'mobile'
                ],
            ]);

        $personModel = NaturalPerson::find($origPerson->id);
        $this->assertEquals($origPerson->id, $personModel->id);
    }

    public function testItShouldErrorOnNotFoundNaturalPerson()
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
            ->getJson(route('natural.show', 99))
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }
}
