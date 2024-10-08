<?php

namespace Tests\ApplyCorporate\Feature\Company;

use App\Models\Auth\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Business;
use App\Models\CompanyInformation;

class CompanySourcesTest extends TestCase
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
        $b = Business::all()->last();
        $payload = $this->getBusinessPayload('company-sources');

        $create = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->putJson(route('businesses.corporatesave', ['business' => $b->id]), $payload);
        $create->assertStatus(Response::HTTP_OK);

        $response = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $b->id, 'section' => 'sources']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($payload['source_of_funds'], $content['data']['source_of_funds']);
        $this->assertEquals($payload['country_source_of_funds'][0], $content['data']['country_source_of_funds'][0]);
        $this->assertEquals($payload['country_source_of_wealth'][0], $content['data']['country_source_of_wealth'][0]);
    }

    private function getBusinessPayload($section)
    {
        return [
            "disabled"  => "",
            "source_of_funds"  => "Please provide a brief description of the source of funds",
            "country_source_of_funds"  => [
                "AGO",
                "AUT"
            ],
            "source_of_wealth"  => [
                "Savings",
                "Loan",
                "Dividends or profits from company"
            ],
            "source_of_wealth_other"  => "",
            "country_source_of_wealth"  => [
                "ALB",
                "BLZ"
            ],
            "products"  => [
                "Credit Card Processing",
                "IBAN4U Payment Account"
            ],
            "section" => $section
        ];
    }
}
