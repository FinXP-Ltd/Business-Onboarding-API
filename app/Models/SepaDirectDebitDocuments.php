<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SepaDirectDebitDocuments extends Model
{
    use HasFactory;

    protected $table = 'ac_sepa_direct_debit_documents';
    protected $fillable = [
        'company_information_id',
        'template_of_customer_mandate',
        'template_of_customer_mandate_size',
        'processing_history_with_chargeback_and_ratios',
        'processing_history_with_chargeback_and_ratios_size',
        'copy_of_bank_settlement',
        'copy_of_bank_settlement_size',
        'product_marketing_information',
        'product_marketing_information_size'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];
}
