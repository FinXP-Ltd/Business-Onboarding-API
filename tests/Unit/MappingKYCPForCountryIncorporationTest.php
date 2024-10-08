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

class MappingKYCPForCountryIncorporationTest extends TestCase
{
    const API_ENDPOINT = 'KYCPApi';

    protected $apiUrl;

    public function setUp(): void
    {
        parent::setUp();

        $this->apiUrl = config('kycp.base_url') . '/' . self::API_ENDPOINT;

        $this->mockHttp();
        Field::query()->forceDelete();
    }

    public function testReturnTheCountryNameAndLookupId()
    {
        $entityId = 2; // Company
        $programId = 11; // Better Payment
        $lookupId = 1; // Countries Lookup ID
        $key = 'GENregcountry';
        $countries = KYCP::getLookupOptions($lookupId)->json();
        $nonPerson = KYCP::getEntityFields($programId, $entityId)->json();

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

        $this->insertLookupTypes($countries, $lookupId, $key);
        $this->insertField($nonPerson, $programId, $key, $entityId);

        $bp = KYCP::formatBusinessComposition($business, $programId);
        $lookup = LookupType::where('group', $key)->where('name', 'Germany')->first()->toArray();

        $this->assertEquals($bp['Entities'][0]['EntityTypeId'], $entityId);
        $this->assertEquals($bp['Entities'][0]['business_id'], $businessId);
        $this->assertEquals($bp['Entities'][0]['model_type'], 'B');

        $this->assertEquals($lookup['name'], 'Germany');
        $this->assertEquals($lookup['lookup_id'], $bp['Entities'][0]['Fields']->$key);
    }

    private function insertLookupTypes($data, $lookupId, $key)
    {
        foreach ($data['Values'] as $type) {
            LookupType::firstOrCreate([
                'name' => $type['name'],
                'description' => $type['name'],
                'group' => $key,
                'type' => 'Lookup',
                'lookup_id' => $type['id'],
                'lookup_type_id' => $lookupId
            ]);
        }
    }

    private function insertField($data, $programId, $key, $entityId)
    {
        $resource = $this->getResources();

        foreach ($data['Fields'] as $key =>  $field) {
            if (! Arr::isAssoc($field)) {
                $field = $field[0];
            }
            Field::firstOrCreate([
                'program_id' => $programId,
                'entity_id' => $entityId,
                'key' => $key,
                'type' => $field['KycpDataType'] ?? null,
                'lookup_id' => $field['KycpLookupTypeId'] ?? null,
                'repeater' => isset($field['KycpRepeater']) ? true : false,
                'required' => isset($field['KycpRequired']) ?? false,
                'mapping_table' => isset($resource[$entityId]['fields'][$key]) ? $resource[$entityId]['fields'][$key] : null,
            ]);
        }
    }

    private function getResources(): array
    {
        return include(resource_path('constants/bp-kycp.php'));
    }

    protected function mockHttp()
    {
        Http::fake([
            "{$this->apiUrl}/entity/getfields/*" => Http::response([
                "EntityTypeId" => 2,
                "EntityType" => "Company",
                "Fields" => [
                    "GENname" => [
                    "KycpDataType" => "String",
                    "KycpRequired" => "true"
                    ],
                    "GENregnumber" => [
                    "KycpDataType" => "String"
                    ],
                    "GENvatnumber" => [
                    "KycpDataType" => "String"
                    ],
                    "GENphonenumber" => [
                    "KycpDataType" => "String"
                    ],
                    "GENemail" => [
                    "KycpDataType" => "String"
                    ],
                    "GENregline1" => [
                    "KycpDataType" => "String"
                    ],
                    "GENregline2" => [
                    "KycpDataType" => "String"
                    ],
                    "GENopline1" => [
                    "KycpDataType" => "String"
                    ],
                    "GENopline2" => [
                    "KycpDataType" => "String"
                    ],
                    "GENoplocality" => [
                    "KycpDataType" => "String"
                    ],
                    "GENopposcode" => [
                    "KycpDataType" => "String"
                    ],
                    "GENOpsNotes" => [
                    [
                        "KycpDataType" => "String",
                        "KycpRepeater" => "true"
                    ]
                    ],
                    "GENlicenserepjuris" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "6",
                    "KycpRequired" => "true"
                    ],
                    "GENMID" => [
                    "KycpDataType" => "String"
                    ],
                    "GENCredID" => [
                    "KycpDataType" => "String"
                    ],
                    "GENdateincorp" => [
                    "KycpDataType" => "DateTime"
                    ],
                    "GENWebsite" => [
                    "KycpDataType" => "FreeText"
                    ],
                    "GENAddWebSites" => [
                    "KycpDataType" => "FreeText"
                    ],
                    "GENregcountry" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "1"
                    ],
                    "GENopcountry" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "1"
                    ],
                    "GENpweproduct" => [
                    [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "3",
                        "KycpRepeater" => "true"
                    ]
                    ],
                    "GENindustry" => [
                    [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "5",
                        "KycpRepeater" => "true"
                    ]
                    ],
                    "GENBusinessActivity" => [
                    "KycpDataType" => "FreeText"
                    ],
                    "GENsharecapital" => [
                    "KycpDataType" => "Integer"
                    ],
                    "GENnoofshrhld" => [
                    "KycpDataType" => "Integer"
                    ],
                    "GENreglocality" => [
                    "KycpDataType" => "String"
                    ],
                    "GENcompanytype" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "4"
                    ],
                    "GENcountryincorp" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "1"
                    ],
                    "GENsameasreg" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "2"
                    ],
                    "GENcountryoflicense" => [
                    [
                        "KycpDataType" => "Lookup",
                        "KycpLookupTypeId" => "1",
                        "KycpRepeater" => "true"
                    ]
                    ],
                    "GENjurisdealing" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "7"
                    ],
                    "GenTradingURLs" => [
                    "KycpDataType" => "String"
                    ],
                    "GenRefunds" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "25"
                    ],
                    "GENcountry" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "1"
                    ],
                    "GENestannualincoming" => [
                    "KycpDataType" => "Integer"
                    ],
                    "GenTickAmou" => [
                    "KycpDataType" => "Integer"
                    ],
                    "GenMCC" => [
                    [
                        "KycpDataType" => "String",
                        "KycpRepeater" => "true"
                    ]
                    ],
                    "GenAgent" => [
                    [
                        "KycpDataType" => "String",
                        "KycpRepeater" => "true"
                    ]
                    ],
                    "GENcomplex" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "11"
                    ],
                    "GENcompany" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "13"
                    ],
                    "GENreluctant" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "12"
                    ],
                    "SPEsof" => [
                    "KycpDataType" => "FreeText"
                    ],
                    "GENsanctioned" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "8"
                    ],
                    "GENadversemedia" => [
                    "KycpDataType" => "Lookup",
                    "KycpLookupTypeId" => "9"
                    ],
                    "GenGoLive" => [
                    "KycpDataType" => "DateTime"
                    ],
                    "GENTermDeclineDate" => [
                    "KycpDataType" => "DateTime"
                    ],
                    "GenSetIBAN" => [
                    "KycpDataType" => "String"
                    ],
                    "GenREFBP" => [
                    "KycpDataType" => "String"
                    ]
                ]
            ]),

           "{$this->apiUrl}/field/get/*" => Http::response([
                "Values" => [
                    [
                        "externalRef" => "1256",
                        "id" => 1256,
                        "name" => "Sw"
                    ],
                    [
                        "externalRef" => "1102",
                        "id" => 1102,
                        "name" => "Germany"
                    ],
                    [
                        "externalRef" => "1105",
                        "id" => 1105,
                        "name" => "Belgium"
                    ]
                ]
            ]),
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
}
