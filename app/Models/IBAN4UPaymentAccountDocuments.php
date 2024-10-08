<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IBAN4UPaymentAccountDocuments extends Model
{
    use HasFactory;

    protected $table = 'ac_iban4u_documents';
    protected $fillable = [
        'company_information_id',
        'agreements_with_the_entities',
        'agreements_with_the_entities_size',
        'board_resolution',
        'board_resolution_size',
        'third_party_questionnaire',
        'third_party_questionnaire_size'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];
}
