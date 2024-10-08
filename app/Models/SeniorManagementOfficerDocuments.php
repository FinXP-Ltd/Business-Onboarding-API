<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SeniorManagementOfficerDocuments extends Model
{
    use HasFactory, HasUuids;

     protected $table = 'senior_management_officer_documents';

    protected $fillable = [
        'proof_of_address',
        'proof_of_address_size',
        'identity_document',
        'identity_document_size',
        'identity_document_addt',
        'identity_document_addt_size'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];
}
