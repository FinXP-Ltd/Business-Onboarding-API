<?php

namespace Tests\Onboarding\Feature\Business;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\Document;
use App\Models\BusinessComposition;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson as NonPerson;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CompositionCreateTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldCreateBusinessComposition()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub
        ]);

        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);
        $business = Business::factory()->create(['user' => $user->id]);

        $businessComposition = [
            "business_id" => $business->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["DIR"],
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->postJson(route('business.createComposition'), $businessComposition)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data' => ['business_composition_id']
            ]);
    }

    public function testShouldDeleteBusinessComposition()
    {
        $naturalPerson = NaturalPerson::factory()->create();
        $b = Business::factory()->create();

        $businessComposition = BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )->create([
            'business_id' => $b->id,
        ]);

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
             ->deleteJson(route('business.deleteComposition', $businessComposition->id))
             ->assertStatus(Response::HTTP_OK)
             ->assertJson([
                'code' => Response::HTTP_OK,
                'status' => 'success',
                'message' => 'Successfully deleted composition!',
            ]);
    }

    public function testShouldCreateBusinessCompositionForTheSameMappingEntityAndPositionWithDifferentBusinessId()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub
        ]);

        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);
        $businessOne = Business::factory()->create(['user' => $user->id]);
        $businessTwo = Business::factory()->create(['user' => $user->id]);

        BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 4])->create([
            'business_id' => $businessOne->id,
        ]);

        $businessComposition = [
            "business_id" => $businessTwo->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["DIR"],
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
             ->postJson(route('business.createComposition'), $businessComposition)
             ->assertStatus(Response::HTTP_CREATED)
             ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data' => ['business_composition_id']
             ]);
    }

    public function testShouldInvalidateBusinessCompositionCreateOnIncorrectPositionValue()
    {
        $naturalPerson = NaturalPerson::factory()->create()->first();

        $b = Business::factory()->create()->first();

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["NOT_REAL"],
            "voting_share" => 20,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

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
        ->postJson(route('business.createComposition', $b->id), $businessComposition)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonStructure([
            'status',
            'message',
            'code',
        ]);
    }

    public function testShouldBeUnableToCreateBusinessCompositionForTheSameMappingEntityAndPosition()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);

        $b = Business::factory()->create(['user' => $user->id]);

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["UBO"],
            "voting_share" => 20,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->postJson(route('business.createComposition'), $businessComposition)
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonStructure([
            'status',
            'message',
            'code',
            'data' => ['business_composition_id']
        ]);

        $businessComposition['position'] = ['UBO', 'DIR'];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->postJson(route('business.createComposition', $b->id), $businessComposition)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonStructure([
            'status',
            'code',
            'message'
        ]);
    }

    public function testShouldInvalidateBusinessCompositionCreateOnPositionThatIsNotArray()
    {
        $naturalPerson = NaturalPerson::factory()->create()->first();

        $b = Business::factory()->create();

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => "UBO",
            "voting_share" => 20,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

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
            ->postJson(route('business.createComposition', $b->id), $businessComposition)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
            'status',
            'message',
            'code',
        ]);
    }

    public function testShouldInvalidateBusinessCompositionCreateOnVotingShareWithNonShareholderPosition()
    {
        $naturalPerson = NaturalPerson::factory()->create()->first();

        $b = Business::latest()->first();

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["DIR"],
            "voting_share" => 100,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $b = Business::latest()->first();

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
        ->postJson(route('business.createComposition', $b->id), $businessComposition)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonStructure([
            'status',
            'message',
            'code',
        ]);
    }

    public function testShouldRequireVotingShareIfBusinessCompositionContainsShareholderPosition()
    {
        $naturalPerson = NaturalPerson::factory()->create()->first();

        $b = Business::factory()->create()->first();

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["SH"],
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $b = Business::latest()->first();

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
        ->postJson(route('business.createComposition', $b->id), $businessComposition)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonStructure([
            'status',
            'message',
            'code',
        ]);
    }

    public function testShouldBeUnableToCreateBusinessCompositionIfVotingShareWillExceed100()
    {
        $naturalPerson = NaturalPerson::factory()->create()->first();
        $b = Business::factory()->create()->first();

        BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
            'business_id' => $b->id,
            'voting_share' => 100,
        ]);

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["SH"],
            "voting_share" => 1,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

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
            ->postJson(route('business.createComposition', $b->id), $businessComposition)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code',
            ]);
    }

    public function testShouldCreateBusinessCompositionForSecondShareholderWithLessThanTwentyFiveShare()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);

        $business = Business::factory()->create(['user' => $user->id]);

        $businessComposition = [
            "business_id" => $business->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["SH"],
            "voting_share" => 2,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->postJson(route('business.createComposition'), $businessComposition)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data' => ['business_composition_id']
            ]);
    }

    public function testShouldNotCreateBusinessCompositionForThirdShareholderWithLessThanTwentyFiveShare()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);
        $naturalPerson = NaturalPerson::factory()->create()->first();

        $business = Business::factory()->create(['user' => $user->id])->first();

        $businessComposition = [
            "business_id" => $business->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["SH"],
            "voting_share" => 1,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->postJson(route('business.createComposition'), $businessComposition)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code',
            ]);
    }

    public function testShouldNotCreateBusinessCompositionIfBusinessIsAlreadySubmitted()
    {

        $b = Business::latest()->first();

        $businessComposition = [
            "business_id" => $b->id,
            "model_type" => 'P',
            "position" => ["SH"],
            "voting_share" => 25,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

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
            ->postJson(route('business.createComposition'), $businessComposition)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testShouldThrowErrorIfTotalShareExceed()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $naturalPerson = NaturalPerson::factory()->create(['user_id' => $user->id]);
        $business = Business::factory()->create(['user' => $user->id])->first();

         BusinessComposition::factory()
        ->hasPerson(
            [
            'business_compositionable_id' => $naturalPerson->id,
            'business_compositionable_type' => NaturalPerson::class,
            ]
        )
        ->hasPosition(['lookup_type_id' => 6])->create([
            'business_id' => $business->id,
            'voting_share' => 100,
        ]);

        $businessComposition = [
            "business_id" => $business->id,
            "model_type" => 'P',
            "mapping_id" => $naturalPerson->id,
            "position" => ["SH"],
            "voting_share" => 25,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposter, guard: 'auth0-api')
            ->postJson(route('business.createComposition'), $businessComposition)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'status',
                'message',
                'code'
            ]);
    }

    private function getBusinessPayload()
    {
        return [
            "name" => fake()->name(),
            "tax_information" => [
                "tax_country" => "DEU",
                "registration_number" => fake()->swiftBicNumber(),
                "registration_type" => "TRADING",
                "tax_identification_number" => "31659837651"
            ],
            "vat_number" => "updated_vat",
            "foundation_date" => "2022-09-30",
            "telephone" => "11223366699",
            "email" => "info@finxp.com",
            "website" => "https://www.finxp.com",
            "additional_website" => "https://my.finxp.com",
            "registered_address" => [
                "line_1" => "Line 1",
                "line_2" => "Line 2",
                "city" => "Amsterdam",
                "postal_code" => "Amsterdam",
                "country" => "DEU"
            ],
            "operational_address" => [
                "line_1" => "Line 1",
                "line_2" => "Line 2",
                "city" => "Amsterdam",
                "postal_code" => "Amsterdam",
                "country" => "MLT"
            ],
            "contact_details" => [
                "first_name" => "Jordan",
                "last_name" => "Ingles",
                "position_held" => "Company Executive Officer",
                "country_code" => "+49",
                "mobile_no" => "11223366699",
                "email" => "jordan.ingles@finxp.com"
            ],
            "business_details" => [
                "finxp_products" => [
                    "IBAN4U Payment Account",
                    "SEPA Direct Debit",
                    "Credit Card Processing"
                ],
                "industry_key" => [
                    "Plastering Contractors",
                    "Electrical Contractors"
                ],
                "business_purpose" => "Change the world",
                "number_employees" => 100,
                "share_capital" => 10150000.5,
                "number_shareholder" => 5,
                "number_directors" => 1,
                "previous_year_turnover" => "2021",
                "license_rep_juris" => "YES",
                "country_of_license" => "DEU",
                "country_juris_dealings" => ['MLT', "DEU"],
                "business_year_count" => 2,
                "source_of_funds" => [
                    "TREASURY",
                    "DONATIONS"
                ],
                "terms_and_conditions" => true,
                "privacy_accepted" => true
            ],
            "iban4u_payment_account" => [
                "purpose_of_account_opening" => "reason goes here",
                "partners_incoming_transactions" => "foo bar baz",
                "country_origin" =>  ['MLT', "DEU"],
                "country_remittance" => ['MLT', "DEU"],
                "estimated_monthly_transactions" => 666,
                "average_amount_transaction_euro" => 1337,
                "accepting_third_party_funds" => "YES"
            ],
            "sepa_dd_direct_debit" => [
                "currently_processing_sepa_dd" => "YES",
                "sepa_dds" => [],
                "sepa_dd_volume_per_month" => 200
            ],
            "credit_card_processing" => [
                "currently_processing_cc_payments" => "YES",
                "trading_urls" => "url1,url2,url3",
                "offer_recurring_billing" => "YES",
                "recurring_details" => "foo bar baz",
                "offer_refunds" => "YES",
                "refund_details" => "foo bar baz",
                "country" => "PHL",
                "distribution_sale_volume" => 99.99,
                "processing_account_primary_currency" => "EUR",
                "average_ticket_amount" => 66,
                "highest_ticket_amount" => 99,
                "alternative_payment_methods" => [
                    "GIRO",
                    "SOFORT"
                ],
                "other_alternative_payment_methods" => "Other Payment Method",
                "method_currently_offered" => [
                    "GIRO",
                    "SOFORT"
                ],
                "other_alternative_payment_method_used" => "Other Payment Method",
                "current_mcc" => "Current MCC",
                "current_descriptor" => "foo bar baz",
                "cb_volumes_twelve_months" => 1234.56,
                "cc_volumes_twelve_months" => 1234.56,
                "refund_volumes_twelve_months" => 6699,
                "current_acquire_psp" => "foo bar baz",
                "iban4u_processing" => "YES",
            ]
        ];
    }
}
