<?php

namespace Tests\ApplyCorporate\Feature\Business;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\CompanyInformation;
use App\Models\CompanyIban4UAccount;
use App\Models\CompanyIban4UAccountActivity;
use App\Models\CompanyIban4UAccountCountry;
use App\Models\BusinessDetail;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessValidateBySectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldValidateSectionCompanyDetails()
    {
        $b = Business::factory()->create(['status' => 'OPENED']);
        $compInfo =  CompanyInformation::create(['business_id' => $b->id]);

        $iban4u = CompanyIban4UAccount::create([
            'business_id' => $b->id,
            'company_information_id' => $compInfo->id
        ]);

        CompanyIban4UAccountActivity::create(['company_iban4u_account_id' => $iban4u->id]);
        CompanyIban4UAccountCountry::create(['company_iban4u_account_id' => $iban4u->id]);

        $payload = [
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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    public function testShouldValidateSectionCompanyAddress()
    {
        $b = Business::all()->last();

        $payload = [
            'registered_address' => [
                'registered_street_number' => 'Manila',
                'registered_postal_code' => '23421',
                'registered_city' => 'mandaluyong',
                'registered_street_name' => 'Welfare Ville',
                'registered_country' => 'BFA'
            ],
            'operational_address' => [
                'operational_street_number' => 'Manila',
                'operational_street_name' => 'Welfare Ville 12333',
                'operational_postal_code' => '23421',
                'operational_city' => 'mandaluyong',
                'operational_country' => 'CPV'
            ],
            'is_same_address' => true,
            'section' => 'company-address',
            'corporate_saving' => true
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');

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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    public function testShouldValidateSectionCompanySources()
    {
        $b = Business::all()->last();

        $payload = [
            'source_of_funds' => 'asdasd',
            'country_source_of_funds' => [
                'AND',
                'RUS'
            ],
            'source_of_wealth' => [
                'Investments',
                'Donations',
                'Other'
            ],
            'source_of_wealth_other' => 'test',
            'country_source_of_wealth' => [
                'ALB',
                'BVT'
            ],
            'section' => 'company-sources',
            'corporate_saving' => true
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');

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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    public function testShouldValidateSectionCompanySepadd()
    {
        $b = Business::all()->last();

        $payload = [
            'processing_sepa_dd' => 'NO',
	        'expected_global_mon_vol' => '234',
		    'sepa_dd_products' => [],
			'section' => 'company-sepa-dd',
			'corporate_saving' => true
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');

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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    public function testShouldValidateSectionIban4u()
    {
        $b = Business::all()->last();

        $payload = [
            'iban4u_payment_account' => [
                'purpose_of_account_opening' => 'Purpose of opening account in',
                'annual_turnover' => '2345',
                'deposit' => [
                    'trading' => [
                        'Non-EU'
                    ],
                    'countries' => [
                        'ASM',
                        'BWA'
                    ],
                    'approximate_per_month' => '16-20',
                    'cumulative_per_month' => '12'
                ],
                'withdrawal' => [
                    'trading' => [
                        'EU/EAA'
                    ],
                    'countries' => [
                        'SVK',
                        'SWE'
                    ],
                    'approximate_per_month' => '0',
                    'cumulative_per_month' => '45'
                ],
                'activity' => [
                    'incoming_payments' => [
                        [
                            'name' => 'activity one',
                            'country' => 'DZA'
                        ],
                        [
                            'name' => 'activity two',
                            'country' => 'DZA'
                        ]
                    ],
                    'outgoing_payments' => [
                        [
                            'name' => 'organization one',
                            'country' => 'DZA'
                        ]
                    ],
                    'held_accounts' => 'YES',
                    'held_accounts_description' => 'held accounts',
                    'refused_banking_relationship' => 'NO',
                    'refused_banking_relationship_description' => '',
                    'terminated_banking_relationship' => 'NO',
                    'terminated_banking_relationship_description' => ''
                ]
            ],
            'disabled' => false,
            'products' => [
                'SEPA Direct Debit',
                'Credit Card Processing',
                'IBAN4U Payment Account'
            ],
            'section' => 'iban4u-payment-account'
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');
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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    public function testShouldValidateSectionCreditCardProcessing()
    {
        $b = Business::all()->last();

        $payload = [
            'credit_card_processing' => [
                'currently_processing_cc_payments' => 'NO',
                'offer_recurring_billing' => 'YES',
                'frequency_offer_billing' => 'Monthly',
                'if_other_offer_billing' => '',
                'offer_refunds' => 'YES',
                'frequency_offer_refunds' => 'Quarterly',
                'if_other_offer_refunds' => '',
                'processing_account_primary_currency' => 'EUR',
                'average_ticket_amount' => '12',
                'highest_ticket_amount' => '34',
                'alternative_payment_methods' => 'sd',
                'payment_method_currently_offered' => 'Credit Card',
                'current_mcc' => 'Current MCC',
                'current_descriptor' => 'descriptiona',
                'cb_volumes_twelve_months' => '235',
                'sales_volumes_twelve_months' => '12',
                'refund_twelve_months' => '42',
                'current_acquire_psp' => '23',
                'trading_urls' => [
                'https =>//one.finxp.com/',
                'https =>//two.finxp.com/'
                ],
                'countries' => [
                [
                    'countries_where_product_offered' => 'DZA',
                    'distribution_per_country' => '10'
                ],
                [
                    'countries_where_product_offered' => 'BOL',
                    'distribution_per_country' => '12'
                ]
                ]
            ],
            'products' => [
                'SEPA Direct Debit',
                'Credit Card Processing',
                'IBAN4U Payment Account'
            ],
            'disabled' => '',
            'section' => 'acquiring-services'
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');

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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    public function testShouldValidateSectionSeniorManagementOfficer()
    {
        $b = Business::all()->last();

        $payload = [
            'tax_name' => 'US Tax Resident',
            'company_representative' => [
                [
                    'first_name' => 'Andrew',
                    'surname' => 'Enqiruez',
                    'middle_name' => 'Salvador',
                    'date_of_birth' => '1990-04-08',
                    'place_of_birth' => 'Moscow',
                    'email_address' => 'raman@russia.edu',
                    'nationality' => 'MSK',
                    'citizenship' => 'DEU',
                    'phone_code' => '+376',
                    'phone_number' => '34534534534',
                    'roles' => [
                        [
                        'roles_in_company' => 'UBO',
                        'percent_ownership' => '45',
                        'iban4u_rights' => 'UBO'
                        ]
                    ],
                    'residential_address' => [
                        'street_number' => 'Manila',
                        'street_name' => 'Fabella',
                        'postal_code' => '2342',
                        'city' => 'mandaluyong',
                        'country' => 'SGP'
                    ],
                    'identity_information' => [
                        'id_type' => 'Passport',
                        'country_of_issue' => 'AND',
                        'id_number' => 'ID6663GDH',
                        'document_date_issued' => '2024-04-12',
                        'document_expired_date' => '2023-04-14',
                        'high_net_worth' => 'YES',
                        'us_citizenship' => 'NO',
                        'politically_exposed_person' => 'NO'
                    ],
                    'company_representative_document' => [
                        'proof_of_address' => 'Company PCI certificate.pdf',
                        'proof_of_address_size' => '150 KB',
                        'identity_document' => 'Copy of Bank settlement.pdf',
                        'identity_document_size' => '230 KB',
                        'identity_document_addt' => 'Third Party Questionnaire.pdf',
                        'identity_document_addt_size' => '240 KB',
                        'source_of_wealth' => '',
                        'source_of_wealth_size' => ''
                    ]
                ]
            ],
            'senior_management_officer' => [
                'first_name' => 'Sally',
                'surname' => 'Tbugil',
                'date_of_birth' => '1990-03-12',
                'place_of_birth' => 'MLT',
                'email_address' => 'sally@test.com',
                'nationality' => 'DEU',
                'citizenship' => 'MLT',
                'phone_number' => '47562112',
                'phone_code' => '+639',
                'roles_in_company' => '',
                'residential_address' => [
                'street_number' => '123123',
                'street_name' => 'Block',
                'postal_code' => '3255',
                'city' => 'Manila',
                'country' => 'PHL',
                ],
                'identity_information' => [
                'id_type' => 'Passport',
                'country_of_issue' => 'DEU',
                'id_number' => 'GH763584',
                'document_date_issued' => '1991-08-11',
                'document_expired_date' => '2025-01-20',
                'high_net_worth' => '450',
                'politically_exposed_person' => 'YES'
                ],
                'senior_management_officer_document' => [
                'proof_of_address' => 'Block Area 231',
                'identity_document' => 'Passport'
                ]
            ],
            'section' => 'company_representatives'
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');

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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    public function testShouldValidateSectionCompanyRepresentative()
    {
        $b = Business::all()->last();

        $payload = [
            'company_representative' => [
                    [
                    'first_name' => 'Andrew',
                    'surname' => 'Enqiruez',
                    'middle_name' => 'Salvador',
                    'date_of_birth' => '1990-04-08',
                    'place_of_birth' => 'Moscow',
                    'email_address' => 'raman@russia.edu',
                    'nationality' => 'MSK',
                    'citizenship' => 'DEU',
                    'phone_code' => '+376',
                    'phone_number' => '34534534534',
                    'roles' => [
                        [
                        'roles_in_company' => 'UBO',
                        'percent_ownership' => '45',
                        'iban4u_rights' => 'UBO'
                        ]
                    ],
                    'residential_address' => [
                        'street_number' => 'Manila',
                        'street_name' => 'Fabella',
                        'postal_code' => '2342',
                        'city' => 'mandaluyong',
                        'country' => 'SGP'
                    ],
                    'identity_information' => [
                        'id_type' => 'Passport',
                        'country_of_issue' => 'AND',
                        'id_number' => 'ID6663GDH',
                        'document_date_issued' => '2024-04-11',
                        'document_expired_date' => '2023-04-09',
                        'high_net_worth' => 'YES',
                        'us_citizenship' => 'NO',
                        'politically_exposed_person' => 'NO'
                    ],
                    'company_representative_document' => [
                        'proof_of_address' => 'Company PCI certificate.pdf',
                        'proof_of_address_size' => '150 KB',
                        'identity_document' => 'Copy of Bank settlement.pdf',
                        'identity_document_size' => '230 KB',
                        'identity_document_addt' => 'Third Party Questionnaire.pdf',
                        'identity_document_addt_size' => '240 KB',
                        'source_of_wealth' => '',
                        'source_of_wealth_size' => ''
                    ]
                ]
            ],
            'senior_management_officer' => [],
            'section' => 'company_representatives'
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');

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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    public function testShouldValidateSectionDataProtectionMarketing()
    {
        $b = Business::all()->last();

        $payload = [
            'data_protection_marketing' => [
                'data_protection_notice' => 1,
                'receive_messages_from_finxp' => 'NO',
                'receive_market_research_survey' => 'NO'
            ],
            'section' => 'data-protection-and-marketing',
            'corporate_saving' => true
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');

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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    public function testShouldValidateSectionDeclaration()
    {
        $b = Business::all()->last();

        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image('sample.jpg');

        $payload = [
            'products' => [
                'SEPA Direct Debit',
                'Credit Card Processing',
                'IBAN4U Payment Account'
            ],
            'file' => $documentFile,
            'section' => 'declaration',
        ];

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');

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
            ->putJson(route('businesses.corporatesave', $b->id), $payload)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
            'status',
            'message'
        ]);
    }
}
