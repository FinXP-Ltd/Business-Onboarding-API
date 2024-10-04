<?php

namespace App\Models\Person;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NaturalPersonIdentificationDocument extends Model
{
    use HasFactory;

    protected $table = 'natural_person_identification_docs';

    protected $fillable = [
        'document_type', 
        'document_number', 
        'document_country_of_issue',
        'document_expiry_date'
    ];
}
