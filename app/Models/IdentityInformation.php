<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class IdentityInformation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_representative_identity_information';
    protected $fillable = [
        'index',
        'order',
        'id_type',
        'country_of_issue',
        'id_number',
        'document_date_issued',
        'document_expired_date',
        'filename_identity_document',
        'filetype_identity_document',
        'high_net_worth',
        'us_citizenship',
        'politically_exposed_person',
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];
}
