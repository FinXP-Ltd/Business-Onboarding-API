<?php

namespace Tests\Feature\Unit;

use App\Jobs\KYCPJob;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class KYCPJobTest extends TestCase
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

    public function testShouldQueueKycpServices()
    {
        Queue::fake();
        Http::fake();

        KYCPJob::dispatch('addApplication', []);

        Queue::assertPushed(KYCPJob::class, 1);

        KYCPJob::dispatch('updateApplication', []);
        Queue::assertPushed(KYCPJob::class, 2);

        KYCPJob::dispatch('getApplication', 'foobarbaz');
        Queue::assertPushed(KYCPJob::class, 3);
    }

    public function testJobShouldRunKycpServicesInHandleMethod()
    {
        Queue::fake();
        Http::fake();

        $job = new KYCPJob('addApplication', []);
        $job->handle();

        Http::assertSentCount(1);
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
                            "GenDDProd" => "",
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
            ])
        ]);
    }
}
