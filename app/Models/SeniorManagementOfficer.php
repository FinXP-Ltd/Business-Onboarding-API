<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SeniorManagementOfficer extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'senior_management_officer';
    protected $fillable = [
        'first_name',
        'middle_name',
        'surname',
        'place_of_birth',
        'date_of_birth',
        'nationality',
        'citizenship',
        'email_address',
        'phone_code',
        'phone_number',
        'roles_in_company',
        'company_information_id',
        'required_indicator'
    ];

    protected $hidden = ['created_at', 'updated_at', 'id'];

    public function seniorOfficerResidentialAddress(): HasOne
    {
        return $this->hasOne(SeniorManagementOfficerResidentialAddress::class, 'senior_officer_id');
    }

    public function seniorOfficerIdentityInformation(): HasOne
    {
        return $this->hasOne(SeniorManagementOfficerIdentityInformation::class, 'senior_officer_id');
    }

    public function seniorManagementOfficerDocuments(): HasOne
    {
        return $this->hasOne(SeniorManagementOfficerDocuments::class, 'senior_officer_id');
    }
}
