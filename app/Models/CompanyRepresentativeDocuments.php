<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyRepresentativeDocuments extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_representative_documents';

    protected $fillable = [
        'company_information_id',
        'index',
        'order',
        'proof_of_address',
        'proof_of_address_size',
        'identity_document',
        'identity_document_size',
        'identity_document_addt',
        'identity_document_addt_size',
        'source_of_wealth',
        'source_of_wealth_size'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];
}
