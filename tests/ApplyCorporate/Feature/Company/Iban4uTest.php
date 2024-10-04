<?php

namespace Tests\ApplyCorporate\Feature\Company;

use App\Models\Auth\User;
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\Business;
use App\Models\CompanyCreditCardProcessing;
use App\Models\CompanyIban4UAccount;
use App\Models\CompanyIban4UAccountActivity;
use App\Models\CompanyIban4UAccountCountry;
use App\Models\CompanyInformation;

class Iban4uTest extends TestCase
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

        $iban4u = CompanyIban4UAccount::create([
            'business_id' => $b->id,
            'company_information_id' => $compInfo->id
        ]);
             CompanyCreditCardProcessing::create(['company_information_id' => $compInfo->id]);
        CompanyIban4UAccountActivity::create(['company_iban4u_account_id' => $iban4u->id]);
        CompanyIban4UAccountCountry::create(['company_iban4u_account_id' => $iban4u->id]);

        $payload = $this->getBusinessPayload('iban4u-payment-account');

        $create = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->putJson(route('businesses.corporatesave', ['business' => $b->id]), $payload);
        $create->assertStatus(Response::HTTP_OK);

        $response = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $b->id, 'section' => 'iban4u']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($payload['iban4u_payment_account']['purpose_of_account_opening'], $content['data']['iban4u_payment_account']['purpose_of_account_opening']);
        $this->assertEquals($payload['iban4u_payment_account']['annual_turnover'], $content['data']['iban4u_payment_account']['annual_turnover']);
        $this->assertEquals($payload['iban4u_payment_account']['activity']['incoming_payments'][0]['name'], $content['data']['iban4u_payment_account']['activity']['incoming_payments'][0]['name']);
    }

    private function getBusinessPayload($section)
    {
        return [
            "iban4u_payment_account"  => [
                "purpose_of_account_opening"  => "Purpose of opening account",
                "annual_turnover"  => "455",
                "deposit"  => [
                "trading"  => [
                    "EU/EAA"
                ],
                "countries"  => [
                    "NOR",
                    "MLT"
                ],
                "approximate_per_month"  => "1-5",
                "cumulative_per_month"  => "50"
                ],
                "withdrawal"  => [
                "trading"  => [
                    "EU/EAA"
                ],
                "countries"  => [
                    "LTU",
                    "POL"
                ],
                "approximate_per_month"  => "5-10",
                "cumulative_per_month"  => "345"
                ],
                "activity"  => [
                "incoming_payments"  => [
                    [
                    "name"  => "Incoming one",
                    "country"  => "ASM"
                    ]
                ],
                "outgoing_payments"  => [
                    [
                    "name"  => "Outgoing One",
                    "country"  => "BMU"
                    ]
                ],
                "held_accounts"  => "No other bank accounts held",
                "held_accounts_description"  => "",
                "refused_banking_relationship"  => "NO",
                "refused_banking_relationship_description"  => "",
                "terminated_banking_relationship"  => "NO",
                "terminated_banking_relationship_description"  => ""
                ]
            ],
            "disabled"  => false,
            "products"  => [
                "Credit Card Processing",
                "IBAN4U Payment Account"
            ],
            "section"  =>  $section
        ];
    }
}
