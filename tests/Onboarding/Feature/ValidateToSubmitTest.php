<?php

namespace Tests\Onboarding\Feature;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\Document;
use App\Models\BusinessComposition;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\Person\NaturalPerson;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;


class ValidateToSubmitTest extends TestCase
{
    public $imposter;
    public $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();

        $authUserToken =  $this->getTokenPayload('agent');
        $sub = $authUserToken['sub'];
        $this->user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub,
        ]);

        $this->imposter = $this->createImposterUser(
            id:  $this->user->auth0,
            email:  $this->user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->createBusinessApp();
    }

    public function testShouldNotSubmitIfItIsNotComplete()
    {
        $b = Business::all()->last();

        $this->impersonate(credential: $this->imposter, guard: 'auth0-api')
        ->putJson(route('business.submit', $b->id))
        ->assertStatus(Response::HTTP_CONFLICT)
        ->assertJson([
            'code' => Response::HTTP_CONFLICT,
            'status' => 'failed',
            'message' => 'Unable to submit business. Please complete the shareholding composition first.'
        ]);
    }

    public function testShouldNotSubmitBusinessIfTheCompositionDirectorsAndShareholdersAreNotComplete()
    {
        $b = Business::all()->last();

        $naturalPerson = NaturalPerson::factory()->create(['user_id' =>  $this->user->id]);
        $this->addRoles($b->id, $naturalPerson, 'P', 'SH', $this->imposter);

        $this->impersonate(credential: $this->imposter, guard: 'auth0-api')
        ->putJson(route('business.submit', $b->id))
        ->assertStatus(Response::HTTP_CONFLICT)
        ->assertJson([
            'code' => Response::HTTP_CONFLICT,
            'status' => 'failed',
            'message' => 'Unable to submit business. Please complete the list of compositions first. Add 1 Director/s and 0 Shareholder/s'
        ]);
    }

    public function testShouldNotSubmitBusinessIfDocumentsAreNotComplete()
    {
        $b = Business::all()->last();

        $naturalPerson = NaturalPerson::factory()->create(['user_id' =>  $this->user->id]);
        $this->addRoles($b->id, $naturalPerson, 'P', 'SH', $this->imposter);

        $nonPerson = NonNaturalPerson::factory()->create(['user_id' =>  $this->user->id]);
        $this->addRolesNonPerson($b->id, $nonPerson, 'N', 'DIR', $this->imposter);

        $this->impersonate(credential: $this->imposter, guard: 'auth0-api')
        ->putJson(route('business.submit', $b->id))
        ->assertStatus(Response::HTTP_CONFLICT)
        ->assertJson([
            'code' => Response::HTTP_CONFLICT,
            'status' => 'failed',
            'message' => 'Unable to submit business. There are compositions that still need required documents.'
        ]);
    }

    public function testShouldNotSubmitIfDocumentsAreNotComplete()
    {
        $b = Business::all()->last();

        $naturalPerson = NaturalPerson::factory()->create(['user_id' =>  $this->user->id]);
        $this->addRoles($b->id, $naturalPerson, 'P', 'SH', $this->imposter);

        $nonPerson = NonNaturalPerson::factory()->create(['user_id' =>  $this->user->id]);
        $this->addRolesNonPerson($b->id, $nonPerson, 'N', 'DIR', $this->imposter);


        $this->uploadDocument('B', $b->id, 'certificate_of_incorporation', $this->imposter);

        $this->impersonate(credential: $this->imposter, guard: 'auth0-api')
        ->putJson(route('business.submit', $b->id))
        ->assertStatus(Response::HTTP_CONFLICT)
        ->assertJson([
            'code' => Response::HTTP_CONFLICT,
            'status' => 'failed',
            'message' => 'Unable to submit business. There are compositions that still need required documents.',
            'missing documents' => [
                $nonPerson->id => [
                    'DIR_CORPORATE' =>  ["utility_bill_or_proof_of_address"]
                ]
            ]
        ]);
    }

    private function createBusinessApp()
    {
        $this->impersonate(credential: $this->imposter, guard: 'auth0-api')
        ->postJson(route('business.create'), $this->getBusinessPayload())
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonStructure([
            'status',
            'message',
            'data' => ['company_id'],
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
                "number_employees" => 100,
                "share_capital" => 10150000.5,
                "number_shareholder" => 1,
                "number_directors" => 1,
                "previous_year_turnover" => "2021",
                "license_rep_juris" => "YES",
                "country_of_license" => "DEU",
                "country_juris_dealings" => ['MLT', 'DEU'],
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
                "country_origin" => ['FIN', 'AUS', 'DEU'],
                "country_remittance" => ['MLT', 'PHL'],
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
                "offer_recurring_billing" => "YES",
                "offer_refunds" => "YES",
                "country" => "PHL",
                "distribution_sale_volume" => 99.99,
                "average_ticket_amount" => 66,
                "highest_ticket_amount" => 99,
                "alternative_payment_methods" => [
                    "GIRO",
                    "SOFORT"
                ],
                "method_currently_offered" => [
                    "GIRO",
                    "SOFORT"
                ],
                "cb_volumes_twelve_months" => 1234.56,
                "cc_volumes_twelve_months" => 1234.56,
                "refund_volumes_twelve_months" => 6699,
                "iban4u_processing" => "YES",
            ]
        ];
    }

    private function hasShareholderAndDirectors()
    {
        return [
            "business_details" => [
                "finxp_products" => [
                    "IBAN4U Payment Account",
                    "SEPA Direct Debi",
                    "Credit Card Processing"
                ],
                "industry_key" => [
                    "Electrical Contractors",
                    "Siding - Contractors"
                ],
                "number_employees" => 100,
                "share_capital" => 10150000.5,
                "number_shareholder" => 1,
                "number_directors" => 1,
                "previous_year_turnover" => "2021",
                "license_rep_juris" => "YES",
                "country_of_license" => "DEU",
                "country_juris_dealings" => ['MLT', 'DEU'],
                "business_year_count" => 2,
                "source_of_funds" => [
                    "TREASURY",
                    "DONATIONS"
                ],
                "terms_and_conditions" => true,
                "privacy_accepted" => true
            ],
        ];
    }

    private function addRoles($businessId, $person, $modelType, $role, $user)
    {
        $businessComposition = [
            "business_id" => $businessId,
            "model_type" => $modelType,
            "mapping_id" => $person->id,
            "position" => [$role],
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01",
            "voting_share" => 25
        ];

        $this->impersonate(credential: $user, guard: 'auth0-api')
            ->postJson(route('business.createComposition'), $businessComposition)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data' => ['business_composition_id']
            ]);
    }

    private function addRolesNonPerson($businessId, $person, $modelType, $role, $user)
    {
        $businessComposition = [
            "business_id" => $businessId,
            "model_type" => $modelType,
            "mapping_id" => $person->id,
            "position" => [$role],
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01",
        ];

        $this->impersonate(credential: $user, guard: 'auth0-api')
            ->postJson(route('business.createComposition'), $businessComposition)
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure([
                'status',
                'message',
                'code',
                'data' => ['business_composition_id']
            ]);
    }

    private function uploadDocument($modelType, $mappingId, $docType, $user)
    {
        $documentFile = UploadedFile::fake()->image('sample.jpg');

        $newDocument = [
            'data' => [
                'document_type' => $docType,
                'owner_type' => $modelType,
                'mapping_id' => $mappingId,
            ],
            'file' => $documentFile,
        ];

        $this->impersonate(credential: $user, guard: 'auth0-api')
        ->post(route('document.upload'), $newDocument)
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonStructure([
            'status',
            'code',
            'message',
            'data' => ['document_id'],
        ]);
    }
}
