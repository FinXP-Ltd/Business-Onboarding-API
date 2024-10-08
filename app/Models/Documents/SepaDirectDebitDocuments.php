<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SepaDirectDebitDocuments extends Model
{
    use HasUuids;

    protected $table = 'docs_sepa_direct_debit_documents';

    protected $fillable = [
        'file_name',
        'file_type',
        'file_size',
        'company_information_id'
    ];

   protected $hidden = ['created_at', 'updated_at', 'id'];
}
