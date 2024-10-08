<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralDocuments extends Model
{
    use HasFactory;

    protected $table = 'ac_general_documents';
    protected $fillable = [
        'company_information_id',
        'memorandum_and_articles_of_association',
        'memorandum_and_articles_of_association_size',
        'certificate_of_incorporation',
        'certificate_of_incorporation_size',
        'registry_exact',
        'registry_exact_size',
        'company_structure_chart',
        'company_structure_chart_size',
        'proof_of_address_document',
        'proof_of_address_document_size',
        'operating_license',
        'operating_license_size'
    ];

    const REQUIRED_DOCUMENTS = 'apply_corporate';

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];

    public function getValidDocumentTypes()
    {
        return [
            'iban4u_payment_account_documents',
            'credit_card_processing_documents',
            'company_representative_document'
        ];
    }
}
