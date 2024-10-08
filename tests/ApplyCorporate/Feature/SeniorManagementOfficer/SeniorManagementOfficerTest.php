<?php

namespace Tests\ApplyCorporate\Feature\SeniorManagementOfficer;


use App\Models\Auth\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Business;
use App\Models\CompanyInformation;
use App\Models\CompanyIban4UAccount;
use App\Models\CompanyIban4UAccountActivity;
use App\Models\CompanyIban4UAccountCountry;
use App\Models\CompanySepaDd;
use App\Models\CompanyCreditCardProcessing;
use App\Models\SeniorManagementOfficer;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Support\Str;
class SeniorManagementOfficerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testToAddSeniorManagementOfficer()
    {
        $b = Business::factory()->create(['status' => 'OPENED']);
        $compInfo =  CompanyInformation::create(['business_id' => $b->id]);

        $iban4u = CompanyIban4UAccount::create([
            'business_id' => $b->id,
            'company_information_id' => $compInfo->id
        ]);

        CompanySepaDd::create(['company_information_id' => $compInfo->id]);
        CompanyCreditCardProcessing::create(['company_information_id' => $compInfo->id]);
        CompanyIban4UAccountActivity::create(['company_iban4u_account_id' => $iban4u->id]);
        CompanyIban4UAccountCountry::create(['company_iban4u_account_id' => $iban4u->id]);

        $senior = SeniorManagementOfficer::create(['company_information_id' => $compInfo->id]);

        $payload= [
            "company_representative" => [],
            "senior_management_officer" => $this->getSeniorManagementOfficer(),
            "section" => "company_representatives",
            "corporate_saving" => true
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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', $b->id, 'representative'));

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Sally', $content['data']['senior_management_officer']['first_name']);
        $this->assertEquals('MLT', $content['data']['senior_management_officer']['place_of_birth']);
    }

    protected function getSeniorManagementOfficer()
    {
        return [
            'first_name' => 'Sally',
            'surname' => 'Tbugil',
            'date_of_birth' => '1990-03-12',
            'place_of_birth' => 'MLT',
            'email_address' => 'sally@test.com',
            'nationality' => 'DEU',
            'citizenship' => 'MLT',
            'phone_number' => '47562112',
            'phone_code' => '+639',
            'roles_in_company' => '',
            'residential_address' => [
            'street_number' => '123123',
            'street_name' => 'Block',
            'postal_code' => '3255',
            'city' => 'Manila',
            'country' => 'PHL',
            ],
            'identity_information' => [
            'id_type' => 'Passport',
            'country_of_issue' => 'DEU',
            'id_number' => 'GH763584',
            'document_date_issued' => '1991-08-11',
            'document_expired_date' => '2025-01-20',
            'high_net_worth' => '450',
            'politically_exposed_person' => 'YES'
            ],
            'senior_management_officer_document' => [
            'proof_of_address' => 'Block Area 231',
            'identity_document' => 'Passport'
            ]
        ];
    }
}
