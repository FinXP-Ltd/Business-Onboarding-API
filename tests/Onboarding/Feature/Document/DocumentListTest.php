<?php

namespace Tests\Onboarding\Feature\Document;

use App\Models\Auth\User;
use App\Models\Business;
use App\Models\Document;
use App\Models\BusinessComposition;
use App\Models\NonNaturalPerson\NonNaturalPerson;
use App\Models\Person\NaturalPerson;
use App\Models\NonNaturalPerson\NonNaturalPerson as NonPerson;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentListTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();
    }

    public function testShouldShowDocumentsNeededForUbo()
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
            "voting_share" => 25,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $entity = [
            'entity_type' => 'UBO',
            'business_id' =>  $naturalPerson->id
        ];

        $imposterOne = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterOne, guard: 'auth0-api')
        ->postJson(route('business.createComposition'), $businessComposition);

        $authUserToken = $this->getTokenPayload('agent');

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $imposterTwo = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterTwo, guard: 'auth0-api')
        ->getJson(route('document.list', $entity))
        ->assertJson(function (AssertableJson $json) {
           return $json
            ->has('UBO', fn ($json) => [
                $json->where('required', []),
                $json->where('optional', [
                    'fxp_internal',
                    'other',
                    'screening_searches',
                    'source_of_wealth_declaration_form',
                    'sow_supporting_docs',
                    'utility_bill_or_proof_of_address',
                    'coloured_copy_of_photo_identity_document'
                ])
            ]);
        });
    }

    public function testShouldShowDocumentsNeededForDirector()
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
            "position" => ["DIR"],
            "voting_share" => 25,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $entity = [
            'entity_type' => 'DIR',
            'business_id' =>  $naturalPerson->id
        ];

        $imposterOne = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterOne, guard: 'auth0-api')
        ->postJson(route('business.createComposition'), $businessComposition);

        $authUserToken = $this->getTokenPayload('agent');

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $imposterTwo = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterTwo, guard: 'auth0-api')
        ->getJson(route('document.list', $entity))
        ->assertJson(function (AssertableJson $json) {
           return $json
            ->has('DIR', fn ($json) => [
                $json->where('required', ['utility_bill_or_proof_of_address']),
                $json->where('optional', [
                    'other',
                    'screening_searches',
                    'coloured_copy_of_photo_identity_document'
                ])
            ]);
        });
    }

    public function testShouldShowDocumentsNeededForShareholder()
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
            "position" => ["SH"],
            "voting_share" => 25,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $entity = [
            'entity_type' => 'SH',
            'business_id' =>  $naturalPerson->id
        ];

        $imposterOne = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterOne, guard: 'auth0-api')
        ->postJson(route('business.createComposition'), $businessComposition);

        $authUserToken = $this->getTokenPayload('agent');

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $imposterTwo = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterTwo, guard: 'auth0-api')
        ->getJson(route('document.list', $entity))
        ->assertJson(function (AssertableJson $json) {
           return $json
            ->has('SH', fn ($json) => [
                $json->where('required', ['utility_bill_or_proof_of_address']),
                $json->where('optional', [
                    'coloured_copy_of_photo_identity_document',
                    'audited_accounts',
                    'bank_statement_demonstration_deposits',
                    'copy_of_court_order_judicial_separation_agreement',
                    'copy_of_will_or_signed_letter_from_solicitor_or_grant_of_probate_or_letter_from_executor',
                    'documentary_evidence_for_the_donor_as_detailed_above',
                    'employment_contract_or_statement_of_income',
                    'evidence_from_the_lottery_company_cheque_winnings_receipt',
                    'loan_agreement_or_statement',
                    'other',
                    'screening_searches',
                    'signed_letter_from_notary_or_solicitor_or_advocate_or_estate_agent_contract_of_sale',
                    'sow_supporting_docs',
                    'statement_from_investment_provider_or_bank_statement_showing_settlement_of_investment'
                ])
            ]);
        });
    }

    public function testShouldShowDocumentsNeededForSignatory()
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
            "position" => ["SIG"],
            "voting_share" => 25,
            "start_date" => "2002-01-01",
            "end_date" => "2021-01-01"
        ];

        $entity = [
            'entity_type' => 'SIG',
            'business_id' =>  $naturalPerson->id
        ];

        $imposterOne = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterOne, guard: 'auth0-api')
        ->postJson(route('business.createComposition'), $businessComposition);

        $authUserToken = $this->getTokenPayload('agent');

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $imposterTwo = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterTwo, guard: 'auth0-api')
        ->getJson(route('document.list', $entity))
        ->assertJson(function (AssertableJson $json) {
           return $json
            ->has('SIG', fn ($json) => [
                $json->where('required', ['utility_bill_or_proof_of_address']),
                $json->where('optional', [
                    'other',
                    'coloured_copy_of_photo_identity_document',
                ])
            ]);
        });
    }

    public function testShouldShowDocumentsNeededForBusiness()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $entity = [
            'entity_type' => 'B'
        ];

        $imposterOne = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterOne, guard: 'auth0-api')
        ->getJson(route('document.list', $entity))
        ->assertJson(function (AssertableJson $json) {
           return $json
            ->has('B', fn ($json) => [
                $json->where('required', ['certificate_of_incorporation']),
                $json->where('optional', [
                    'detailed_corporate_chart_showing_from_ubos_down_to_controlling_persons',
                    'agreements_with_the_entities_that_shall_be_settling_funds_into_the_account',
                    'audited_accounts',
                    'approval',
                    'bank_statement_demonstration_deposits',
                    'board_resolution',
                    'brief_company_profile',
                    'other',
                    'export_of_app_for_archive_for_new_split_programs',
                    'fxp_internal',
                    'screening_searches',
                    'transaction_monitoring_documentation',
                    'yearly_review_docs',
                    'processing_history',
                    'product_information',
                    'proof_of_ownership_of_the_domain'
                ])
            ]);
        });
    }

    public function testShouldShowDocumentsNeededByDefault()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $entity = [
            'entity_type' => null
        ];

        $imposterOne = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterOne, guard: 'auth0-api')
        ->getJson(route('document.list', $entity))
        ->assertJson(function (AssertableJson $json) {
           return $json
            ->has('B', fn ($json) => [
                $json->where('required', ['certificate_of_incorporation']),
                $json->where('optional', [
                    'detailed_corporate_chart_showing_from_ubos_down_to_controlling_persons',
                    'agreements_with_the_entities_that_shall_be_settling_funds_into_the_account',
                    'audited_accounts',
                    'approval',
                    'bank_statement_demonstration_deposits',
                    'board_resolution',
                    'brief_company_profile',
                    'other',
                    'export_of_app_for_archive_for_new_split_programs',
                    'fxp_internal',
                    'screening_searches',
                    'transaction_monitoring_documentation',
                    'yearly_review_docs',
                    'processing_history',
                    'product_information',
                    'proof_of_ownership_of_the_domain'
                ])
            ]);
        });
    }

    public function testShouldShowDocumentsNeededByDirectorCorporate()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $entity = [
            'entity_type' => null
        ];

        $imposterOne = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterOne, guard: 'auth0-api')
        ->getJson(route('document.list', $entity))
        ->assertJson(function (AssertableJson $json) {
           return $json
            ->has('B', fn ($json) => [
                $json->where('required', ['certificate_of_incorporation']),
                $json->where('optional', [
                    'detailed_corporate_chart_showing_from_ubos_down_to_controlling_persons',
                    'agreements_with_the_entities_that_shall_be_settling_funds_into_the_account',
                    'audited_accounts',
                    'approval',
                    'bank_statement_demonstration_deposits',
                    'board_resolution',
                    'brief_company_profile',
                    'other',
                    'export_of_app_for_archive_for_new_split_programs',
                    'fxp_internal',
                    'screening_searches',
                    'transaction_monitoring_documentation',
                    'yearly_review_docs',
                    'processing_history',
                    'product_information',
                    'proof_of_ownership_of_the_domain'
                ])
            ]);
        });
    }

    public function testShouldShowDocumentsNeededByShareholderCorporate()
    {
        $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $entity = [
            'entity_type' => null
        ];

        $imposterOne = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );

        $this->impersonate(credential: $imposterOne, guard: 'auth0-api')
        ->getJson(route('document.list', $entity))
        ->assertJson(function (AssertableJson $json) {
           return $json
            ->has('B', fn ($json) => [
                $json->where('required', ['certificate_of_incorporation']),
                $json->where('optional', [
                    'detailed_corporate_chart_showing_from_ubos_down_to_controlling_persons',
                    'agreements_with_the_entities_that_shall_be_settling_funds_into_the_account',
                    'audited_accounts',
                    'approval',
                    'bank_statement_demonstration_deposits',
                    'board_resolution',
                    'brief_company_profile',
                    'other',
                    'export_of_app_for_archive_for_new_split_programs',
                    'fxp_internal',
                    'screening_searches',
                    'transaction_monitoring_documentation',
                    'yearly_review_docs',
                    'processing_history',
                    'product_information',
                    'proof_of_ownership_of_the_domain'
                ])
            ]);
        });
    }

    public function testShouldThrowErrorIfEntityTypeIsNotInList()
    {
        $entity = [
            'entity_type' => 'foo',
        ];

       $authUserToken = $this->getTokenPayload('agent');

        $sub = $authUserToken['sub'];

        $user = User::factory()->create([
            'email' => fake()->email,
            'auth0' => $sub,
        ]);

        $imposter = $this->createImposterUser(
            id: $user->auth0,
            email: $user->email,
            accessTokenPayload: $authUserToken,
        );
        $this->impersonate(credential: $imposter, guard: 'auth0-api')
        ->getJson(route('document.list', $entity))
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJson([
            'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
            'status' => 'failed',
            'message' => ['entity_type' => ['The selected entity type is invalid.']],
        ]);
    }
}
