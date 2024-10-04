<?php

namespace App\Models\Person;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\Pep;
class AdditionalInfo extends Model
{
    use HasFactory;

    protected $table = 'additional_infos';

    protected $fillable = [
        'occupation',
        'employment',
        'position',
        'source_of_income',
        'source_of_wealth',
        'source_of_wealth_details',
        'other_source_of_wealth_details',
        'us_citizenship',
        'pep',
        'tin',
        'country_tax'
    ];

    protected $casts = [
        'pep' => Pep::class
    ];
}
