<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CompanyAddress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'company_addresses';
    protected $fillable = [
        'company_information_id',
        'registered_street_number',
        'registered_street_name',
        'registered_postal_code',
        'registered_city',
        'registered_country',
        'operational_street_number',
        'operational_street_name',
        'operational_postal_code',
        'operational_city',
        'operational_country',
        'is_same_address'
    ];
    protected $hidden = ['created_at', 'updated_at', 'id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_same_address' => 'boolean',
    ];

    function business()
    {
        return $this->belongsTo(TaxInformation::class);
    }

}
