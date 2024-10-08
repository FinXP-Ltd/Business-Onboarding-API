<?php

namespace Tests\Unit;

use App\Models\Auth\User;
use App\Models\CreditCardProcessing;
use Illuminate\Http\Response;
use App\Models\Business;
use App\Services\Business\Client\Factory;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class CreditCardProcessingTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldRequireFieldsOnAutoSaveIfEmpty()
    {
        $b = Business::factory()->create();

        $payload= [
            "credit_card_processing" => [
                "currently_processing_cc_payments" => "",
                "trading_urls" =>[""],
                "offer_recurring_billing" => "",
                "frequency_offer_billing" => "",
                "if_other_offer_billing" => "",
                "offer_refunds" => "",
                "frequency_offer_refunds" => "",
                "if_other_offer_refunds" => "",
                "countries" => [
                    [
                        "order" => null,
                        "countries_where_product_offered" => "",
                        "distribution_per_country" => ""
                    ]
                ],
                "processing_account_primary_currency" => "",
                "ac_average_ticket_amount" => "",
                "ac_highest_ticket_amount" => "",
                "ac_alternative_payment_methods" => "",
                "ac_method_currently_offered" => "",
                "ac_current_mcc" => "",
                "ac_current_descriptor" => "",
                "ac_cb_volumes_twelve_months" => null,
                "ac_cc_volumes_twelve_months" => null,
                "ac_refund_volumes_twelve_months" => null,
                "ac_current_acquire_psp" => ""
            ]
        ];

        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $this->auth0Mock
            ->shouldReceive('management->users->get')
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
            ->put(route('business.save', $b->id), $payload)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'code',
                'status',
                'message'
            ]);
    }

    public function testShouldProcessCountriesData()
    {
        $b = Business::factory()->create(['status' => 'OPENED']);
        $creditCardProcessing = CreditCardProcessing::factory()->hasCountries(3)->create();
        $service = new Factory();

        $payload = [
            "countries" => [
                [
                    "countries_where_product_offered" => "BRZ",
                    "distribution_per_country" => 30
                ],
                [
                    "countries_where_product_offered" => "CHL",
                    "distribution_per_country" => 60
                ],
                [
                    "countries_where_product_offered" => "PHL",
                    "distribution_per_country" => 90
                ]
            ],
            "section" => "acquiring-services",
            "corporate_saving" => "true"
        ];

        $service->processCountriesData($creditCardProcessing, $payload["countries"]);
        $expectedOrder = range(1, count($payload['countries']));
        $actualOrder = $creditCardProcessing->fresh()->countries->pluck('order')->toArray();
        $this->assertCount(count($payload['countries']), $creditCardProcessing->fresh()->countries);
        $this->assertSame($expectedOrder, $actualOrder);

        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $this->auth0Mock
            ->shouldReceive('management->users->get')
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
            ->put(route('business.save', $b->id), $payload, $this->getHeadersPayload($payload))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message'
            ]);
    }

    public function testAllFieldsSaveOnAutoSave()
    {
        $b = Business::factory()->create(['status' => 'OPENED']);

        $payload= [
            "credit_card_processing" => [
                "currently_processing_cc_payments" => "YES",
                "trading_urls" =>[
                    "https://portaladmins.com",
                    "https://www.ionspecinternational.com",
                    "https://www.tesdaman.com",
                    "https://www.tesdamanV2.com"
                ],
                "offer_recurring_billing" => "YES",
                "frequency_offer_billing" => "QUARTERLY",
                "offer_refunds" => "NO",
                "frequency_offer_refunds" => "OTHER",
                "if_other_offer_refunds" => "Sometimes",
                "countries" => [
                    [
                        "order" => 1,
                        "countries_where_product_offered" => "PHL",
                        "distribution_per_country" => 100
                    ]
                ],
                "processing_account_primary_currency" => "EUR",
                "ac_average_ticket_amount" => "700",
                "ac_highest_ticket_amount" => "1000",
                "ac_alternative_payment_methods" => "Gcash",
                "ac_method_currently_offered" => "Wise",
                "ac_current_mcc" => "current MCC",
                "ac_current_descriptor" => "descriptor",
                "ac_cb_volumes_twelve_months" => 1000,
                "ac_cc_volumes_twelve_months" => 1300,
                "ac_refund_volumes_twelve_months" => 500,
                "ac_current_acquire_psp" => "PSP"
            ],
            "section" => "acquiring-services",
            "corporate_saving" => "true"
        ];

        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $this->auth0Mock
            ->shouldReceive('management->users->get')
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
            ->put(route('business.save', $b->id), $payload, $this->getHeadersPayload($payload))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message'
            ]);
    }

    public function testShouldUpdateCreditCardPrcessingWithCountriesAndTradingUrls()
    {
        $b = Business::factory(["status" => 'OPENED'])->create();
        $creditCardProcessing = CreditCardProcessing::factory()
        ->hasTradingUrl()
        ->hasCountries()
        ->create();

        $creditCardProcessingData = $creditCardProcessing->toArray();

        $payload = [
            "credit_card_processing" => [
                $creditCardProcessingData
            ],
            "section" => "acquiring-services",
            "corporate_saving" => "true"
        ];

        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $this->auth0Mock
            ->shouldReceive('management->users->get')
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
            ->put(route('business.save', $b->id), $payload, $this->getHeadersPayload($payload))
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'status',
                'message'
            ]);
    }

    public function testShouldGetCreditCardProcessing()
    {
        $b = Business::factory()->hasCreditCardProcessing()->create();

        $authUserToken = $this->getTokenPayload('agent');

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
            ->getJson(route('business.show', $b->id))
            ->assertStatus(Response::HTTP_OK)
                ->assertJsonStructure([
                'name',
                "credit_card_processing" => [
                    "currently_processing_cc_payments",
                    "trading_urls",
                    "offer_recurring_billing",
                    "frequency_offer_billing",
                    "offer_refunds",
                    "frequency_offer_refunds" ,
                    "if_other_offer_refunds",
                    "countries",
                    "processing_account_primary_currency",
                    "ac_average_ticket_amount",
                    "ac_highest_ticket_amount",
                    "ac_alternative_payment_methods",
                    "ac_method_currently_offered",
                    "ac_current_mcc",
                    "ac_current_descriptor",
                    "ac_cb_volumes_twelve_months",
                    "ac_cc_volumes_twelve_months",
                    "ac_refund_volumes_twelve_months",
                    "ac_current_acquire_psp"
                ],
            ]);
    }

    private function getHeadersPayload($new_business)
    {
        $ts = time();
        $guid = Str::uuid()->toString();

        $signature = $ts . $guid . json_encode($new_business, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
        $digest = hash_hmac('sha256', $signature, config('app.saving_secret'));

        $headers = [
            'x-app-ts' => $ts,
            'x-app-digest' => $digest,
            'x-app-guid' => $guid
        ];

        return $headers;
    }
}
