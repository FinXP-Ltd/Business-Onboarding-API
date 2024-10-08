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

class PersonUpdateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testItShouldUpdatePerson()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $origPerson = NaturalPerson::factory()
            ->hasAddresses(1)
            ->hasIdentificationDocument(1)
            ->hasAdditionalInfos(1)
            ->create([
                'user_id' => $user->id
            ]);

        $person = [
            'title' => 'Mr',
            'name' => 'Harry',
            'surname' => 'Potter',
            'sex' => 'male',
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
              'pep' => 1,
              'tin' => 'kZA0kqkixyyqN',
              'country_tax' => 'VUX'
            ]
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('natural.update', $origPerson->id), $person)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['person_id'],
            ]);

        $personModel = NaturalPerson::find($origPerson->id);

        $this->assertEquals($personModel->name, $person['name']);
    }

    public function testItShouldThrowNaturalPersonExceptionOnUpdatePersonWithDuplicate()
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

        $dupPerson = NaturalPerson::factory()->create([
            'name' => 'Harry',
            'surname' => 'Potter',
            'date_of_birth' => '1988-12-11',
            'user_id' => $user->id
        ]);

        $person = [
            'title' => 'Mrs',
            'name' => 'Harry',
            'surname' => 'Potter',
            'sex' => 'female',
            'date_of_birth' => '1988-12-11',
            'place_of_birth' => 'MLT',
            'country_code' => 'CD',
            'email_address' => 'alba21@example.com',
            'mobile' => '090481250816931',
            'address' => [
              'line_1' => 'egXlI',
              'line_2' => 'eXO6e',
              'locality' => 'DGxQj',
              'postal_code' => 'ELojr',
              'country' => 'MLT',
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
              'pep' => 1,
              'tin' => 'kZA0kqkixyyqN',
              'country_tax' => 'VUX'
            ]
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('natural.update', $origPerson->id), $person)
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

        $origPerson = NaturalPerson::factory()->create([
            'user_id' => $user->id
        ]);

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
              'country' => 'DEU',
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
              'pep' => 1,
              'tin' => 'kZA0kqkixyyqN',
              'country_tax' => 'VUX'
            ]
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('natural.update', $origPerson->id), $person)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }

    public function testItShouldUpdateIfNameAreTheSameWithDiffBirthdate()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $origPerson = NaturalPerson::factory()
            ->hasAddresses(1)
            ->hasIdentificationDocument(1)
            ->hasAdditionalInfos(1)
            ->create(['user_id' => $user->id]);

        $person = [
            'title' => 'Mrs',
            'name' => 'Harry',
            'surname' => 'Potter',
            'sex' => 'female',
            'date_of_birth' => '1988-12-11',
            'place_of_birth' => 'MLT',
            'country_code' => 'CD',
            'email_address' => 'alba21@example.com',
            'mobile' => '090481250816931',
            'address' => [
              'line_1' => 'egXlI',
              'line_2' => 'eXO6e',
              'locality' => 'DGxQj',
              'postal_code' => 'ELojr',
              'country' => 'DEU',
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
              'pep' => 1,
              'tin' => 'kZA0kqkixyyqN',
              'country_tax' => 'VUX'
            ]
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->putJson(route('natural.update', $origPerson->id), $person)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['person_id']
            ]);
    }
}
