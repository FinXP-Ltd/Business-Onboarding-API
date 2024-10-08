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

class SepaDirectDebitDocumentTest extends TestCase
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

    public function testUploadIban4uDocumentTemplateCustomerMandate()
    {
        $fileName = 'TemplateCustomerMandate.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'template_of_customer_mandate',
            'type' => 'sepa_direct_debit_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['sepa_direct_debit_documents']['template_of_customer_mandate'][0]['template_of_customer_mandate']);
        $this->assertEquals('Template of Customer Mandate - (Paper format/digital or recorded)', $content['data']['sepa_direct_debit_documents']['template_of_customer_mandate'][0]['template_of_customer_mandate_label']);
    }

    public function testUploadIban4uDocumentProcessingHistoryChargebackRatios()
    {
        $fileName = 'ProcessingHistoryChargebackRatios.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'processing_history_with_chargeback_and_ratios',
            'type' => 'sepa_direct_debit_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['sepa_direct_debit_documents']['processing_history_with_chargeback_and_ratios'][0]['processing_history_with_chargeback_and_ratios']);
        $this->assertEquals('Processing History including Chargebacks and Ratios', $content['data']['sepa_direct_debit_documents']['processing_history_with_chargeback_and_ratios'][0]['processing_history_with_chargeback_and_ratios_label']);
    }

    public function testUploadIban4uDocumentSepaBankSettlement()
    {
        $fileName = 'SepaBankSettlement.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'sepa_copy_of_bank_settlement',
            'type' => 'sepa_direct_debit_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['sepa_direct_debit_documents']['sepa_copy_of_bank_settlement'][0]['sepa_copy_of_bank_settlement']);
        $this->assertEquals('Copy of Bank settlement of the account that will receive settlements', $content['data']['sepa_direct_debit_documents']['sepa_copy_of_bank_settlement'][0]['sepa_copy_of_bank_settlement_label']);
    }

    public function testUploadIban4uDocumentProductMarketingInformation()
    {
        $fileName = 'ProductMarketingInformation.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'product_marketing_information',
            'type' => 'sepa_direct_debit_documents',
            'section' => 'required_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['sepa_direct_debit_documents']['product_marketing_information'][0]['product_marketing_information']);
        $this->assertEquals('Product leaflets or Product marketing information', $content['data']['sepa_direct_debit_documents']['product_marketing_information'][0]['product_marketing_information_label']);
    }
}
