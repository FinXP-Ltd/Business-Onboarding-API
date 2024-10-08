<?php

namespace App\Models;

use App\Traits\Scopes\HasBusinessScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxInformation extends Model
{
    use HasFactory, HasBusinessScope;

    protected $table = 'tax_informations';

    protected $fillable = [
        'name',
        'tax_country',
        'registration_number',
        'registration_type',
        'tax_identification_number',
        'jurisdiction',
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    function business()
    {
        return $this->belongsTo(Business::class);
    }
}
