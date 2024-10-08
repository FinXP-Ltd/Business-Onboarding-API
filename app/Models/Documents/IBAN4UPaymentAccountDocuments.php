<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IBAN4UPaymentAccountDocuments extends Model
{
    use HasUuids;

    protected $table = 'docs_iban4u_documents';

    protected $fillable = [
        'file_name',
        'file_type',
        'file_size',
        'company_information_id'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];
}
