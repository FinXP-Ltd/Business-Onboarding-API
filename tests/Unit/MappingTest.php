<?php

namespace Tests\Feature;

use App\Services\KYCP\Facades\KYCP;
use Illuminate\Support\Facades\Http;
use App\Models\COT\Field;
use App\Models\Business;
use App\Models\Auth\User;
use App\Models\BusinessComposition;
use App\Models\Person\NaturalPerson;
use App\Enums\BOEntities;
use App\Enums\KYCEntities;
use App\Enums\Status;
use App\Models\LookupType;
use Illuminate\Support\Arr;
use Tests\TestCase;

class MappingTest extends TestCase
{
    const API_ENDPOINT = 'KYCPApi';

    protected $apiUrl;

    public function setUp(): void
    {
        parent::setUp();

        $this->apiUrl = config('kycp.base_url') . '/' . self::API_ENDPOINT;

        $this->mockHttp();
    }

    public function testItShouldMatchKycpAndBpEntities()
    {
        $bo = BOEntities::DIR;
        $kyc = KYCEntities::DIRECTOR_NATURAL_PERSON;

        $this->assertEquals($bo->type('N'), $kyc->value);
        $this->assertEquals($kyc->word(), 'Director Natural Person');
    }

    public function testBetterPaymentEntities()
    {
        $bo = BOEntities::UBO;
        $this->assertEquals($bo->type('P'), 10);

        $bo = BOEntities::SH;
        $this->assertEquals($bo->type('P'), 9);
    }

    public function testKycEntities()
    {
        $kyc = KYCEntities::UBO;
        $this->assertEquals($kyc->value, 10);

        $kyc = KYCEntities::NATURAL_PERSON;
        $this->assertEquals($kyc->value, 1);
        $this->assertEquals($kyc->word(), 'Natural Person');
    }

    public function testStatusEntities()
    {
        $kyc = Status::INPUTTING;
        $this->assertEquals($kyc->value, 1);
        $this->assertEquals($kyc->BPStatus(), 'SUBMITTED');

        $kyc = Status::PREAPPROVAL;
        $this->assertEquals($kyc->value, 4);
        $this->assertEquals($kyc->BPStatus(), 'PENDING');
    }

    public function testEntitiesFormatShouldHave()
    {
        $authUserToken = $this->getTokenPayload('agent', 'client_id.bp');
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

        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->postJson(route('business.create'), $this->getBusinessPayload());

        $content = json_decode($response->getContent(), true);
        $businessId = $content['data']['company_id'];

        $business = Business::find($businessId);
        $bp = KYCP::formatBusinessComposition($business, 11);

        $this->assertArrayHasKey('EntityTypeId', $bp['Entities'][0]);
        $this->assertArrayHasKey('EntityType', $bp['Entities'][0]);
        $this->assertArrayHasKey('Fields', $bp['Entities'][0]);

        $this->assertEquals($bp['Entities'][0]['EntityType'], 'Company');
        $this->assertEquals($bp['Entities'][0]['EntityTypeId'], 2);
    }

    public function testMapTable()
    {
        $business = Business::all()->last();

        $authUserToken = $this->getTokenPayload('agent', 'client_id.bp');
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

        $payload =  $this->getBusinessPayload();
        $response = $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->postJson(route('business.create'), $payload);

        $content = json_decode($response->getContent(), true);
        $businessId = $content['data']['company_id'];

        $business = Business::find($businessId);
        $bp = KYCP::formatBusinessComposition($business, 11);

        $this->assertEquals($bp['Entities'][0]['Fields']->GENname, $payload['name']);
        $this->assertEquals($bp['Entities'][0]['Fields']->GENregnumber, $payload['tax_information']['registration_number']);
        $this->assertEquals($bp['Entities'][0]['Fields']->GENvatnumber, $payload['vat_number']);
    }

    public function testFormatValue()
    {
        $field = [
            'type' => 'Date',
            'key' => 'GENdob',
            'repeater' => 0,
            'lookup_id' => null
        ];

        $bp = KYCP::formatValue('2022-09-30', $field);
        $this->assertEquals($bp, '09/30/2022');
    }

    public function testGetValueLookup()
    {
        $lookup = KYCP::getLookupOptions(14)->json();

        foreach ($lookup["Values"] as $type) {
            LookupType::firstOrCreate([
                'name' => $type['name'],
                'description' => $type['name'],
                'group' => 'GENtitle',
                'type' => 'Lookup',
                'lookup_id' => $type['id'],
            ]);
        }

        $authUserToken = $this->getTokenPayload('agent', 'client_id.bp');
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

        $this->impersonate(credential: $imposter, guard: 'auth0-api');
        $bp = KYCP::getLookupValue('Ms', 'Lookup', 'GENtitle', 0);

        $this->assertEquals($bp, 1355);
    }

    public function testGetLookupId()
    {
        $lookup = KYCP::getLookupOptions(14)->json();

        foreach ($lookup["Values"] as $type) {
            LookupType::firstOrCreate([
                'name' => $type['name'],
                'description' => $type['name'],
                'group' => 'GENtitle',
                'type' => 'Lookup',
                'lookup_id' => $type['id'],
            ]);
        }

        $authUserToken = $this->getTokenPayload('agent', 'client_id.bp');
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

        $this->impersonate(credential: $imposter, guard: 'auth0-api');

        $bp = KYCP::getLookupId('GENtitle', 'Mr');
        $this->assertEquals($bp, 1352);

        $bp = KYCP::getLookupId('GENtitle', 'Other');
        $this->assertEquals($bp, 1356);
    }

    public function testDefaultEntityList()
    {
        $data = $this->getBusinessPayload();
        $bp = KYCP::defaultEntity(2, $data);

        $this->assertArrayHasKey('GENname', $bp);
        $this->assertArrayHasKey('GENregnumber', $bp);
        $this->assertArrayHasKey('GENvatnumber', $bp);

        $this->assertEquals($bp['GENname'], $data['name']);
        $this->assertEquals($bp['GENregnumber'], $data['tax_information']['registration_number']);
        $this->assertEquals($bp['GENvatnumber'], $data['vat_number']);
    }

    public function testCheckIfFieldIsRepeater()
    {
        $products = [
            [
                'value' => "SEPA_DD",
                'label' => "SEPA_DD"
            ],
            [
                'value' => "IBAN4U",
                'label' => "IBAN4U"
            ]
        ];

        $bp = KYCP::checkRepeater($products, 1);
        $this->assertEquals($bp, ['SEPA_DD', 'IBAN4U']);

        $bp = KYCP::checkRepeater('SEPA_DD', 1);
        $this->assertEquals($bp, []);
    }

    protected function mockHttp()
    {
        Http::fake([
            "{$this->apiUrl}/entity/getfields/*" => Http::response([
                "EntityTypeId" => 1,
                "EntityType" => "Natural Person",
                "Fields" => [
                    "GENtitle" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "14"
                    ],
                    "GENname" => [
                        "KycpDataType" => "String",
                        "KycpIdentificative" => "Combined"
                    ],
                    "GENsurname" => [
                        "KycpDataType" => "String",
                        "KycpIdentificative" => "Combined"
                    ],
                    "GENSex" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "34",
                        "KycpRequired" => "true"
                    ],
                    "GENline1" => [
                        "KycpDataType" => "String"
                    ],
                    "GENline2" => [
                        "KycpDataType" => "String"
                    ],
                    "GENlocality" => [
                        "KycpDataType" => "String"
                    ],
                    "GENposcode" => [
                        "KycpDataType" => "String"
                    ],
                    "GENcountry" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "1"
                    ],
                    "GENnationality" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "1"
                    ],
                    "GENdob" => [
                        "KycpDataType" => "DateTime",
                        "KycpIdentificative" => "Combined"
                    ],
                    "GENpob" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "1"
                    ],
                    "GENmaritalstatus" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "15"
                    ],
                    "GENDocComplex" => [
                        [
                            "GENdoctype" => [
                                "KycpDataType" => "Lookup",
                                "KycpLookupTypeId" => "16"
                            ],
                            "GENdocnumber" => [
                                "KycpDataType" => "String"
                            ],
                            "GENdoccountry" => [
                                "KycpDataType" => "Lookup",
                                "KycpLookupTypeId" => "1"
                            ],
                            "GENdocexpiry" => [
                                "KycpDataType" => "DateTime"
                            ]
                        ]
                    ],
                    "GENemail" => [
                        "KycpDataType" => "String",
                        "KycpIdentificative" => "Combined"
                    ],
                    "GENmobile" => [
                        "KycpDataType" => "String",
                        "KycpIdentificative" => "Combined"
                    ],
                    "GENpweproduct" => [
                        [
                            "KycpDataType" => "Lookup",
                            "KycpLookupTypeId" => "3",
                            "KycpRepeater" => "true"
                        ]
                    ],
                    "GENindustry" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "5"
                    ],
                    "GENsow" => [
                        [
                            "KycpDataType" => "Lookup",
                            "KycpLookupTypeId" => "19",
                            "KycpRepeater" => "true"
                        ]
                    ],
                    "GENsowother" => [
                        "KycpDataType" => "FreeText"
                    ],
                    "GENsowverified" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "20"
                    ],
                    "GENestannualincoming" => [
                        "KycpDataType" => "Integer"
                    ],
                    "GENhnwi" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "21"
                    ],
                    "GENiipapplicant" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "22"
                    ],
                    "GENpep" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "23"
                    ],
                    "GENtin" => [
                        "KycpDataType" => "String",
                        "KycpIdentificative" => "Alone"
                    ],
                    "GENcountrytax" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "1"
                    ],
                    "GENuscitizenship" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "24"
                    ],
                    "GENfacetoface" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "25"
                    ],
                    "GENreluctant" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "12"
                    ],
                    "GENsanctioned" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "8"
                    ],
                    "GENadversemedia" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "9"
                    ],
                    "GENpepscreening" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "27"
                    ],
                    "GENotherfindings" => [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "10"
                    ]
                ]
            ]),

            "{$this->apiUrl}/field/get/*" => Http::response([
                "Values" => [
                    [
                        "externalRef" => "1352",
                        "id" => 1352,
                        "name" => "Mr"
                    ],
                    [
                        "externalRef" => "1353",
                        "id" => 1353,
                        "name" => "Mrs"
                    ],
                    [
                        "externalRef" => "1354",
                        "id" => 1354,
                        "name" => "Miss"
                    ],
                    [
                        "externalRef" => "1355",
                        "id" => 1355,
                        "name" => "Ms"
                    ],
                    [
                        "externalRef" => "1356",
                        "id" => 1356,
                        "name" => "Other"
                    ]
                ]
            ]),

            "{$this->apiUrl}/field/get/23" => Http::response([
                "Values" => [
                    [
                        "externalRef" => "35",
                        "id" => 35,
                        "name" => "Yes"
                    ],
                    [
                        "externalRef" => "36",
                        "id" => 36,
                        "name" => "No"
                    ]
                ]
            ])
        ]);
    }

    private function getBusinessPayload()
    {
        return [
            "name" => "FinXp Limited",
            "tax_information" => [
                "tax_country" => "DEU",
                "registration_number" => fake()->swiftBicNumber(),
                "registration_type" => "TRADING",
                "tax_identification_number" => "31659837651"
            ],
            "vat_number" => "VAT25734",
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
                "number_shareholder" => 5,
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

    private function getFieldsPayload()
    {
        return [
            "id" => 1,
            "program_id" => 1,
            "entity_id" => 2,
            "key" => "GenName",
            "type" => "Lookup",
            "bp_table" => 'name',
            "lookup_id" => null,
            "repeater" => 0,
            "required" => 0,
        ];
    }
}
