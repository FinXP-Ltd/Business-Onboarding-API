<?php

namespace Tests\ApplyCorporate\Feature\Company;

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
use App\Models\DataProtectionMarketing;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Support\Str;

class GetSectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testGetSectionCompanyDetails()
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
        DataProtectionMarketing::create(['company_information_id' => $compInfo->id]);

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
            ->putJson(route('businesses.corporatesave', $b->id), $this->getCompanyDetails())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', $b->id, 'details'));

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('FinXP Limited', $content['data']['company_details']['name']);
        $this->assertEquals('ASFEDER24', $content['data']['company_details']['registration_number']);
        $this->assertEquals('VAT5DS634', $content['data']['company_details']['vat_number']);
    }

    public function testGetSectionCompanyAddress()
    {
        $b = Business::all()->last();

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
            ->putJson(route('businesses.corporatesave', $b->id), $this->getCompanyAddress())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', $b->id, 'address'));

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Test registered_address', $content['data']['registered_address']['registered_street_number']);
        $this->assertEquals('Test operational_address', $content['data']['operational_address']['operational_street_number']);
    }

    public function testGetSectionCompanySources()
    {
        $b = Business::all()->last();

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
            ->putJson(route('businesses.corporatesave', $b->id), $this->getCompanySources())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', $b->id, 'sources'));

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('provide a brief description of the source of funds', $content['data']['company_sources']['source_of_funds']);
    }

    public function testGetSectionIban4uPaymentAccount()
    {
        $b = Business::all()->last();

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
            ->putJson(route('businesses.corporatesave', $b->id), $this->getIban4uPaymentAccount())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', $b->id, 'iban4u'));

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('purpose_of_account_opening', $content['data']['iban4u_payment_account']['purpose_of_account_opening']);
        $this->assertEquals($b->id, $content['data']['iban4u_payment_account']['business_id']);
        $this->assertEquals('YES', $content['data']['iban4u_payment_account']['activity']['terminated_banking_relationship']);
        $this->assertEquals('YES', $content['data']['iban4u_payment_account']['activity']['refused_banking_relationship']);
    }

    public function testGetSectionAcquiringServices()
    {
        $b = Business::all()->last();

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
            ->putJson(route('businesses.corporatesave', $b->id), $this->getAcquiringServices())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', $b->id, 'acquiring-services'));

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('NO', $content['data']['credit_card_processing']['currently_processing_cc_payments']);
        $this->assertEquals('YES', $content['data']['credit_card_processing']['offer_recurring_billing']);
    }

    public function testGetSectionDataProtectionMarketing()
    {
        $b = Business::all()->last();

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
            ->putJson(route('businesses.corporatesave', $b->id), $this->getDataProtectionMarketing())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', $b->id, 'data-protection-marketing'));

        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('YES', $content['data']['data_protection_marketing']['receive_messages_from_finxp']);
        $this->assertEquals('YES', $content['data']['data_protection_marketing']['receive_market_research_survey']);
    }

    private function getCompanyDetails()
    {
        return [
            'name' => 'FinXP Limited',
            'registration_number' => 'ASFEDER24',
            'registration_type' => 'Foundation',
            'trading_name' => 'Cakeie',
            'foundation_date' => '2010-11-15',
            'tax_country' => 'GBR',
            'vat_number' => 'VAT5DS634',
            'number_employees' => '12',
            'tax_identification_number' => 'TAX3883D4D4',
            'jurisdiction' => 'GBR',
            'industry_key' => 'Betting (including Lottery Tickets Casino Gaming Chips Off-track Betting and Wagers)',
            'share_capital' => '320',
            'previous_year_turnover' => 'Less than 1 Million',
            'email' => 'bonchonensi@finxp.com',
            'website' => 'www.yahoo.com',
            'additional_website' => 'www.yahoo.com',
            'is_part_of_group' => 'NO',
            'parent_holding_company' => 'Subsidiary',
            'parent_holding_company_other' => 'YES',
            'has_fiduciary_capacity' => 'NO',
            'has_constituting_documents' => 'NO',
            'is_company_licensed' => 'YES',
            'license_rep_juris' => 'GBR',
            'contact_person_name' => 'Liliam Santos',
            'contact_person_email' => 'bonchonensi@finxp.com',
            'products' => [
                'SEPA Direct Debit'
            ],
            'section' => 'company-details',
            'corporate_saving' => true
        ];
    }

    private function getCompanyAddress()
    {
        return [
           "disabled" => false,
            "registered_address"=> [
                "registered_street_number" => "Test registered_address",
                "registered_street_name" => "fintechmakersg",
                "registered_postal_code" => "16278",
                "registered_city" => "Angermünde",
                "registered_country" => "ALB"
            ],
            "operational_address" => [
                "operational_street_number" => "Test operational_address",
                "operational_street_name" => "Test",
                "operational_postal_code" => "16278",
                "operational_city" => "Angermünde",
                "operational_country" => "ALB"
            ],
            "is_same_address"=> true,
            "products"=> [
                "SEPA Direct Debit",
                "Credit Card Processing",
                "IBAN4U Payment Account"
            ],
            "section"=> "company-address"
        ];
    }

    private function getCompanySources()
    {
        return [
            "source_of_funds" => "provide a brief description of the source of funds",
            "country_source_of_funds" => [
                "AFG"
            ],
            "source_of_wealth" => [
                "Savings"
            ],
            "source_of_wealth_other" => "",
            "country_source_of_wealth" => [
                "AFG",
                "ASM"
            ],
            "products" => [
                "SEPA Direct Debit",
                "Credit Card Processing",
                "IBAN4U Payment Account"
            ],
            "section" => "company-sources"
        ];
    }

    private function getIban4uPaymentAccount()
    {
        return [
            "iban4u_payment_account" => [
                "purpose_of_account_opening"=> "purpose_of_account_opening",
                "annual_turnover"=> "333333",
                "deposit" => [
                "trading"=> [
                    "Non-EU"
                ],
                "countries"=> [
                    "ALB"
                ],
                "approximate_per_month"=> "1-5",
                "cumulative_per_month"=> "50000"
                ],
                "withdrawal" => [
                "trading"=> [
                    "EU/EAA"
                ],
                "countries"=> [
                    "BGR"
                ],
                "approximate_per_month"=> "5-10",
                "cumulative_per_month"=> "33333"
                ],
                "activity" => [
                    "incoming_payments"=> [
                        [
                        "type"=> "incoming",
                        "index"=> 1,
                        "name"=> "test",
                        "country"=> "ALB"
                        ],
                        [
                        "name"=> "activity two",
                        "country"=> ""
                        ]
                    ],
                    "outgoing_payments"=> [
                    [
                    "type"=> "outgoing",
                    "index"=> 1,
                    "name"=> "TEST",
                    "country"=> "ALB"
                    ],
                    [
                    "type"=> "outgoing",
                    "index"=> 2,
                    "name"=> "4444",
                    "country"=> "DZA"
                    ]
                ],
                "held_accounts"=> "YES",
                "held_accounts_description"=> "sparkasse",
                "refused_banking_relationship"=> "YES",
                "refused_banking_relationship_description"=> "test 1",
                "terminated_banking_relationship"=> "YES",
                "terminated_banking_relationship_description"=> "eee"
                ]
            ],
            "disabled"=> false,
            "products"=> [
                "SEPA Direct Debit",
                "Credit Card Processing",
                "IBAN4U Payment Account"
            ],
            "section"=> "iban4u-payment-account"
        ];
    }

    private function getAcquiringServices()
    {
        return [
            "credit_card_processing" => [
                "currently_processing_cc_payments" => "NO",
                "offer_recurring_billing" => "YES",
                "frequency_offer_billing" => "Monthly",
                "if_other_offer_billing" => "",
                "offer_refunds" => "YES",
                "frequency_offer_refunds" => "Other",
                "if_other_offer_refunds" => "4444",
                "processing_account_primary_currency" => "EUR",
                "average_ticket_amount" => "22",
                "highest_ticket_amount" => "22",
                "alternative_payment_methods" => "22",
                "payment_method_currently_offered" => "test",
                "current_mcc" => "TEST",
                "current_descriptor" => "TEST",
                "cb_volumes_twelve_months" => "3333",
                "sales_volumes_twelve_months" => "22",
                "refund_twelve_months" => "33333",
                "current_acquire_psp" => "TEST",
                "trading_urls" => [
                "https =>//one.finxp.com/",
                "https =>//two.finxp.com/"
                ],
                "countries" => [
                [
                    "countries_where_product_offered" => "ALB",
                    "distribution_per_country" => "10"
                ]
                ]
            ],
            "products" => [
                "SEPA Direct Debit",
                "Credit Card Processing",
                "IBAN4U Payment Account"
            ],
            "section" => "acquiring-services"
        ];
    }

    private function getDataProtectionMarketing()
    {
        return [
            "products"=> [
                "SEPA Direct Debit",
                "Credit Card Processing",
                "IBAN4U Payment Account"
            ],
            "data_protection_marketing"=> [
                "data_protection_notice"=> 1,
                "receive_messages_from_finxp"=> "YES",
                "receive_market_research_survey"=> "YES"
            ],
            "section"=> "data-protection-and-marketing"
        ];
    }
}
