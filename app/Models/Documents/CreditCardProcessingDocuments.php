<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CreditCardProcessingDocuments extends Model
{
    use HasUuids;

    protected $table = 'docs_credit_card_processing_documents';

    protected $fillable = [
        'file_name',
        'file_type',
        'file_size',
        'company_information_id'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];
}
