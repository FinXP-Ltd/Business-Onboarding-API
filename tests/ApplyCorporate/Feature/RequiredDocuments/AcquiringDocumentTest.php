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

class AcquiringDocumentTest extends TestCase
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

    public function testUploadIban4uDocumentProofOwnershipDomain()
    {
        $fileName = 'ProofWwnershipDomain.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'proof_of_ownership_of_the_domain',
            'type' => 'credit_card_processing_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['credit_card_processing_documents']['proof_of_ownership_of_the_domain'][0]['proof_of_ownership_of_the_domain']);
        $this->assertEquals('Proof of ownership of the domain', $content['data']['credit_card_processing_documents']['proof_of_ownership_of_the_domain'][0]['proof_of_ownership_of_the_domain_label']);
    }

    public function testUploadIban4uDocumentProcessingHistory()
    {
        $fileName = 'ProcessingHistory.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'processing_history',
            'type' => 'credit_card_processing_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['credit_card_processing_documents']['processing_history'][0]['processing_history']);
        $this->assertEquals('Processing History', $content['data']['credit_card_processing_documents']['processing_history'][0]['processing_history_label']);
    }

    public function testUploadIban4uDocumentCcCopyBankSettlement()
    {
        $fileName = 'BankSettlement.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'cc_copy_of_bank_settlement',
            'type' => 'credit_card_processing_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['credit_card_processing_documents']['cc_copy_of_bank_settlement'][0]['cc_copy_of_bank_settlement']);
        $this->assertEquals('Copy of Bank settlement of the account that will receive settlements', $content['data']['credit_card_processing_documents']['cc_copy_of_bank_settlement'][0]['cc_copy_of_bank_settlement_label']);
    }

    public function testUploadIban4uDocumentCompanyPciCertificate()
    {
        $fileName = 'CompanyPciCertificate.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'company_pci_certificate',
            'type' => 'credit_card_processing_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['credit_card_processing_documents']['company_pci_certificate'][0]['company_pci_certificate']);
        $this->assertEquals('Company PCI certificate - Or PCI self-assessment questionnaire', $content['data']['credit_card_processing_documents']['company_pci_certificate'][0]['company_pci_certificate_label']);
    }
}
