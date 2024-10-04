<?php

namespace Tests\Onboarding\Feature\Person;

use Illuminate\Http\Response;
use Tests\TestCase;
use App\Exceptions\NaturalPersonException;
use App\Models\Auth\User;
use GuzzleHttp\Psr7\Response as Psr7Response;
use App\Models\Person\AdditionalInfo;
use App\Models\Person\NaturalPerson;
use App\Models\Document;
use App\Models\Person\NaturalPersonAddresses;
use App\Models\Person\NaturalPersonIdentificationDocument;

class PersonCreateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();

    }


    public function testShouldCreateNewPerson()
    {
        $person = $this->personPayload();

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
            ->postJson(route('natural.store'), $person)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['person_id'],
            ]);
    }

    public function testItShouldThrowNaturalPersonExceptionOnCreateNewPerson()
    {
        $person = [
            'title' => 'Mrs',
            'name' => 'Vivian',
            'surname' => 'Stroman',
            'sex' => 'female',
            'date_of_birth' => '1988-12-11',
            'place_of_birth' => 'DEU',
            'country_code' => 'CD',
            'email_address' => 'alba21@example.com',
        ];

        NaturalPerson::factory()->create($person);
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
            ->postJson(route('natural.store'), $person)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }

    public function testItShouldThrowValidationErrorOnMissingRequired()
    {
        $person = $this->personPayload();
        $person['name'] =null;

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
            ->postJson(route('natural.store'), $person)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }

    public function testShouldCreateNewPersonWithTheSameNameButDiffBirthdate()
    {
        $dupPerson = NaturalPerson::factory()->create([
            'name' => 'Vivian',
            'surname' => 'Stroman',
            'date_of_birth' => '1988-12-10' //current 1988-12-11
        ]);

        $person = $this->personPayload();

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
            ->postJson(route('natural.store'), $person)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['person_id']
            ]);
    }

    public function testShouldThrowValidationErrorOnNewPersonWithSameNameSurnameAndDateOfBirthOfTheExistingPerson()
    {
        $person = [
            'title' => 'Mrs',
            'name' => 'Vivian',
            'surname' => 'Stroman',
            'sex' => 'female',
            'date_of_birth' => '1988-12-11',
            'place_of_birth' => 'DEU',
            'country_code' => 'CD',
            'email_address' => 'alba21@example.com'
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
            ->postJson(route('natural.store'), $person)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
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

        $np = NaturalPerson::factory()->create([
            'user_id' => $user->id
        ]);

        $address = NaturalPersonAddresses::factory()->create([
            'natural_person_id' => $np->id
        ]);

        $np_model = NaturalPerson::find($np->id);

        $this->assertInstanceOf(NaturalPersonAddresses::class, $np_model->addresses()->first());
        $this->assertEquals($address->natural_person_id, $np_model->id);
    }

    public function testItShouldHaveIdentificationDocuments()
    {
        $authUserToken = $this->getTokenPayload('client');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $np = NaturalPerson::factory()->create([
            'user_id' => $user->id
        ]);

        $docs = NaturalPersonIdentificationDocument::factory()->create([
            'natural_person_id' => $np->id
        ]);

        $np_model = NaturalPerson::find($np->id);

        $this->assertInstanceOf(NaturalPersonIdentificationDocument::class, $np_model->identificationDocument()->first());
        $this->assertEquals($docs->natural_person_id, $np_model->id);
    }

    public function testItShouldHaveAddtInfo()
    {
        $authUserToken = $this->getTokenPayload('client');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $np = NaturalPerson::factory()->create([
            'user_id' => $user->id
        ]);

        $addt = AdditionalInfo::factory()->create([
            'natural_person_id' => $np->id
        ]);

        $np_model = NaturalPerson::find($np->id);

        $this->assertInstanceOf(AdditionalInfo::class, $np_model->additionalInfos()->first());
        $this->assertEquals($addt->natural_person_id, $np_model->id);
    }

    public function testItShouldHaveDocuments()
    {
        $authUserToken = $this->getTokenPayload('client');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $np = NaturalPerson::factory()->create([
            'user_id' => $user->id
        ]);

        $address = Document::factory()->create([
            'document_type' => 'other',
            'documentable_id' => $np->id,
            'documentable_type' => 'App\\Models\\Person\\NaturalPerson'
        ]);

        $np_model = NaturalPerson::find($np->id);

        $this->assertInstanceOf(Document::class, $np_model->documents()->first());
        $this->assertEquals($address->non_natural_person_id, $np_model->documentable_id);
    }

    private function personPayload()
    {
        return [
            'title' => 'Mrs',
            'name' => 'Vivian',
            'surname' => 'Stroman',
            'sex' => 'female',
            'date_of_birth' => '1988-12-11',
            'place_of_birth' => 'DEU',
            'country_code' => 'CD',
            'email_address' => 'alba21@example.com',
            'mobile' => '090481250816931',
            'address' => [
              'line_1' => 'egXlI',
              'line_2' => 'eXO6e',
              'locality' => 'DGxQj',
              'postal_code' => 'ELojr',
              'country' => 'DEU',
              'nationality' => 'DEU'
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
              'pep' => 1,
              'tin' => 'kZA0kqkixyyqN',
              'country_tax' => 'VUX'
            ]
        ];
    }
}
