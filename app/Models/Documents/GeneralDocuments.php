<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GeneralDocuments extends Model
{
    use HasUuids;

    protected $table = 'docs_general_documents';

    protected $fillable = [
        'file_name',
        'file_type',
        'file_size',
        'company_information_id'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];
}
