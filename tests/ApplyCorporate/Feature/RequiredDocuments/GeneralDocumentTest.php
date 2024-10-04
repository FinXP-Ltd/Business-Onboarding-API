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

class GeneralDocumentTest extends TestCase
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

    public function testShouldGetGeneralDocumentMemorandumAndArticlesOfAssociation()
    {
        Storage::fake('azure');
        $fileName = 'MemorandumFile.docx';
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'memorandum_and_articles_of_association',
            'type' => 'general_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['general_documents']['memorandum_and_articles_of_association'][0]['memorandum_and_articles_of_association']);
        $this->assertEquals('Memorandum and Articles of Association', $content['data']['general_documents']['memorandum_and_articles_of_association'][0]['memorandum_and_articles_of_association_label']);
    }

    public function testShouldGetGeneralDocumentCertificateOfIncorporation()
    {
        Storage::fake('azure');
        $fileName = 'CertificateOfIncorporation.docx';
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'certificate_of_incorporation',
            'type' => 'general_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['general_documents']['certificate_of_incorporation'][0]['certificate_of_incorporation']);
        $this->assertEquals('Certificate of Incorporation', $content['data']['general_documents']['certificate_of_incorporation'][0]['certificate_of_incorporation_label']);
    }

    public function testShouldGetGeneralDocumentRegistryExact()
    {
        Storage::fake('azure');
        $fileName = 'RegistrtExtract.docx';
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'registry_exact',
            'type' => 'general_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['general_documents']['registry_exact'][0]['registry_exact']);
        $this->assertEquals('Registry exact confirming the UBOs/Shareholders and Directors of the applying company - Not older 6 months.', $content['data']['general_documents']['registry_exact'][0]['registry_exact_label']);
    }
}
