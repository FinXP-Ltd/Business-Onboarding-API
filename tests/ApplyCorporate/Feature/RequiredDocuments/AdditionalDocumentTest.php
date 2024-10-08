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

class AdditionalDocumentTest extends TestCase
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

    public function testUploadAdditionalDocuments()
    {
        $fileName = 'AdditionalDocument.pdf';
        Storage::fake('azure');
        $documentFile = UploadedFile::fake()->image($fileName);

        $payload= [
            'file' => $documentFile,
            'file_name' => $fileName,
            'column' => 'Others',
            'type' => 'additional_documents',
            'section' => 'additional_documents',
            'corporate_saving' => 'true'
        ];

        $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->post(route('businesses.corporatedocument.upload', $this->business->id), $payload);

        $response = $this->impersonate(credential: $this->role, guard: 'auth0-api')
        ->getJson(route('businesses.corporatecompany.sections', ['business' => $this->business->id, 'section' => 'required-documents']));
        $response->assertStatus(Response::HTTP_OK);

        $content = json_decode($response->getContent(), true);
        $this->assertEquals($fileName, $content['data']['additional_documents'][0]['file_name']);
        $this->assertEquals('Others', $content['data']['additional_documents'][0]['file_type']);
    }
}
