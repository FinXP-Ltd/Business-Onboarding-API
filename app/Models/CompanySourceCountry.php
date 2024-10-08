<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanySourceCountry extends Model
{
    use HasFactory, HasUuids;

    protected $table = "company_source_countries";

    protected $fillable = [
        "company_information_id",
        "type",
        "country",
        "is_selected"
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_selected' => 'boolean'
    ];

    function taxInformation()
    {
        return $this->belongsTo(TaxInformation::class);
    }
}
