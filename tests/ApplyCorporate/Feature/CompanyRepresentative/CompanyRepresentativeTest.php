<?php

namespace Tests\ApplyCorporate\Feature\CompanyRepresentative;

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
use App\Models\CompanyRepresentative;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Support\Str;

class CompanyRepresentativeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testToAddCompanyRepresentative()
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

        $companyRep = CompanyRepresentative::create(['company_information_id' => $compInfo->id]);

        $payload= [
            "company_representative" => [$this->getCompanyRepresentative()],
            "senior_management_officer" => [],
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
        $this->assertEquals('Andrew', $content['data']['company_representative'][0]['first_name']);
        $this->assertEquals('Enqiruez', $content['data']['company_representative'][0]['surname']);
    }

    protected function getCompanyRepresentative()
    {
        return [
            'first_name' => 'Andrew',
            'surname' => 'Enqiruez',
            'middle_name' => 'Salvador',
            'date_of_birth' => '1990-04-08',
            'place_of_birth' => 'Moscow',
            'email_address' => 'raman@russia.edu',
            'nationality' => 'MSK',
            'citizenship' => 'DEU',
            'phone_code' => '+376',
            'phone_number' => '34534534534',
            'roles' => [
                [
                'roles_in_company' => 'UBO',
                'percent_ownership' => '45',
                'iban4u_rights' => 'UBO'
                ]
            ],
            'residential_address' => [
                'street_number' => 'Manila',
                'street_name' => 'Fabella',
                'postal_code' => '2342',
                'city' => 'mandaluyong',
                'country' => 'SGP'
            ],
            'identity_information' => [
                'id_type' => 'Passport',
                'country_of_issue' => 'AND',
                'id_number' => 'ID6663GDH',
                'document_date_issued' => '21-04-2024',
                'document_expired_date' => '04-04-2023',
                'high_net_worth' => 'YES',
                'us_citizenship' => 'NO',
                'politically_exposed_person' => 'NO'
            ],
            'company_representative_document' => [
                'proof_of_address' => 'Company PCI certificate.pdf',
                'identity_document' => 'Copy of Bank settlement.pdf',
                'identity_document_addt' => 'Third Party Questionnaire.pdf',
                'source_of_wealth' => ''
            ]
        ];
    }
}
