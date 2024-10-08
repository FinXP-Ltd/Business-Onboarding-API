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

class MappingKYCPForPepTest extends TestCase
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

    public function testPepShouldBeStringWithLookupId()
    {
        $entityId = 1; // Natural Person
        $programId = 11; // Better Payment
        $lookupId = 23; // PEP Lookup ID
        $key = 'GENpep';
        $pep = KYCP::getLookupOptions($lookupId)->json();
        $naturalPerson = KYCP::getEntityFields($programId, $entityId)->json();

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

        $this->insertLookupTypes($pep, $lookupId, $key);
        $this->insertField($naturalPerson, $programId, $key, $entityId);

        $person = $this->getNaturalPersonPayload();
        $lookup = LookupType::where('name', $person['person']['additional_info']['pep'])->where('group', $key)->first()->toArray();

        $this->assertEquals($lookup['lookup_id'], 35);
        $this->assertEquals($person['person']['additional_info']['pep'], $lookup['name']);
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

    private function getNaturalPersonPayload()
    {
        return [
            'person' => [
                'title' => 'Mrs',
                'name' => 'Vivian',
                'surname' => 'Stroman',
                'sex' => 'female',
                'date_of_birth' => '1988-12-11',
                'place_of_birth' => 'Belarus',
                'country_code' => 'DEU',
                'email_address' => 'alba21@example.com',
                'mobile' => '090481250816931',
                'address' => [
                'line_1' => 'egXlI',
                'line_2' => 'eXO6e',
                'locality' => 'DGxQj',
                'postal_code' => 'ELojr',
                'country' => 'DEU',
                'nationality' => 'DEU'
                ],
                'identification_document' => [
                'document_type' => 't0Q',
                'document_number' => 'pAKFSNHfItKTf',
                'document_country_of_issue' => 'PHL',
                'document_expiry_date' => '2010-03-15'
                ],
                'additional_info' => [
                'occupation' => '03PX5',
                'employment' => '7xLSG',
                'position' => 'DtJS8',
                'source_of_income' => '58U9R',
                'source_of_wealth' => 'MmM',
                'source_of_wealth_details' => 'beY',
                'other_source_of_wealth_details' => 'yNT',
                'us_citizenship' => true,
                'pep' => 'Yes',
                'tin' => 'kZA0kqkixyyqN',
                'country_tax' => 'DEU'
                ]
            ]
        ];
    }
}
