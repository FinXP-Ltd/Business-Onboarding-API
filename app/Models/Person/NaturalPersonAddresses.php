<?php

namespace App\Models\Person;

use App\Traits\HasEncryptedFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NaturalPersonAddresses extends Model
{
    use HasFactory, HasEncryptedFields;

    protected $table = 'natural_person_addresses';

    protected $fillable = [
        'line_1',
        'line_2',
        'locality',
        'postal_code',
        'country',
        'city',
        'nationality'
    ];

    protected $encryptable = [
        'line_1',
        'line_2',
        'locality',
        'postal_code',
        'country',
        'city',
        'nationality'
    ];
}
