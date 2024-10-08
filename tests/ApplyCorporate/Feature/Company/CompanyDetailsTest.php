<?php

namespace Tests\ApplyCorporate\Feature\Company;

use App\Models\Auth\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Business;
use App\Models\CompanyInformation;

class CompanyDetailsTest extends TestCase
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

    public function testAbleToUpdateAndGetCompanyDetailsSection()
    {
        $b = Business::factory()->create(['status' => 'OPENED']);
        $compInfo =  CompanyInformation::create(['business_id' => $b->id]);

        $payload = $this->getBusinessPayload('company-details');

        $create = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->putJson(route('businesses.corporatesave', ['business' => $b->id]), $payload);
        $create->assertStatus(Response::HTTP_OK);

        $response = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $b->id, 'section' => 'details']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($payload['name'], $content['data']['name']);
        $this->assertEquals($payload['registration_number'], $content['data']['registration_number']);
        $this->assertEquals($payload['vat_number'], $content['data']['vat_number']);
    }

    private function getBusinessPayload($section)
    {
        return [
            "name" => "Test5346",
            "registration_number" => "NU63411",
            "registration_type" => "Partnership",
            "trading_name" => "Company trading as",
            "foundation_date" => "2024-06-05",
            "tax_country" => "AUS",
            "vat_number" => "VA263718",
            "number_employees" => "45",
            "number_of_years" => "35",
            "tax_identification_number" => "IH6274",
            "jurisdiction" => "ARG",
            "industry_key" => "Boat Dealers",
            "business_activity_description" => "Brief description of your business activity",
            "share_capital" => "450",
            "previous_year_turnover" => "Between 1 and 3 Millions",
            "email" => "marjorie.asensi@finxp.com",
            "website" => "https://localhost.ph",
            "additional_website" => "https://liongsd.ph",
            "is_part_of_group" => "YES",
            "parent_holding_company" => "Associate",
            "parent_holding_company_other" => "",
            "has_fiduciary_capacity" => "NO",
            "has_constituting_documents" => "YES",
            "is_company_licensed" => "YES",
            "license_rep_juris" => "BEN",
            "contact_person_name" => "Lion Samonte",
            "contact_person_email" => "moscow@gmail.test",
            "products" => [
                "Credit Card Processing",
                "IBAN4U Payment Account"
            ],
            "section" => $section
        ];
    }
}
