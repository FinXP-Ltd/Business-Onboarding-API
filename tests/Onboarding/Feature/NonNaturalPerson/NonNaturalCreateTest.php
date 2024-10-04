<?php

namespace Tests\Onboarding\Feature\NonNaturalPerson;

use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Auth\User;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPersonAddresses;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Support\Str;

class NonNaturalCreateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldCreateNewNonNaturalPerson()
    {
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
            ],
        ];

        $authUserToken = $this->getTokenPayload('client');

        $sub = $authUserToken['sub'];

        $this->auth0Mock->shouldReceive('management->users->get')
            ->once()
            ->with($sub)
            ->andReturn(new Psr7Response(200, [], json_encode([
                'user_id' => $sub,
                'name' => 'Asensi, Marjorie',
                'email' => 'marjorie.asensi@finxp.com'
            ])));

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
            ->postJson(route('nonNatural.store'), $nonNatural)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['non_natural_person_id'],
            ]);
    }

    public function testItShouldHaveAddresses()
    {
        $authUserToken = $this->getTokenPayload('client');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $non_np = NonNaturalPerson::factory()->create([
            'user_id' => $user->id
        ]);

        $address = NonNaturalPersonAddresses::factory()->create([
            'non_natural_person_id' => $non_np->id
        ]);

        $non_np_model = NonNaturalPerson::find($non_np->id);

        $this->assertInstanceOf(NonNaturalPersonAddresses::class, $non_np_model->addresses()->first());
        $this->assertEquals($address->non_natural_person_id, $non_np_model->id);
    }

    public function testItShouldThrowNaturalPersonExceptionOnCreateNewNonNaturalPerson()
    {
        NonNaturalPerson::factory()->create([
            'name' => 'ACB Limited',
        ]);

        $nonNatural = [
            'name' => 'ACB Limited',
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
            ],
            'user_id' => Str::uuid()->toString()
        ];
        $authUserToken = $this->getTokenPayload('client');

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
            ->postJson(route('nonNatural.store'), $nonNatural)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }

    public function testItShouldThrowValidationErrorOnMissingRequired()
    {
        $person = [
            'title' => 'Mrs',
            'name' => 'Vivian',
            'surname' => 'Stroman',
            'sex' => 'female',
            'date_of_birth' => '1988-12-11',
            'email_address' => 'alba21@example.com',
            'mobile' => '090481250816931',
            'address' => [
              'line_1' => 'egXlI',
              'line_2' => 'eXO6e',
              'locality' => 'DGxQj',
              'postal_code' => 'ELojr',
              'country' => 'vbe',
              'nationality' => 'ukB'
            ],
            'identification_document' => [
              'document_type' => 't0Q',
              'document_number' => 'pAKFSNHfItKTf',
              'document_country_of_issue' => 'PHL',
              'document_expiry_date' => '2010-03-15'
            ],
            'additional_info' => [
              'occupation' => '03PX5',
              'employment' => '7xLSG',
              'position' => 'DtJS8',
              'source_of_income' => '58U9R',
              'source_of_wealth' => 'MmM',
              'source_of_wealth_details' => 'beY',
              'other_source_of_wealth_details' => 'yNT',
              'us_citizenship' => true,
              'pep' => 'Yes',
              'tin' => 'kZA0kqkixyyqN',
              'country_tax' => 'VUX'
            ],
            'user_id' => Str::uuid()->toString()
        ];
        $authUserToken = $this->getTokenPayload('client');

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
            ->postJson(route('nonNatural.store'), $person)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }
}
