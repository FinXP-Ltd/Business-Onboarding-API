<?php

namespace App\Models\NonNaturalPerson;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NonNaturalPersonAddresses extends Model
{
    use HasFactory;

    protected $table = 'non_natural_person_addresses';

    protected $fillable = [
        'line_1',
        'line_2',
        'postal_code',
        'locality',
        'licensed_reputable_jurisdiction',
        'country'
    ];
}
