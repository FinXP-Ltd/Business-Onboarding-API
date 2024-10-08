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
use Illuminate\Support\Facades\Http;
use App\Services\KYCP\Facades\KYCP;


class SubmitToKycpTest extends TestCase
{
    protected $apiUrl;
    protected $model;

    const API_ENDPOINT = 'KYCPApi';

    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
        $this->apiUrl = config('kycp.base_url') . '/' . self::API_ENDPOINT;

        $this->mockHttp();

    }

    public function testShouldSubmitSuccessfully()
    {
        $authUserToken =  $this->getTokenPayload('operation');
        $sub = $authUserToken['sub'];
        $user = User::factory()->create([
            'email' => 'marjorie.asensi+1@finxp.com',
            'auth0' => $sub,
        ]);

        $operation = $this->createImposterUser(
            id:  $user->auth0,
            email:  $user->email,
            accessTokenPayload: $authUserToken,
        );

        $newBusiness = array_merge($this->getBusinessPayload(), $this->hasShareholderAndDirectors());

        $response =  $this->impersonate(credential: $operation, guard: 'auth0-api')
        ->postJson(route('business.create'), $newBusiness);

        $content = json_decode($response->getContent(), true);
        $businessId = $content['data']['company_id'];

        $business = Business::all()->last();
        $this->assertEquals($business->id, $businessId);

        $format =  KYCP::formatBusinessComposition($business, 11);
        $entity = $format['Entities'][0]['Fields'];
        $this->assertEquals($newBusiness['name'], $entity->GENname);
        $this->assertEquals($newBusiness['tax_information']['registration_number'], $entity->GENregnumber);
        $this->assertEquals($newBusiness['vat_number'], $entity->GENvatnumber);

        $kycp =  KYCP::addApplication($format)->json();
        $this->assertEquals($kycp['Success'], true);
        $this->assertEquals($kycp['Entities'][0]['EntityType'], 'Company');
    }

    public function getResources(): array
    {
        return include(resource_path('constants/bp-kycp.php'));
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
            "vat_number" => "VAT2564",
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

    protected function mockHttp()
    {
        Http::fake([
            "{$this->apiUrl}/entity/getentitytypes/*" => Http::response([
                "Values" => [
                    [
                        "id" => 1,
                        "name" => "Natural Person"
                    ],
                    [
                        "id" => 2,
                        "name" => "Company"
                    ],
                    [
                        "id" => 8,
                        "name" => "Shareholder Corporate"
                    ],
                    [
                        "id" => 9,
                        "name" => "Shareholder Natural Person"
                    ],
                    [
                        "id" => 10,
                        "name" => "UBO"
                    ],
                    [
                        "id" => 11,
                        "name" => "Director Corporate"
                    ],
                    [
                        "id" => 12,
                        "name" => "Director Natural Person"
                    ],
                    [
                        "id" => 13,
                        "name" => "Company Secretary "
                    ],
                    [
                        "id" => 14,
                        "name" => "Administrator/Authorised Signatory"
                    ],
                    [
                        "id" => 17,
                        "name" => "Minority Shareholder"
                    ]
                ]
            ]),

            "{$this->apiUrl}/application/add" => Http::response([
                'Success' => true,
                'Result' => 'Ok',
                'Uid' => '2MVL8M',
                'Entities' => [
                    [
                        "Id" => 22460,
                        "EntityTypeId" => 2,
                        "EntityType" => "Company",
                        "Fields" => [
                            "entities" => [],
                        ],
                        "Entities" => [],
                        "Result" => "Ok",
                    ]
                ]
            ]),

            "{$this->apiUrl}/application/getstructure/*" => Http::response([
                "Id" => 5051,
                "Uid" => "2MVL8M",
                "ProgramId" => 1,
                "Entities" => [
                    [
                        "ApplicationEntityId" => 23916,
                        "Id" => 22460,
                        "EntityTypeId" => 2,
                        "EntityType" => "Company",
                        "Fields" => [
                            "GENname" => "",
                            "GENregnumber" => "",
                            "GENdateincorp" => "",
                            "GENcountryincorp" => "",
                            "GENcompanytype" => "",
                            "GENvatnumber" => "",
                            "GENtin" => "",
                            "GENphonenumber" => "",
                            "GENemail" => "",
                            "GENWebsite" => "",
                            "GENAddWebSites" => "",
                            "GENregline1" => "",
                            "GENregline2" => "",
                            "GENreglocality" => "",
                            "GENregposcode" => "",
                            "GENregcountry" => "",
                            "GENsameasreg" => "",
                            "GENopline1" => "",
                            "GENopline2" => "",
                            "GENoplocality" => "",
                            "GENopposcode" => "",
                            "GENopcountry" => "",
                            "GENsurname" => "",
                            "email" => "",
                            "GENmobile" => [
                                "",
                            ],
                            "GENposition" => "",
                            "GENpweproduct" => [
                                "",
                            ],
                            "GENindustry" => [
                                "",
                            ],
                            "GENBusinessActivity" => "",
                            "GENNoEmp" => "",
                            "GENsharecapital" => "",
                            "GENnoofshrhld" => "",
                            "GENNoDirectors" => "",
                            "GENPreYTO" => "",
                            "GENlicenserepjuris" => "",
                            "GENcountryoflicense" => [
                                "",
                            ],
                            "GENjurisdealing" => [
                                "",
                            ],
                            "GENNoYearsBusiness" => "",
                            "GenIBANYN" => "",
                            "GENPurposeOfAcc" => "",
                            "GENInPart" => [
                                "",
                            ],
                            "SPEcountryorigin" => [
                                "",
                            ],
                            "GENCounInPay" => [
                                "",
                            ],
                            "GenOutPart" => [
                                "",
                            ],
                            "SPEcountryremittance" => [
                                "",
                            ],
                            "GenCounOutPay" => [
                                "",
                            ],
                            "GENestannualincoming" => "",
                            "GENEstimNoMonthTrns" => "",
                            "GENEstAveofTrans" => "",
                            "GENThirdParty" => "",
                            "GenCCYN" => "",
                            "GenCCCurPross" => "",
                            "GenTradingURLs" => "",
                            "GenRecurBill" => "",
                            "GenRefunds" => "",
                            "GENcountry" => "",
                            "GenSaleVol" => [
                                "",
                            ],
                            "GenCurrecy" => [
                                "",
                            ],
                            "GenTickAmou" => "",
                            "GenHighTicAmount" => "",
                            "GenAPM" => [
                                "",
                            ],
                            "GenAPMUsed" => [
                                "",
                            ],
                            "GenMCC" => [
                                "",
                            ],
                            "GenCurrDescp" => [
                                "",
                            ],
                            "GenCCCBVol12M" => [
                                "",
                            ],
                            "GenCCVol12M" => [
                                "",
                            ],
                            "GenCCRefVol12" => [
                                "",
                            ],
                            "GenCurrProv" => [
                                "",
                            ],
                            "SEPADD_YN" => "",
                            "GenDDCurProcs" => "",
                            "GenDDPricing" => "",
                            "GenDDVol" => "",
                            "GenAgent" => "",
                            "GENstartup" => "",
                            "GENcomplex" => "",
                            "GENcompany" => "",
                            "GENreluctant" => "",
                            "SPEsof" => "",
                            "GENwebshield" => "",
                            "GENsanctioned" => "",
                            "GENadversemedia" => "",
                            "GENOpsNotes" => [
                                "",
                            ],
                            "GenGoLive" => "",
                            "GENIbanNo" => "",
                            "GENTermDeclineDate" => "",
                            "GENUBOFullName1" => "",
                            "GENUBODateAcc1" => "",
                            "GENUBOPerHeld1" => "",
                            "GENPostionHeld1" => [
                                "",
                            ],
                            "GENUBOAddNotes1" => "",
                            "GENUBOFullName2" => "",
                            "GENUBOPerHeld2" => "",
                            "GENUBODateAcc2" => "",
                            "GENPostionHeld2" => [
                                "",
                            ],
                            "GENUBONotes2" => "",
                            "GENUBOFullName3" => "",
                            "GENUBOPerHeld3" => "",
                            "GENUBODateAcc3" => "",
                            "GENPostionHeld3" => [
                                "",
                            ],
                            "GENUBONotes3" => "",
                            "GENUBOFullName4" => "",
                            "GENUBOPerHeld4" => "",
                            "GENUBODateAcc4" => "",
                            "GENPostionHeld4" => [
                                "",
                            ],
                            "GENUBONotes4" => "",
                            "GENPrivacy" => "",
                            "GENTerms" => "",
                        ],
                    ],
                ],
            ]),

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
            ])
        ]);
    }
}
