<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SeniorManagementOfficerResidentialAddress extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'senior_officer_residential_address';
    protected $fillable = [
        'index',
        'order',
        'street_number',
        'street_name',
        'postal_code',
        'city',
        'country',
    ];

    protected $hidden = ['created_at', 'updated_at', 'id', 'company_information_id'];
}
