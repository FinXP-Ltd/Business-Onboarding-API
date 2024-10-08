<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GeneralDocuments;
use App\Models\IBAN4UPaymentAccountDocuments;
use App\Models\CreditCardProcessingDocuments;
use App\Models\SepaDirectDebitDocuments;
use Illuminate\Support\Str;

class TransferDocumentList extends Command
{
    protected $signature = 'transfer:documents';
    protected $description = 'Transfer the list to the corresponding tables';
    protected $modelGeneralDocuments = 'App\Models\Documents\GeneralDocuments';
    protected $Iban4uDocuments = 'App\Models\Documents\IBAN4UPaymentAccountDocuments';
    protected $sepaDocuments = 'App\Models\Documents\SepaDirectDebitDocuments';
    protected $creditCardDocuments = 'App\Models\Documents\CreditCardProcessingDocuments';

    public function handle()
    {
        $this->line('You are about to transfer data from another table');
        if ($this->confirm('Do you wish to continue?')) {
            $this->line('');
            $this->line('processing....');
            if($this->confirm('Did you migrate the tables needed?')){
                $this->line('');
                $this->line('General Documents');
                $this->transferGeneralDocuments();

                $this->line('');
                $this->line('IBAN4U Payment Account Documents');
                $this->transferIBAN4UPaymentAccountDocuments();

                $this->line('');
                $this->line('Credit Card Processing Documents');
                $this->transferCreditCardProcessingDocuments();

                $this->line('');
                $this->line('Sepa Direct Debit Documents');
                $this->transferSepaDirectDebitDocuments();
            }
        }else{
            $this->line('Aborted....');
            return 0;
        }

    }

    public function transferGeneralDocuments()
    {
        $generalDocuments = GeneralDocuments::all();
        $generalDocuments->each(function($documents) {

            $this->insertToTable($this->modelGeneralDocuments,
                $documents->memorandum_and_articles_of_association,
                'memorandum_and_articles_of_association',
                $documents->memorandum_and_articles_of_association_size,
                $documents->company_information_id);

            $this->insertToTable($this->modelGeneralDocuments,
                $documents->certificate_of_incorporation,
                'certificate_of_incorporation',
                $documents->certificate_of_incorporation_size,
                $documents->company_information_id);

            $this->insertToTable($this->modelGeneralDocuments,
                $documents->registry_exact,
                'registry_exact',
                $documents->registry_exact_size,
                $documents->company_information_id);

            $this->insertToTable($this->modelGeneralDocuments,
                $documents->company_structure_chart,
                'company_structure_chart',
                $documents->certificate_of_incorporation_size,
                $documents->company_information_id);

            $this->insertToTable($this->modelGeneralDocuments,
                $documents->proof_of_address_document,
                'proof_of_address_document',
                $documents->proof_of_address_document_size,
                $documents->company_information_id);

            $this->insertToTable($this->modelGeneralDocuments,
                $documents->operating_license,
                'operating_license',
                $documents->operating_license_size,
                $documents->company_information_id);
        });
    }

    public function transferIBAN4UPaymentAccountDocuments()
    {
        $iban4u = IBAN4UPaymentAccountDocuments::all();
        $iban4u->each(function($documents) {

            $this->insertToTable($this->Iban4uDocuments,
                $documents->agreements_with_the_entities,
                'agreements_with_the_entities',
                $documents->agreements_with_the_entities_size,
                $documents->company_information_id);

            $this->insertToTable($this->Iban4uDocuments,
                $documents->board_resolution,
                'board_resolution',
                $documents->board_resolution_size,
                $documents->company_information_id);

            $this->insertToTable($this->Iban4uDocuments,
                $documents->third_party_questionnaire,
                'third_party_questionnaire',
                $documents->third_party_questionnaire_size,
                $documents->company_information_id);
        });
    }

    public function transferCreditCardProcessingDocuments()
    {
        $creditCardProcessing = IBAN4UPaymentAccountDocuments::all();
        $creditCardProcessing->each(function($documents) {

            $this->insertToTable($this->creditCardDocuments,
                $documents->proof_of_ownership_of_the_domain,
                'proof_of_ownership_of_the_domain',
                $documents->proof_of_ownership_of_the_domain_size,
                $documents->company_information_id);

            $this->insertToTable($this->creditCardDocuments,
                $documents->processing_history,
                'processing_history',
                $documents->processing_history_size,
                $documents->company_information_id);

            $this->insertToTable($this->creditCardDocuments,
                $documents->copy_of_bank_settlement,
                'copy_of_bank_settlement',
                $documents->copy_of_bank_settlement_size,
                $documents->company_information_id);

            $this->insertToTable($this->creditCardDocuments,
                $documents->company_pci_certificate,
                'company_pci_certificate',
                $documents->company_pci_certificate_size,
                $documents->company_information_id);
        });
    }

    public function transferSepaDirectDebitDocuments()
    {
        $creditCardProcessing = SepaDirectDebitDocuments::all();
        $creditCardProcessing->each(function($documents) {

            $this->insertToTable($this->sepaDocuments,
                $documents->template_of_customer_mandate,
                'template_of_customer_mandate',
                $documents->template_of_customer_mandate_size,
                $documents->company_information_id);

            $this->insertToTable($this->sepaDocuments,
                $documents->processing_history_with_chargeback_and_ratios,
                'processing_history_with_chargeback_and_ratios',
                $documents->processing_history_with_chargeback_and_ratios_size,
                $documents->company_information_id);

            $this->insertToTable($this->sepaDocuments,
                $documents->copy_of_bank_settlement,
                'copy_of_bank_settlement',
                $documents->copy_of_bank_settlement_size,
                $documents->company_information_id);

            $this->insertToTable($this->sepaDocuments,
                $documents->product_marketing_information,
                'product_marketing_information',
                $documents->product_marketing_information_size,
                $documents->company_information_id);
        });
    }

    public function insertToTable($model, $fileName, $fileType, $fileSize, $companyInformationId)
    {
        if($fileName && $fileSize){
            $data = [
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'company_information_id' => $companyInformationId
            ];

            $exist = $model::where($data);

            if ($exist->count() == 0) {
                $data['id'] = Str::uuid(36);
                $model::insert($data);
                $this->line('Added...'.$fileType);
            }
        }
    }
}
