<?php

namespace Tests\Feature;

use App\Services\KYCP\Facades\KYCP;
use Illuminate\Support\Facades\Http;
use App\Models\COT\Field;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class KYCPTest extends TestCase
{
    const API_ENDPOINT = 'KYCPApi';

    const APP_PAYLOAD = [
        "programId" => 1,
        "entities" => [
            [
                "entityTypeId" => 2,
                "entityType" => "Company",
                "fields" => [
                    "entities" => [
                    ]
                ]
            ]
        ]
    ];

    protected $apiUrl;

    public function setUp(): void
    {
        parent::setUp();

        $this->apiUrl = config('kycp.base_url') . '/' . self::API_ENDPOINT;

        $this->mockHttp();
    }

    public function testShouldSuccessfullyAddAnApplication()
    {
        $response = KYCP::addApplication(self::APP_PAYLOAD)->json();

        $this->assertTrue($response['Success']);
        $this->assertEquals($response['Result'], "Ok");
        $this->assertCount(1, $response['Entities']);
    }

    public function testShouldSuccessfullyAddOrUpdateAnApplication()
    {
        $response = KYCP::addOrUpdateApplication(self::APP_PAYLOAD)->json();

        $this->assertTrue($response['Success']);
        $this->assertEquals($response['Result'], "Ok");
        $this->assertCount(1, $response['Entities']);
    }

    public function testShouldSuccessfullyRetrieveAnApplication()
    {
        $kycpApp = KYCP::addApplication(self::APP_PAYLOAD)->json();

        $response = KYCP::getApplication($kycpApp['Uid'])->json();

        $this->assertIsInt($response['Id']);
        $this->assertCount(1, $response['Entities']);

        $entity = $response['Entities'][0];
        $this->assertIsInt($entity['ApplicationEntityId']);
        $this->assertEquals($entity['EntityType'], self::APP_PAYLOAD['entities'][0]['entityType']);
        $this->assertEquals($entity['EntityTypeId'], self::APP_PAYLOAD['entities'][0]['entityTypeId']);
        $this->assertIsArray($entity['Fields']);
    }

    public function testShouldSuccessfullyUpdateAnApplication()
    {
        $response = KYCP::updateApplication(self::APP_PAYLOAD)->json();

        $this->assertTrue($response['Success']);
        $this->assertEquals($response['Result'], "Ok");
        $this->assertCount(1, $response['Entities']);
    }

    public function testItShouldGetEntityFields()
    {
        $fields = KYCP::getEntityFields(1, 1);
        $fields = $fields->json();
        $fields = $fields['Fields'];

        $this->assertTrue(sizeof($fields) > 20, 'This should be equal to the length of fields');
        $this->assertTrue($fields['GENtitle']['KycpDataType'] === 'Lookup', 'The response should be mapped');
        $this->assertTrue(isset($fields['GENtitle']['KycpLookupTypeId']), 'KycpLookupTypeId should be present');
    }

    public function testItShouldGetLookupOptions()
    {
        $options = KYCP::getLookupOptions(2)->json();

        $this->assertEquals(sizeof($options['Values']), 3);
    }

    public function testShouldUploadEntityDocument()
    {
        $file = UploadedFile::fake()->createWithContent('test.pdf', '');

        $payload = [
            'title' => 'Coloured copy of photo Identity Document',
            'doc_type_id' => 11,
        ];

        $upload = KYCP::uploadEntityDocument($file, 'test.pdf', $payload);
        $this->assertEquals($upload['Result'], 'Ok');
    }

    public function testUpdateStatusWithdrawnToKyc()
    {
        $add = KYCP::addApplication(self::APP_PAYLOAD)->json();
        $status = KYCP::updateStatus($add['Uid'], 14)->json();

        $this->assertEquals($status['Success'], true);
        $this->assertEquals($status['StatusId'], 14);
        $this->assertEquals($status['StatusName'], 'Dormant - Withdrawn');
    }

    protected function mockHttp()
    {
        Http::fake([
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

            "{$this->apiUrl}/application/update" => Http::response([
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

            "{$this->apiUrl}/application/addorupdate" => Http::response([
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
                        "externalRef" => "1",
                        "id" => 1,
                        "name" => "Yes - Relevant to AML"
                    ],
                    [
                        "externalRef" => "2",
                        "id" => 2,
                        "name" => "Yes - Not relevant to AML"
                    ],
                    [
                        "externalRef" => "3",
                        "id" => 3,
                        "name" => "No"
                    ]
                ]
            ]),

            $this->apiUrl . '/document/*' => Http::Response(
                [
                    'EntityTypeId' => 16,
                    'Result' => 'Ok',
                    'Id' => 23895,
                    'ApplicationId' => 3159,
                    'EntityId' => 0,
                    'ApplicationEntityId' => 0,
                    'Title' => 'Coloured copy of photo Identity Document',
                    'Filename' => 'Test.pdf',
                    'PhysicalPath' => '637672953055980861Test.pdf',
                    'ClassTypeId' => 0,
                    'UploadedDate' => '2021-09-15T09:35:05.6000799Z',
                    'ExpiryDate' => '0001-01-01T00:00:00',
                    'Doc_TypeId' => 11,
                    'ModifyedByUserId' => 0,
                    'Deleted' => false,
                    'VerificationStatus' => 0,
                    'PhysicalCopy' => false,
                ],
                200,
                [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ),

            "{$this->apiUrl}/application/setstatus/*" => Http::response([
                "Success" => true,
                "Message" => "",
                "ApplicationId" => 5051,
                "ApplicationUid" => "2MVL8M",
                "StatusId" => 14,
                "StatusName" => "Dormant - Withdrawn"
            ]),
        ]);
    }
}
