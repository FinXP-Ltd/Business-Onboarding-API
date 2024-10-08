<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCardProcessingDocuments extends Model
{
    use HasFactory;

    protected $table = 'ac_credit_card_processing_documents';
    protected $fillable = [
        'company_information_id',
        'proof_of_ownership_of_the_domain',
        'proof_of_ownership_of_the_domain_size',
        'processing_history',
        'processing_history_size',
        'copy_of_bank_settlement',
        'copy_of_bank_settlement_size',
        'company_pci_certificate',
        'company_pci_certificate_size'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];
}
