<?php

namespace Tests\ApplyCorporate\Feature\RequiredDocuments;

use App\Models\Auth\User;
use App\Models\CompanyInformation;
use App\Models\GeneralDocuments;
use App\Models\CompanySepaDd;
use App\Models\CompanyCreditCardProcessing;
use App\Models\IBAN4UPaymentAccountDocuments;
use App\Models\CreditCardProcessingDocuments;
use App\Models\SepaDirectDebitDocuments;
use Illuminate\Http\Response;
use App\Models\Business;
use Database\Factories\CreditCardProcessingDocumentsFactory;
use Database\Factories\IBAN4UPaymentAccountDocumentsFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Iban4uDocumentsTest extends TestCase
{
    public $business;
    public $companyInfo;
    public $role;
    public $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->swapAuth0Mock();

        $this->business = Business::factory()->create(['status' => 'OPENED']);
        $this->companyInfo =  CompanyInformation::create(['business_id' => $this->business->id]);
        CompanySepaDd::create(['company_information_id' => $this->companyInfo->id]);
        CompanyCreditCardProcessing::create(['company_information_id' => $this->companyInfo->id]);

        $authUserToken = $this->getTokenPayload('agent', 'client_id.app');
        $sub = $authUserToken['sub'];
        $this->user = User::factory()->create([
            'email' => 'marjorie.asensi@finxp.com',
            'auth0' => $sub
        ]);

        $this->role = $this->createImposterUser(
            id: $this->user->auth0,
            email: $this->user->email,
            accessTokenPayload: $authUserToken,
        );
    }

    public function testUploadIban4uDocumentAgreementsEntities()
    {
        $fileName = 'IBAN4UAccount.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'agreements_with_the_entities',
            'type' => 'iban4u_payment_account_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['iban4u_payment_account_documents']['agreements_with_the_entities'][0]['agreements_with_the_entities']);
        $this->assertEquals('Agreements with the entities that shall be settling funds into the IBAN4U Account', $content['data']['iban4u_payment_account_documents']['agreements_with_the_entities'][0]['agreements_with_the_entities_label']);
    }

    public function testUploadIban4uDocumentBoardResolution()
    {
        $fileName = 'BoardResolution.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'board_resolution',
            'type' => 'iban4u_payment_account_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['iban4u_payment_account_documents']['board_resolution'][0]['board_resolution']);
        $this->assertEquals('Board Resolution', $content['data']['iban4u_payment_account_documents']['board_resolution'][0]['board_resolution_label']);
    }

    public function testUploadIban4uDocumentThirdPartyQuestionnaire()
    {
        $fileName = 'ThirdPartyQuestionnaire.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'third_party_questionnaire',
            'type' => 'iban4u_payment_account_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['iban4u_payment_account_documents']['third_party_questionnaire'][0]['third_party_questionnaire']);
        $this->assertEquals('Third Party Questionnaire', $content['data']['iban4u_payment_account_documents']['third_party_questionnaire'][0]['third_party_questionnaire_label']);
    }
}
