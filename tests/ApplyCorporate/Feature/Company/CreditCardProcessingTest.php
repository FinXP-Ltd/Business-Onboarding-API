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

class CreditCardProcessingTest extends TestCase
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

        $payload = $this->getBusinessPayload('acquiring-services');

        $create = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->putJson(route('businesses.corporatesave', ['business' => $b->id]), $payload);
        $create->assertStatus(Response::HTTP_OK);

        $response = $this->impersonate(credential: $this->user, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $b->id, 'section' => 'acquiring-services']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);

        $this->assertEquals($payload['credit_card_processing']['currently_processing_cc_payments'], $content['data']['credit_card_processing']['currently_processing_cc_payments']);
        $this->assertEquals($payload['credit_card_processing']['trading_urls'][0], $content['data']['credit_card_processing']['trading_urls'][0]);
        $this->assertEquals($payload['credit_card_processing']['alternative_payment_methods'], $content['data']['credit_card_processing']['alternative_payment_methods']);
    }

    private function getBusinessPayload($section)
    {
        return [
            "credit_card_processing" => [
                "currently_processing_cc_payments" => "NO",
                "offer_recurring_billing" => "NO",
                "frequency_offer_billing" => "",
                "if_other_offer_billing" => "",
                "offer_refunds" => "NO",
                "frequency_offer_refunds" => "",
                "if_other_offer_refunds" => "",
                "processing_account_primary_currency" => "BIF",
                "average_ticket_amount" => "25",
                "highest_ticket_amount" => "50",
                "alternative_payment_methods" => "GCash App",
                "payment_method_currently_offered" => "Credit Card Gcash",
                "current_mcc" => "Current MCC",
                "current_descriptor" => "Current descriptor",
                "cb_volumes_twelve_months" => "120",
                "sales_volumes_twelve_months" => "34",
                "refund_twelve_months" => "234",
                "current_acquire_psp" => "Current Acquirer",
                "trading_urls" => [
                "https://one.finxp.com/",
                "https://two.finxp.com/"
                ],
                "countries" => [
                [
                    "countries_where_product_offered" => "AIA",
                    "distribution_per_country" => "10"
                ]
                ],
                "trading_urls[1]" => "https://two.finxp.com/"
            ],
            "products" => [
                "Credit Card Processing",
                "IBAN4U Payment Account"
            ],
            "disabled" => "",
            "trading_urls" => [
                "https://one.finxp.com/",
                "https://two.finxp.com/"
            ],
            "section" => $section
        ];
    }
}
