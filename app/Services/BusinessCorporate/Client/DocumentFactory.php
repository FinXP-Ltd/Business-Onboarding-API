<?php

namespace App\Services\BusinessCorporate\Client;

use App\Abstracts\BusinessDeleteDocument;
use Illuminate\Database\Eloquent\Collection;

class DocumentFactory extends BusinessDeleteDocument
{
    public function deleteDocuments(): void
    {
        $types = [
            'iban4u_payment_account_documents',
            'credit_card_processing_documents',
            'sepa_direct_debit_documents',
            'company_representative',
            'general_documents',
            'additiona_documents'
        ];

        foreach ($types as $type) {
            $this->processDeletion($type);
        }
    }

    private function processDeletion(string $type): void
    {
        $path = "apply_corporate/{$this->getBusiness()->companyInformation->id}";
        $documents = $this->getDocuments($type);

        if ($type === 'general_documents') {
            $path ="$path/required_documents";
        }

        $this->deleteEachFileInGroup($documents, $path);

        $this->deleteCompanyRepresentativeDocuments($path);
    }

    private function deleteEachFileInGroup(Collection $documents, string $path): void
    {
        $documents->each(function($document) use ($path) {
            $this->setAfterDeleteProcess(fn () => $document->delete())->deleteFile($document, $path);
        });
    }

    private function getDocuments(string $type): Collection
    {
        $companyInformation = $this->getBusiness()->companyInformation()->first();

        switch ($type) {
            case 'iban4u_payment_account_documents':
                $files = $companyInformation->iban4uPaymentAccountDocuments()->get();
                break;
            case 'credit_card_processing_documents':
                $files = $companyInformation->creditCardProcessingDocuments()->get();
                break;
            case 'sepa_direct_debit_documents':
                $files = $companyInformation->sepaDirectDebitDocuments()->get();
                break;

            case 'additional_documents':
                $files = $companyInformation->additionalDocuments()->get();
                break;
            case 'general_documents':
            default:
                $files = $companyInformation->generalDocuments()->get();
                break;
        }

        return $files;
    }

    private function deleteCompanyRepresentativeDocuments(string $path)
    {
        $this->getBusiness()->companyInformation()
            ->first()
            ->companyRepresentative()
            ->get()
            ->each(function($companyRepresentative) use ($path) {
                $companyRepresentative
                    ->companyRepresentativeDocument()
                    ->get()
                    ->each(function($document) use ($path) {
                        $columnName = null;

                        $index = $document->index - 1;

                        $path .= "/company_representative/$index";

                        $files = [
                            'proof_of_address' => $document->proof_of_address,
                            'identity_document' => $document->identity_document,
                            'source_of_wealth' => $document->source_of_wealth,
                            'identity_document_addt' => $document->identity_document_addt
                        ];

                        foreach($files as $key => $value) {
                            if (trim($value) !== '') {
                                $this->setAfterDeleteProcess(function () use ($document, $key) {
                                    $document->update([
                                        $key => null,
                                        $key . '_size' => null
                                    ]);
                                })
                                ->deleteFile($document, $path, $columnName, $value);
                            }
                        }
                    });
            });
    }
}
