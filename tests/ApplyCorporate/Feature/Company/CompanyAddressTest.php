<?php

namespace Tests\ApplyCorporate\Feature\Company;

use App\Models\Auth\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Business;
use App\Models\CompanyInformation;

class CompanyAddressTest extends TestCase
{
    public $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');
        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub
        ]);

        $this->user = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
    }

    public function testAbleToUpdateAndGetCompanyAddressSection()
    {
        $b = Business::factory()->create(['status' => 'OPENED']);
        $compInfo =  CompanyInformation::create(['business_id' => $b->id]);
        $payload = $this->getBusinessPayload('company-address');

        $create = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->putJson(route('businesses.corporatesave', ['business' => $b->id]), $payload);
        $create->assertStatus(Response::HTTP_OK);

        $response = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $b->id, 'section' => 'address']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($payload['registered_address']['registered_street_number'], $content['data']['registered_address']['registered_street_number']);
        $this->assertEquals($payload['operational_address']['operational_street_number'], $content['data']['operational_address']['operational_street_number']);
        $this->assertEquals($payload['is_same_address'], $content['data']['is_same_address']);
    }

    private function getBusinessPayload($section)
    {
        return [
            "disabled" => "",
            "registered_address" => [
                "registered_street_number" => "Building 120",
                "registered_street_name" => "Fabella high Land",
                "registered_postal_code" => "3485",
                "registered_city" => "Manila",
                "registered_country" => "DOM"
            ],
            "operational_address" => [
                "operational_street_number" => "Highland Fibuorti",
                "operational_street_name" => "Street Fabella",
                "operational_postal_code" => "3845",
                "operational_city" => "taguig",
                "operational_country" => "HUN"
            ],
            "is_same_address" => false,
            "products" => [
                "Credit Card Processing",
                "IBAN4U Payment Account",
                "SEPA Direct Debit"
            ],
            "section" => $section
        ];
    }
}
